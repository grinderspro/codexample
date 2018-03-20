<?php

namespace app\modules\search\models;

use yii\helpers\ArrayHelper;
use \app\components\eppclient\EppClientInterface;

class CentralNicSource
{
    private $client;

    public function __construct(EppClientInterface $client)
    {
        $this->client = $client;
    }

    public function check($names)
    {
        $names = $this->prepare($names);
        $result = $this->client->check($names);

        if ($result) {
            return $this->parseResult($result);
        }

        return $result;
    }

    private function parseResult($result)
    {
        $data = [];

        $result = ArrayHelper::getValue($result, ['chkData', 'cd'], []);
        if (isset($result['name'])) {
            $result = [$result];
        }

        foreach ($result as $item) {
            if (is_array($item['name'])) {
                $item['name'] = array_shift($item['name']);
            }

            $item['name'] = str_replace('.art', '', $item['name']);

            $data[$item['name']]['name'] = $item['name'];

            $available = $this->parseavailable($item);
            if ($available !== null) {
                $data[$item['name']]['available'] = $available;
            }

            //Premium
            $premium = $this->parsePremium($item);
            if ($premium !== null) {
                $data[$item['name']]['premium'] = $premium;
            }

            $price = $this->parsePrice($item);
            if ($price !== null) {
                $data[$item['name']]['price'] = $price;
            }
        }

        return $data;
    }

    private function parseavailable($item)
    {
        $available = ArrayHelper::getValue($item, ['@name', 'avail']);
        if ($available !== null) {
            return (bool)$available;
        }

        return null;
    }

    private function parsePrice($item)
    {
        $prices = ArrayHelper::getValue($item, ['fee']);
        if (is_array($prices)) {
            $price = array_shift($prices);
            if ($price >= 10) {
                return $price * 1.2;
            }
            return $price * 2;
        }

        return null;
    }

    /**
     * Parce premium domain
     *
     * Is the domain premium?
     *
     * @param $item Array
     * @return int|null
     */
    private function parsePremium($item)
    {
        $premium = null;

        if(!empty($item['class']))
            $item['class'] == 'premium' ? $premium = 1 : $premium = 0;

        return $premium;
    }

    private function prepare($names)
    {
        foreach ($names as $k => $name) {
            $names[$k] = $name . '.art';
        }

        return $names;
    }
}
