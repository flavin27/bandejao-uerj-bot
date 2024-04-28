<?php

namespace App\Console\Commands;

use App\Repositories\UerjRepository;
use App\Services\UerjService;
use Illuminate\Console\Command;

class FetchDataCommand extends Command
{

    private UerjService $uerjService;
    private UerjRepository $uerjRepository;

    public function __construct(UerjService $uerjService, UerjRepository $uerjRepository)
    {
        parent::__construct();
        $this->uerjService = $uerjService;
        $this->uerjRepository = $uerjRepository;
    }
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

        $dataHoje = Date("d/m");
        $diaHoje = intval(Date("w")) -1;

        if ($diaHoje > 4 || $diaHoje < 0) {
            $this->info('Today is not a weekday, no data to fetch');
            return;
        }

        $result = $this->uerjService->scrape_data();

        $this->info('Data fetched successfully!');

        $this->info('Storing data in the database...');

        $response = $this->uerjRepository->refreshDatabse($result);

        $this->info($response->getContent());

        $this->info('Data stored successfully!');
    }
}
