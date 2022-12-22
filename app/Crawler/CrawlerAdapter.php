<?php

namespace App\Crawler;

use HeadlessChromium\BrowserFactory;
use Symfony\Component\BrowserKit\HttpBrowser;

abstract class CrawlerAdapter
{
    /**
     * @return void
     */
    abstract public function parseLinks($url): void;

    abstract public function parseAdvertise($finder, $domDocument, $url, HttpBrowser $browser): void;

}
