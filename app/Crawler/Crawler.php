<?php

namespace App\Crawler;

use App\Models\Target;
use Exception;

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

    public function parseAdvertises()
    {
        $callback = function () {
            $targets = Target::query()
                ->where('status', Target::NOT_PARSED)
                ->take(10000)
                ->get();

            foreach ($targets as $target) {
                $url = $target->url;
                try {
                    $res = file_get_contents($url);
                    $domDocument = new \DOMDocument('1.0', 'UTF-8');
                    $domDocument->loadHTML($res);
                    $finder = new \DomXPath($domDocument);
                    $nodes = CrawlerHelper::getClassNodes($finder, 'ownership');
                    $ownership = utf8_decode(isset($nodes[0]) ? $nodes[0]->data : '');
                    if ($ownership === 'mülkiyyətçi') {
                        $this->adapter->parseAdvertise($finder, $domDocument, $url);
                        $target->update(['status' => Target::PARSED]);
                    } else {
                        $target->delete();
                    }
                }catch (Exception $exception){
//                    echo $exception->getMessage()
                }
            }
        };
        $this->fixInternalErrors($callback);
    }
}
