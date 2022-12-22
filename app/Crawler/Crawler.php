<?php

namespace App\Crawler;

use App\Models\Target;
use Exception;
use HeadlessChromium\BrowserFactory;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class Crawler
{
    private CrawlerAdapter $adapter;
    private HttpBrowser $browser;

    public function __construct(CrawlerAdapter $adapter)
    {
        $this->adapter = $adapter;

        $domain = 'bina.az';
        $jar = new CookieJar();
        $jar->set(new Cookie('_name_session', 'value', null, null, $domain));
        $client = HttpClient::create([
            'timeout' => 900,
            'verify_peer' => false
        ]);
        $this->browser = new HttpBrowser($client, null, $jar);
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
            $crawler = $this->browser->request('GET', $url);
            $res = $crawler->html();
            $domDocument = new \DOMDocument('1.0', 'UTF-8');
            $domDocument->loadHTML($res);
            $finder = new \DomXPath($domDocument);

            $currentPage = CrawlerHelper::getPage($url);
            $maxPage = $this->adapter->getMaxPageCount($finder);
            $progressBar->start($maxPage);
            while ($currentPage < $maxPage) {
                $nextPageUrl = CrawlerHelper::getUrl($url, $currentPage++);
                $res = $this->browser->request('GET', $nextPageUrl)->html();
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
                    $res = $this->browser->request('GET', $url)->html();
                    $domDocument = new \DOMDocument('1.0', 'UTF-8');
                    $domDocument->loadHTML($res);
                    $finder = new \DomXPath($domDocument);
                    $nodes = CrawlerHelper::getClassNodes($finder, 'ownership');
                    $ownership = utf8_decode(isset($nodes[0]) ? $nodes[0]->data : '');
                    if ($ownership === 'mülkiyyətçi') {
                        echo "Saved: $url \n";
//                        $target->update(['status' => Target::PARSING]);
                        $this->adapter->parseAdvertise($finder, $domDocument, $url, $this->browser);
                        $target->update(['status' => Target::PARSED]);
                    } else {
                        echo "Deleted: $url \n";
                        $target->delete();
                    }
                } catch (Exception $exception) {
//                    $target->update(['status' => Target::NOT_PARSED]);
                    dump($exception->getMessage(), $exception->getFile(), $exception->getLine(), $url);
                } finally {
                    $browser->close();
                }
            }
        };
        $this->fixInternalErrors($callback);
    }
}
