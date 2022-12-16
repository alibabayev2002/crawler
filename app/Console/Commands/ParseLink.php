<?php

namespace App\Console\Commands;

use App\Crawler\Adapters\BinaCrawlerAdapter;
use App\Crawler\Crawler;
use Illuminate\Console\Command;

class ParseLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:links:bina';

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
        $progressBar = $this->output->createProgressBar();

        $crawler->parseLinks('https://bina.az/alqi-satqi?page=2',$progressBar);
    }
}
