<?php

namespace App\Crawler;

use HeadlessChromium\BrowserFactory;

abstract class CrawlerAdapter
{
    /**
     * @return void
     */
    abstract public function parseLinks($url): void;

    abstract public function parseAdvertise($finder, $domDocument, $url): void;

}
