<?php

use App\Crawler\CrawlerHelper;
use App\Models\Advertise;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Artisan::command('districts', function () {


    $array = [
        34 => 154,
    ];

    foreach ($array as $key => $item) {
        Advertise::query()
            ->where('district',$key)
            ->update(['district'=>$item]);


    }

    \App\Models\District::query()
        ->whereIn('id',array_keys($array))->delete();

//    Advertise::query()
//        ->whereNull('district')
//        ->chunk(1000,function ($advertises,$key){
//            echo "Started key: {$key} \n";
//            foreach ($advertises as $advertise){
//                $distinctId = CrawlerHelper::getDistinctId($advertise->longitude, $advertise->latitude);
//                $advertise->update([
//                    'district' => $distinctId
//                ]);
//            }
//        });
});
