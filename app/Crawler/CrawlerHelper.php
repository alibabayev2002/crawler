<?php

namespace App\Crawler;

use App\Models\District;
use Exception;

class CrawlerHelper
{
    /**
     * @param $url
     * @return int
     */
    public static function getPage($url): int
    {
        $queryString = parse_url($url, PHP_URL_QUERY);
        $queryArray = [];
        parse_str($queryString, $queryArray);
        return (int)$queryArray['page'] ?? 1;
    }

    /**
     * @param $url
     * @param $page
     * @return array|string
     */
    public static function getUrl($url, $page): array|string
    {
        $queryArray = [];
        $queryString = parse_url($url, PHP_URL_QUERY);
        parse_str($queryString, $queryArray);
        $queryArray['page'] = $page;
        return str_replace($queryString, http_build_query($queryArray), $url);
    }

    public static function getClassNodes($finder, $class, $childNodes = true)
    {
        if ($childNodes)
            return $finder->query("//*[contains(@class, '$class')]")[0]?->childNodes;
        else
            return $finder->query("//*[contains(@class, '$class')]");
    }


    public static function getIdNode($finder, $id)
    {
        return $finder->query("//*[contains(@id, '$id')]")[0]?->firstChild;
    }


    public static function getHtml($url)
    {
        if (app()->environment('production')) {
            $browser = Crawler::getBrowser();
            dd($browser);
            $page = $browser->createPage();
            $page->navigate($url)->waitForNavigation();
            $html = $page->getHtml();
            $page->close();
            return $html;
        }

        return file_get_contents($url);
    }

    public static function getDistinctId($long, $lat)
    {
        try {
            $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$lat},{$long}&sensor=false&key=AIzaSyB2Z6PwkrIeQkR5C45Zev71IJbyqOSfT5o";

            $result = json_decode(file_get_contents($url), true);

            if ($result["status"] !== "OK") {
                return null;
            }


            foreach ($result['results'] as $result) {
                $addressComponent = $result['address_components'];
                foreach ($addressComponent as $address) {
                    if (in_array("administrative_area_level_2", $address["types"])) {
                        $districtName = $address['long_name'];
                        $district = District::query()
                            ->where('name', $districtName)
                            ->first();

                        if (!$district) {
                            $district = District::query()
                                ->create([
                                    'name' => $districtName
                                ]);
                        }
                        return $district->id;
                    }
                }
            }
        } catch (Exception $exception) {
            return null;
        }

        return null;

    }

}

