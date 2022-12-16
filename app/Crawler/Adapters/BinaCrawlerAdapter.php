<?php

namespace App\Crawler\Adapters;

use App\Crawler\CrawlerAdapter;
use App\Crawler\CrawlerHelper;
use App\Models\Advertise;
use App\Models\Target;
use GuzzleHttp\Client;

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
            $links[] = ['url' => 'https://bina.az' . $node->getElementsByTagName('a')[0]?->attributes[2]->value];
        }

        Target::query()
            ->upsert($links, ['url']);
    }


    public function getMaxPageCount($finder): float
    {
        return ceil((int)CrawlerHelper::getClassNodes($finder, 'js-search-filters-items-count')[0]->data / 6);
    }

    public function parseAdvertise($finder, $domDocument, $url): void
    {
        $data = [];

        $identifier = explode('/', $url);
        $data['identifier'] = $identifier[array_key_last($identifier)];
        $phoneApiUrl = $url . '/phones';
        $data['phones'] = json_decode(file_get_contents($phoneApiUrl), true)['phones'];
        $data['price'] = (float)CrawlerHelper::getClassNodes($finder, 'price-val')[0]->data;
        $data['address'] = utf8_decode(CrawlerHelper::getClassNodes($finder, 'map_address')[0]->data);
        $data['name'] = utf8_decode(CrawlerHelper::getClassNodes($finder, 'contacts')[0]->firstChild->data);
        $data['description'] = utf8_decode($domDocument->getElementsByTagName('article')[0]->firstChild->firstChild->data);

        $table = CrawlerHelper::getClassNodes($finder, 'param_info');

        $childNodes = $table[0]->childNodes;
        $attributes = [
            'Kateqoriya' => 'category',
            'Mərtəbə' => 'floor',
            'Sahə' => 'area',
            'Çıxarış' => 'document_type',
            'Təmir' => 'repair',
        ];
        $data['additional'] = [];

        foreach ($childNodes as $tr) {
            $key = utf8_decode($tr->firstChild->firstChild->data);
            $value = utf8_decode($tr->childNodes[1]->firstChild->data);
            if (array_key_exists($key, $attributes)) {
                $data[$attributes[$key]] = $value;
            } else {
                $data['additional'][] = ['key' => $key, 'value' => $value];
            }
        }
        $map = $domDocument->getElementById('item_map');
        $data['longitude'] = $map->getAttribute('data-lng');
        $data['latitude'] = $map->getAttribute('data-lat');
        $data['url'] = $url;

        Advertise::query()
            ->create($data);
    }
}
