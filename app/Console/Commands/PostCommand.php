<?php

namespace App\Console\Commands;

use App\Repositories\UerjRepository;
use App\Services\TwitterService;
use Illuminate\Console\Command;

class PostCommand extends Command
{
    private TwitterService $twitterService;

    private UerjRepository $uerjRepository;

    public function __construct(TwitterService $twitterService, UerjRepository $uerjRepository)
    {
        parent::__construct();
        $this->twitterService = $twitterService;
        $this->uerjRepository = $uerjRepository;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uerj:post';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post data to twitter';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Posting data to twitter...');

        $dataHoje = Date("d/m");
        $diaHoje = intval(Date("w")) -1;

        if ($diaHoje > 4 || $diaHoje < 0) {
            $this->info('Today is not a weekday, no data to post');
            return;
        }

        $cardapio = $this->uerjRepository->getCardapioDoDia($diaHoje);
        $cardapio = $cardapio[0];

        $payload = $dataHoje . "-" . $cardapio['dia_da_semana'] . PHP_EOL .
            "Saladas: " . $cardapio['saladas'] . PHP_EOL .
            "Prato principal: " . $cardapio['prato_principal'] . PHP_EOL .
            "Ovolactovegetariano: " . $cardapio['ovolactovegetariano'] . PHP_EOL .
            "Guarnição: " . $cardapio['guarnicao'] . PHP_EOL .
            "Acompanhamentos: " . $cardapio['acompanhamentos'] . PHP_EOL .
            "Sobremesa: " . $cardapio['sobremesa'];

        if (strlen($payload) > 280) {
            $tweet = $dataHoje . "-" . $cardapio['dia_da_semana'] . PHP_EOL .
                "Saladas: " . $cardapio['saladas'] . PHP_EOL .
                "Prato principal: " . $cardapio['prato_principal'] . PHP_EOL .
                "Ovolactovegetariano: " . $cardapio['ovolactovegetariano'] . PHP_EOL .
                "Guarnição: " . $cardapio['guarnicao'];
            $reply = "Acompanhamentos: " . $cardapio['acompanhamentos'] . PHP_EOL .
                "Sobremesa: " . $cardapio['sobremesa'];
            $responseTweet = $this->twitterService->post_tweet($tweet);
            if ($responseTweet->getStatusCode() != 200) {
                $this->error('Error posting data to twitter!');
                $this->info('Response: ' . $responseTweet->getContent());
                return;
            }
            $responseTweet = json_decode($responseTweet->getContent());

            $responseReply = $this->twitterService->post_reply($reply, $responseTweet->tweet_id);

            if ($responseReply->getStatusCode() != 200) {
                $this->error('Error posting data to twitter!');
                $this->info('Response: ' . $responseReply->getContent());
                return;
            }

            $this->info('Data posted successfully!');
            return;

        }

        $response = $this->twitterService->post_tweet($payload);

        if ($response->getStatusCode() != 200) {
            $this->error('Error posting data to twitter!');
            $this->info('Response: ' . $response->getContent());
            return;
        }

        $this->info('Data posted successfully!');
    }


}
