<?php

namespace App\Crawler;

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
            $page = $browser->createPage();
            $page->navigate($url)->waitForNavigation();
            $html = $page->getHtml();
            $page->close();
            return $html;
        }

        return file_get_contents($url);
    }

}

