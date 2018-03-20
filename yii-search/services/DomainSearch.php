<?php

namespace app\modules\search\services;

use yii\helpers\ArrayHelper;
use app\modules\search\models\ArtSource;
use app\modules\search\models\CentralNicSource;
use app\modules\search\models\DomainPrefix;
use app\helpers\DomainName;
use app\helpers\ArrayUtils;

/**
 * Domain Search
 */
class DomainSearch
{
    /**
     * @var ArtSource
     */
    private $artSource;
    /**
     * @var CentralNicSource
     */
    private $centralNicSource;

    public function __construct(ArtSource $artSource, CentralNicSource $centralNicSource)
    {
        $this->artSource = $artSource;
        $this->centralNicSource = $centralNicSource;
    }

    private function emptyDomainItem($domain)
    {
        return [
            'name' => $domain,
            'price' => null,
            'medal' => false,
            'achievements' => [],
            'available' => false,
            'isRandom' => false,
        ];
    }

    public function searchDomain($domain)
    {
        $domainItem = $this->emptyDomainItem($domain);

        $centralNicData = $this->centralNicSearchDomains([$domain]);
        $centralNicData = array_shift($centralNicData);
        if ($centralNicData) {
            $domainItem['price'] = $centralNicData['price'];
            $domainItem['available'] = $centralNicData['available'];
            $domainItem['premium'] = $centralNicData['premium'];
        }

        if (DomainName::isLatinDomain($domain) === false) {
            return $domainItem;
        }

        $variants = $this->artSearchDomains($domain, 1, 0);
        $artData = ArrayHelper::getValue($variants, $domain, null);
        if ($artData !== null) {
            $domainItem['price'] = $artData['price'];
        }

        $domainItem['achievements'] = $this->artSearchAchievements($domain);
        $domainItem['medal'] = $this->isMedal($domainItem);

        return $domainItem;
    }

    public function searchVariants($domain, $offset = 0, $sortBy = 'weight', $sortMode = SORT_DESC)
    {
        $domainItems = [];

        $variants = $this->artSearchDomains($domain, 3, $offset);

        if (count($variants)) {
            if ($sortBy !== null) {
                ArrayHelper::multisort($variants, $sortBy, $sortMode);
            }
            foreach ($variants as $variant) {
                $domainItem = $this->emptyDomainItem($variant['name']);

                $domainItem['price'] = $variant['price'];
                $domainItem['achievements'] = $this->artSearchAchievements($variant['name']);
                $domainItem['medal'] = count($domainItem['achievements']) > 0;

                $domainItems[] = $domainItem;
            }
        }

        $domainItems = $this->generateRandomDomains($domain, $domainItems, $offset);
        $domainItems = ArrayHelper::index($domainItems, 'name');
        $domainNames = array_keys($domainItems);

        $centralNicData = $this->centralNicSearchDomains($domainNames);

        if (is_array($centralNicData)) {
            foreach ($centralNicData as $centralNicItem) {
                $domainItem = $domainItems[$centralNicItem['name']];

                if ($centralNicItem['available'] !== null) {
                    if ($centralNicItem['available'] === false) {
                        unset($domainItems[$centralNicItem['name']]);
                        continue;
                    }
                    $domainItem['available'] = $centralNicItem['available'];
                    $domainItem['premium'] = $centralNicItem['premium'];
                }

                if ($domainItem['isRandom'] === true) {
                    $domainItem['price'] = $centralNicItem['price'];
                    if ($domainItem['price'] > 20) {
                        $domainItem['achievements'] = $this->artSearchAchievements($domainItem['name']);
                        $domainItem['medal'] = $this->isMedal($domainItem);
                    }
                }

                $domainItems[$centralNicItem['name']] = $domainItem;
            }
        }

        return $domainItems;
    }

    public function getDomains($domains = '')
    {
        if($domains == '')
            return;

        $domainsArray = explode(',', $domains);
        $domainItems = $this->centralNicSearchDomains($domainsArray);

        return $domainItems;
    }


    /**
     * Генерирует доменные имена на основе искомого домена.
     *
     * @param $domain - искомый (стартовый) домен
     * @param $domainItems - массив уже сгенерированных ранее доменных имен, с помощью API (artSearchDomains)
     * @param $offset - кол-во шагов (кол-во раз, нажатых на кнопку "посмотреть еще варианты")
     * @return array
     */
    private function generateRandomDomains($domain, $domainItems, $offset)
    {
        $randomVariantsCount = 12 - count($domainItems);
        $randomVariants = $this->generateNames($domain, $randomVariantsCount, $offset);

        $randomVariantsItems = [];
        foreach ($randomVariants as $randomVariant) {
            $domainItem = $this->emptyDomainItem($randomVariant);
            $domainItem['isRandom'] = true;

            $randomVariantsItems[] = $domainItem;
        }

        if (count($domainItems)) {
            $domainItems = ArrayUtils::shuffle($domainItems, $randomVariantsItems);
        } else {
            $domainItems = $randomVariantsItems;
        }

        return $domainItems;
    }

    private function generateNames($domain, $count, $offset)
    {
        $cacheKey = 'domain-prefix-' . $count . '-' . $offset;
        $prefixes = \Yii::$app->cache->getOrSet($cacheKey, function () use ($count, $offset) {
            return DomainPrefix::find()
                ->limit($count)
                ->offset($offset * $count)
                ->asArray()
                ->all();
        }, 24 * 3600);

        $names = [];
        foreach ($prefixes as $prefix) {
            $name = str_replace('+', $domain, $prefix['title']);
            $names[] = $name;
        }

        return $names;
    }

    private function artSearchAchievements($domain)
    {
        $achievements = $this->artSource->searchAchievements($domain, [], false);
        if ($achievements === false || !count($achievements)) {
            return [];
        }

        return $achievements;
    }

    private function artSearchDomains($domain, $limit, $offset)
    {
        $domains = $this->artSource->searchDomains($domain, $limit, $offset * $limit);
        if ($domains === false || !count($domains)) {
            return [];
        }

        return $domains;
    }

    private function centralNicSearchDomains($domains)
    {
        $domains = array_map('idn_to_ascii', $domains);
        return $this->centralNicSource->check($domains);
    }

    /**
     * Выдаем медаль домену (значек в общем списке доменов при поиске).
     * Пока только для премиальных. ПРи необходимости - дописать
     *
     * @param $domainData
     * @return bool
     */
    private function isMedal($domainData)
    {
        $medal = false;

        if(!empty($domainData['premium']))
            $medal = true;

        return $medal;
    }
}
