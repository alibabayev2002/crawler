<?php

namespace App\Crawler;

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
            $res = file_get_contents($url);
            $domDocument = new \DOMDocument('1.0', 'UTF-8');
            $domDocument->loadHTML($res);
            $finder = new \DomXPath($domDocument);

            $currentPage = CrawlerHelper::getPage($url);
            $maxPage = $this->adapter->getMaxPageCount($finder);
            $progressBar->start($maxPage);
            while ($currentPage < $maxPage) {
                $nextPageUrl = CrawlerHelper::getUrl($url, $currentPage++);
                $res = file_get_contents($nextPageUrl);
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

    public function parseAdvertises()
    {
        $callback = function () {
            $targets = Target::query()
                ->where('status', Target::NOT_PARSED)
                ->take(10000)
                ->get();


            $browser = self::getBrowser();


            foreach ($targets as $target) {
                $url = $target->url;
                try {
                    $page = $browser->createPage();
                    $page->navigate($url)->waitForNavigation();
                    $res = $page->getHtml();
                    $page->close();
                    $domDocument = new \DOMDocument('1.0', 'UTF-8');
                    $domDocument->loadHTML($res);
                    $finder = new \DomXPath($domDocument);
                    $nodes = CrawlerHelper::getClassNodes($finder, 'ownership');
                    $ownership = utf8_decode(isset($nodes[0]) ? $nodes[0]->data : '');
                    if ($ownership === 'mülkiyyətçi') {
                        echo "Saved: $url \n";
//                        $target->update(['status' => Target::PARSING]);
                        $this->adapter->parseAdvertise($finder, $domDocument, $url);
                        $target->update(['status' => Target::PARSED]);
                    } else {
                        echo "Deleted: $url \n";
                        $target->delete();
                    }
                } catch (Exception $exception) {
//                    $target->update(['status' => Target::NOT_PARSED]);
                    dump($exception->getMessage(), $exception->getFile(), $exception->getLine(), $url);
                }
            }
        };
        $this->fixInternalErrors($callback);
    }
}
