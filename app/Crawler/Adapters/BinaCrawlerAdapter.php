<?php

namespace App\Crawler\Adapters;

use App\Crawler\Crawler;
use App\Crawler\CrawlerAdapter;
use App\Crawler\CrawlerHelper;
use App\Models\Advertise;
use App\Models\Target;
use Exception;
use GuzzleHttp\Client;
use HeadlessChromium\Browser\ProcessAwareBrowser;
use HeadlessChromium\BrowserFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BinaCrawlerAdapter extends CrawlerAdapter
{

    private $domDocument;
    private $finder;


    public function __construct()
    {

    }

    public function parseLinks($finder): void
    {
        $linkNodes = CrawlerHelper::getClassNodes($finder, 'items_list');


        $links = [];

        foreach ($linkNodes as $node) {
            $check = true;
            try{
                $href = @$node->getElementsByTagName('a')[0]?->getAttribute('href');
            }catch (Exception $exception){
                continue;
            }
            if (!$href) {
                continue;
            }


            foreach ($node->childNodes[2]->childNodes as $div) {
                if ($div->getAttribute('class') == "products-label")
                    $check = false;
            }

            if (!$check)
                continue;

            $links[] = ['url' => 'https://bina.az' . $href];
        }


        Target::query()
            ->upsert($links, ['url']);
    }


    public function getMaxPageCount($finder): float
    {
        return ceil((int)CrawlerHelper::getClassNodes($finder, 'js-search-filters-items-count')[0]->data / 24);
    }

    public function parseAdvertise($finder, $domDocument, $url): void
    {
        $data = [];

        $identifier = explode('/', $url);
        $data['identifier'] = $identifier[array_key_last($identifier)];
        $phoneApiUrl = $url . '/phones';

        $domDocument2 = new \DOMDocument('1.0', 'UTF-8');
        $phoneApiHtml = CrawlerHelper::getHtml($phoneApiUrl);
        $domDocument2->loadHTML($phoneApiHtml);

        $json = null;

        if(app()->environment('production')){
            $json = $domDocument2->getElementsByTagName('pre')[0]->firstChild->data;
        }else{
            $json = $phoneApiHtml;
        }

        $data['phones'] = json_decode($json, true)['phones'];
        $data['price'] = (float)str_replace(' ', '', CrawlerHelper::getClassNodes($finder, 'price-val')[0]->data);
        $data['address'] = utf8_decode(CrawlerHelper::getClassNodes($finder, 'map_address')[0]->data);
        $data['username'] = utf8_decode(CrawlerHelper::getClassNodes($finder, 'contacts')[0]->firstChild->data);
        $data['name'] = utf8_decode(CrawlerHelper::getClassNodes($finder, 'services-container')[0]->firstChild->data);
        $description = '';
        foreach ($domDocument->getElementsByTagName('article')[0]->childNodes as $pTag){
            $description.= ' ' . $pTag->firstChild?->data;
        }
        $data['description'] = utf8_decode($description);
        $data['images'] = [];
        $thumbnails = CrawlerHelper::getClassNodes($finder, 'thumbnail', false);
        $disk = Storage::disk('digitalocean');

        foreach ($thumbnails as $thumbnail) {
            $href = $thumbnail->getAttribute('data-mfp-src');
            if ($href) {
                $info = pathinfo($href);
                $contents = file_get_contents($href);
                $file = '/tmp/' . $info['basename'];
                file_put_contents($file, $contents);
                $image = new UploadedFile($file, $info['basename']);
                $name = $disk->putFileAs('/advertises/b', $image, Str::slug($image->getClientOriginalName()) . '.' . $image->getClientOriginalExtension());
                $data['images'][] = $name;
            }
        }

        $table = CrawlerHelper::getClassNodes($finder, 'param_info',app()->environment('production'));

        $childNodes = $table[0]->childNodes[0]->childNodes;
        $attributes = [
            'Kateqoriya' => 'category',
            'Mərtəbə' => 'floor',
            'Sahə' => 'area',
            'Torpaq sahəsi' => 'land',
            'Çıxarış' => 'document_type',
            'Təmir' => 'repair',
            'Otaq sayı' => 'room_count',
        ];
        $data['additional'] = [];

        foreach ($childNodes as $tr) {
            $key = utf8_decode($tr->firstChild->firstChild->data);
            $value = mb_strtolower(utf8_decode($tr->childNodes[1]->firstChild->data));
            if ($key == 'Sahə' || $key == 'Torpaq sahəsi') {
                $value = (float)$value;
            }

            if (array_key_exists($key, $attributes)) {
                $data[$attributes[$key]] = $value;
            } else {
                $data['additional'][] = ['key' => $key, 'value' => $value];
            }
        }

        if (!(isset($data['room_count']) && $data['room_count'])) {
            return;
        }

        $map = $domDocument->getElementById('item_map');
        $data['longitude'] = $map->getAttribute('data-lng');
        $data['latitude'] = $map->getAttribute('data-lat');
        $data['url'] = $url;

        foreach ($data as $key => $item) {
            if (is_array($item)) {
                $data[$key] = json_encode($item);
            }
        }

        Advertise::query()
            ->upsert([$data], ['url']);

    }
}
