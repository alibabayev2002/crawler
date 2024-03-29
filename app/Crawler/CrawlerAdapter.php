<?php

namespace App\Crawler;

use HeadlessChromium\BrowserFactory;

abstract class CrawlerAdapter
{
    /**
     * @return void
     */
    abstract public function parseLinks($finder): void;

    abstract public function parseAdvertise($finder, $domDocument, $url): void;

}
