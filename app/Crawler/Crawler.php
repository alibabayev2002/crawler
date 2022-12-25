<?php

namespace App\Crawler;

use App\Models\Advertise;
use App\Models\Target;
use Exception;
use HeadlessChromium\BrowserFactory;

class Crawler
{
    private CrawlerAdapter $adapter;

    public function __construct(CrawlerAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fixInternalErrors($callback)
    {
        $internalErrors = libxml_use_internal_errors(true);
        $callback();
        libxml_use_internal_errors($internalErrors);
    }

    /**
     * @return void
     */
    public function parseLinks($url, $progressBar): void
    {
        $callback = function () use ($url, $progressBar) {
            $domDocument = new \DOMDocument('1.0', 'UTF-8');
            $domDocument->loadHTML(CrawlerHelper::getHtml($url));
            $finder = new \DomXPath($domDocument);

            $currentPage = CrawlerHelper::getPage($url);
            $maxPage = $this->adapter->getMaxPageCount($finder);
            $progressBar->start($maxPage);
            while ($currentPage < $maxPage) {
                $nextPageUrl = CrawlerHelper::getUrl($url, $currentPage++);
                $res = CrawlerHelper::getHtml($nextPageUrl);
                $domDocument = new \DOMDocument('1.0', 'UTF-8');
                $domDocument->loadHTML($res);
                $finder = new \DomXPath($domDocument);
                $this->adapter->parseLinks($finder);
                $progressBar->advance();
            }
        };

        $this->fixInternalErrors($callback);
    }

    static function getBrowser()
    {
        $socketFile = '/tmp/chrome-php-demo-socket';
        try {
            $socket = @file_get_contents($socketFile);
            $browser = BrowserFactory::connectToBrowser($socket);
        } catch (Exception $e) {
            // The browser was probably closed, start it again
            $factory = new BrowserFactory('chromium-browser');
            $browser = $factory->createBrowser([
                'keepAlive' => true,
                'headless' => true,
                'enableImages' => false
            ]);
            // save the uri to be able to connect again to browser
            \file_put_contents($socketFile, $browser->getSocketUri(), LOCK_EX);
        }

        return $browser;
    }

    public function parseAdvertises($step)
    {
        $callback = function () use ($step) {

            $url = Advertise::pluck('url')
                ->toArray();


            $targetsCount = Target::query()
                ->whereNotIn('url', $url)
                ->count();

            $targets = Target::query()
                ->whereNotIn('url', $url)
                ->get();


            foreach ($targets as $target) {
                $url = str_replace('.html', '', $target->url);
                try {
                    $res = CrawlerHelper::getHtml($url);
                    $domDocument = new \DOMDocument('1.0', 'UTF-8');
                    $domDocument->loadHTML($res);
                    $finder = new \DomXPath($domDocument);
                    $nodes = CrawlerHelper::getClassNodes($finder, 'ownership');
                    $ownership = utf8_decode(isset($nodes[0]) ? $nodes[0]->data : '');
                    $target->update(['status' => Target::PARSING]);
                    if ($ownership === 'mülkiyyətçi') {
                        echo "Saved: $url \n";
                        $this->adapter->parseAdvertise($finder, $domDocument, $url);
                    } else {
                        echo "Deleted: $url \n";
                    }
                    $target->update(['status' => Target::PARSED]);
                } catch (Exception $exception) {
//                    dd($exception->getMessage(),$exception->getLine());
////                    $target->update(['status' => Target::NOT_PARSED]);
                    echo "Error: $url \n";
                }
            }
        };
        $this->fixInternalErrors($callback);
    }
}
