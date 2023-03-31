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


Artisan::command('phones', function () {
    $advertises = Advertise::query()->whereNotNull('phones')->select('phones', 'username')->get();
    $path = public_path('phones.csv');
    $fp = fopen($path, 'w');
    foreach ($advertises as $advertise) {
        $phones = $advertise->phones;
        $nameString = $advertise->username;
        $nameArray = explode(' ', $nameString);
        $name = $nameArray[0];
        $surname = $nameArray[1] ?? null;
        foreach ($phones as $phone) {
            $phone = preg_replace('@[\D]@', '', $phone);
            $phone = preg_replace('@^0@', '994', $phone);
            $row = $surname ? [$phone,$name,$surname] : [$phone,$name];
            fputcsv($fp, $row);
        }

    }
    fclose($fp);
})->purpose('Display an inspiring quote');
