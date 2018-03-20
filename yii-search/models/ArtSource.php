<?php

namespace app\modules\search\models;

use \app\components\httpclient\HttpClientInterface;
use yii\helpers\ArrayHelper;

class ArtSource
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function searchDomains($name, $limit, $offset)
    {
        $result = $this->client->get(ArtResource::$search, [
            'name' => $name,
            'limit' => $limit,
            'offset' => $offset,
        ]);

        if ($result) {
            return $this->parseDomains($result);
        }

        return $result;
    }

    public function searchAchievements($name, $params, $similar)
    {
        $result = $this->client->get(ArtResource::$stat, [
            'name' => $name,
            'params' => $params,
            'similar' => $similar,
        ]);

        if ($result) {
            return $this->parseAchievements($result);
        }

        return $result;
    }

    private function parseAchievements($result)
    {
        $achievements = ArrayHelper::getValue($result, 'data', []);
        $achievements = array_filter($achievements);

        if (isset($achievements['busy1']) && isset($achievements['busy2'])) {
            if ($achievements['busy1'] > $achievements['busy2']) {
                unset($achievements['busy2']);
            } else {
                unset($achievements['busy1']);
            }
        }

        if (isset($achievements['arty']) && $achievements['arty'] > 0) {
            $achievements['arty'] = $achievements['arty'] + 6;
            $achievements['arty'] = $achievements['arty'] > 10 ? 10 : $achievements['arty'];
        }

        $achievements = ['early' => 10] + $achievements;

        return $achievements;
    }

    private function parseDomains($result)
    {
        $likeDomains = [];

        $data = ArrayHelper::getValue($result, 'like', []);
        foreach ($data as $item) {
            foreach ($item as $domain) {
                $likeDomains[$domain['name']] = $this->parseDomain($domain);
            }
        }

        $withDomains = [];

        $data = ArrayHelper::getValue($result, 'with', []);
        foreach ($data as $domain) {
            $withDomains[$domain['name']] = $this->parseDomain($domain);
        }

        $domains = ArrayHelper::merge($likeDomains, $withDomains);

        return $domains;
    }

    private function parseDomain($item)
    {
        return [
            'name' => $item['name'],
            'price' => $item['price'] * 1.2,
            'weight' => $item['weight'],
        ];
    }
}
