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
        $factory = new BrowserFactory('chromium-browser');
        return $factory->createBrowser([
            'headless' => true,
            'enableImages' => false
        ]);
    }

    public function parseAdvertises($step)
    {
        $callback = function () use($step){
            $targetsCount = Target::query()
                ->whereNot('status', Target::PARSED)->count();



            $targets = Target::query()
                ->whereNot('status', Target::PARSED)
                ->orderByDesc('id')
                ->take(10000)
                ->paginate((int)ceil($targetsCount / 4),page:$step);





            foreach ($targets as $target) {
                $url = str_replace('.html','',$target->url);
                try {
                    $res = CrawlerHelper::getHtml($url);
                    $domDocument = new \DOMDocument('1.0', 'UTF-8');
                    $domDocument->loadHTML($res);
                    $finder = new \DomXPath($domDocument);
                    $nodes = CrawlerHelper::getClassNodes($finder, 'ownership');
                    $ownership = utf8_decode(isset($nodes[0]) ? $nodes[0]->data : '');
                    if ($ownership === 'mülkiyyətçi') {
                        echo "Saved: $url \n";
                        $target->update(['status' => Target::PARSING]);
                        $this->adapter->parseAdvertise($finder, $domDocument, $url);
                        $target->update(['status' => Target::PARSED]);
                    } else {
                        echo "Deleted: $url \n";
                        $target->delete();
                    }
                } catch (Exception $exception) {
////                    $target->update(['status' => Target::NOT_PARSED]);
                    echo "Error: $url \n";
                }
            }
        };
        $this->fixInternalErrors($callback);
    }
}
