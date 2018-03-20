<?php

namespace app\commands;

use Yii;
use yii\base\Exception;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use TheIconic\Tracking\GoogleAnalytics\Analytics;
use app\models\Order;

/**
 * Command for sync data from the CentralNic to Google Analytics
 */
class SyncGoogleController extends Controller
{
    public $debug;

    private $analytics;

    public function options($actionID)
    {
        return ['debug'];
    }

    public function init()
    {
        $analytics = new Analytics(true);
        $analytics
            ->setDebug(true) // (bool)$this->debug
            ->setProtocolVersion('1')
            ->setEventCategory('Domain')
            ->setEventAction('Create')
            ->setTrackingId('UA-98103468-2');

        $this->analytics = $analytics;
    }

    public function actionStart()
    {
        $date = date('Y-m-d', strtotime('yesterday'));

        $centralNicData = $this->centralNicData($date);
        if (!count($centralNicData)) {
            $this->stdout("Empty data in CentralNic ($date)\n", Console::FG_RED);
            return;
        }

        $orders = $this->orders($date);
        if (!count($orders)) {
            $this->stdout("No orders ($date)\n", Console::FG_RED);
            return;
        }

        foreach ($orders as $order) {
            $registrarId = $order['registrar_id'];
            $domains = explode(',', $order['domains']);
            foreach ($domains as $domain) {
                $key = array_search($domain, ArrayHelper::getValue($centralNicData, [$registrarId], []));
                if ($key === false) {
                    $this->stdout(
                        "Order №{$order['id']}: {$domain} ({$registrarId}) not found at the CentralNic\n",
                        Console::FG_RED
                    );
                    continue;
                }

                $googleClientId = ArrayHelper::getValue(unserialize($order['params']), 'googleClientId', '');
                if (empty($googleClientId)) {
                    $this->stdout("Order №{$order['id']}: has empty google client id\n", Console::FG_RED);
                    continue;
                }

                unset($centralNicData[$registrarId][$key]);
                $this->sendToGoogle($order['id'], $googleClientId, $registrarId, $domain);
            }
        }

        $this->stdout("Done!\n", Console::FG_GREEN);
    }

    private function sendToGoogle(string $orderId, string $clientId, string $registrarId, string $domain)
    {
        $this->stdout("Order №{$orderId}: {$domain} ({$registrarId}): sent to google $clientId\n", Console::FG_YELLOW);

        $this->analytics
            ->setClientId($clientId)
            ->setEventLabel($domain)
            ->sendEvent();
    }

    private function fetchCentralNicData(string $date): array
    {
        $client = new Client(['baseUrl' => 'https://stats.art.art']);
        $response = $client->get('info/t.php', [
            'created-before' => $date,
            'created-after' => $date,
        ])->send();

        if (!$response->isOk) {
            throw new Exception(Yii::t('app.msg', 'An error occurred while getting data from stats.art.art'));
        }

        $data = $response->getData();
        $data = ArrayHelper::getValue($data, 'data', []);

        return $data;
    }

    private function centralNicData(string $date): array
    {
        $data = $this->fetchCentralNicData($date);

        $dataByRegistrar = [];
        foreach ($data as $item) {
            if ($item['type'] !== 'create') {
                continue;
            }

            $domain = ArrayHelper::getValue($item, 'domain.name');
            $registrarId = ArrayHelper::getValue($item, 'registrar.id');

            $dataByRegistrar[$registrarId][] = $domain;
        }

        return $dataByRegistrar;
    }

    private function orders(string $date): array
    {
        return Order::find()
            ->select(['id', 'registrar_id', 'domains', 'params'])
            ->where(['DATE(date_create)' => $date])
            ->asArray()
            ->all();
    }
}
