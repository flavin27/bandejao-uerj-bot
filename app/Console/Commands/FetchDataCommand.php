<?php

namespace App\Console\Commands;

use App\Services\UerjService;
use Illuminate\Console\Command;

class FetchDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uerj:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fetch data from UERJ website and store it in the database.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Fetching data from UERJ website...');

        $result = UerjService::scrape_data();

        dd($result);
    }
}
