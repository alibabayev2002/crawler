<?php

namespace App\Console\Commands;

use App\Crawler\Adapters\BinaCrawlerAdapter;
use App\Crawler\Crawler;
use Illuminate\Console\Command;

class ParseAdvertise extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:advertises:bina';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $binaCrawler = new BinaCrawlerAdapter();

        $crawler = new Crawler($binaCrawler);
//        $progressBar = $this->output->createProgressBar();

        $crawler->parseAdvertises();

    }
}
