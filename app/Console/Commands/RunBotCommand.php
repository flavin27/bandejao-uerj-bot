<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunBotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uerj:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'run the bot';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('STARTING BOT PROCESS...');

        $this->info('Fetching data from UERJ website...');

        $this->call('uerj:data');

        $this->info('Data fetched successfully!');

        $this->info('Posting data...');

        $this->call('uerj:post');

        $this->info('Data posted successfully!');
    }
}
