<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class UerjService
{

    private static string $url = "https://www.restauranteuniversitario.uerj.br/#cardapio";

    public function scrape_data(): array
    {
        $content = '';

        while (!$content) {
            try {
                $content = self::fetchContent();
            } catch (Exception $e) {
                Log::error("Erro ao recuperar dados do site da UERJ: " . $e->getMessage());
            }
        }
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($content);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $pratos = [];

        $element = $xpath->query("//*[@id='menu-1']")->item(0);
        if ($element) {
            $innerElements = $xpath->query(".//*[contains(@class, 'et_pb_text_inner')]", $element);
            if ($innerElements) {
                $dia = '';
                $data = '';

                foreach ($innerElements as $innerElement) {
                    $texto_corrigido = self::normalizeString($innerElement->textContent);

                    $dia = self::extractDia($texto_corrigido, $dia);
                    $data = self::extractData($texto_corrigido, $data);

                    self::processPrato($texto_corrigido, $dia, $data, $pratos);
                }
            }
        }
        $cardapioNaoFormatado =  array_chunk($pratos, 6);
        $cardapioFormatado = array_map(function ($prato) {
            $prato =  self::addGlutenAlert($prato);
            $prato = self::addLactoseAlert($prato);
            $prato = self::normalizeSpaces($prato);
            $prato[4] = self::addSpace($prato[4]);
            return $prato;
        }, $cardapioNaoFormatado);
        $keys = self::GetCardapioKeys();

        $caradapioFinal = array_map(function ($prato) use ($keys) {
            return array_combine($keys, $prato);
        }, $cardapioFormatado);

        $diasDaSemana = self::getDiasDaSemanaKeys();
        $caradapioFinal =  array_combine($diasDaSemana, $caradapioFinal);

        if (count($caradapioFinal) === 0) {
            echo "o site ainda não atualizou o cardápio da semana. Tente novamente mais tarde :P";
            die;
        }

        return $caradapioFinal;
    }

    public static function fetchContent(): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_DNS_SHUFFLE_ADDRESSES, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Erro cURL: ' . curl_error($ch);

        }
        curl_close($ch);

        return $content;
    }

    private static function normalizeString(string $string): string
    {
        $string = print_r($string, true);
        $string = str_replace(["\n", "\r"], '', $string);
        $string = str_replace('Ã', 'ã', $string);
        $string = str_replace('É', 'é', $string);
        $string = str_replace('À', 'à', $string);
        $string = str_replace('Í', 'í', $string);
        $string = str_replace('Ó', 'ó', $string);
        $string = str_replace('Ç', 'ç', $string);
        $string = str_replace('Ê', 'ê', $string);
        return $string;
    }

    private static function removeDateAndDay(string $string, string $day, string $date): string
    {
        $string = strtolower($string);
        $string = str_replace([$day, $date], '', $string);
        return $string;
    }

    private static function extractDia(string $texto_corrigido, string $dia): string
    {
        if (preg_match('/^(Segunda|Terça|Quarta|Quinta|Sexta)$/', $texto_corrigido)) {
            return $texto_corrigido;
        }
        return $dia;
    }

    private static function extractData(string $texto_corrigido, string $data): string
    {
        if (preg_match('/^\d{1,2} [A-Za-z]{3}$/', $texto_corrigido)) {
            return $texto_corrigido;
        }
        return $data;
    }

    private static function processPrato(string $texto_corrigido, string $dia, string $data, array &$pratos): void
    {
        if (preg_match('/^(Saladas|Prato Principal|Ovolactovegetariano|Guarnição|Acompanhamentos|Sobremesa)/', $texto_corrigido)) {
            $texto_corrigido = str_replace(array('Saladas', 'Prato Principal', 'Ovolactovegetariano', 'Guarnição', 'Acompanhamentos', 'Sobremesa'), '', $texto_corrigido);
            $pratos[] = self::removeDateAndDay($texto_corrigido, $dia, $data);
        }
    }

    public static function getCardapioDia(int $dia): array
    {
        $cardapio = self::scrape_data();
        if (count($cardapio) === 0 || $dia < 0 || $dia > 4 || !isset($cardapio[$dia])) {
            return [];
        }

//        $string = self::addSpace($cardapio[$dia][4]);
//        $cardapio[$dia][4] = $string;
//
//        $cardapio[$dia] = self::addGlutenAlert($cardapio[$dia]);
//        $cardapio[$dia] = self::addLactoseAlert($cardapio[$dia]);
//        $cardapio[$dia] = self::normalizeSpaces($cardapio[$dia]);


        return $cardapio[$dia];
    }

    public static function addSpace(string $string): string
    {
        $resultado = preg_replace('/(parboilizado)(arroz)/', '$1, $2', $string);

        $stringCorrigida = preg_replace('/(integral)(feijão)/', '$1, $2', $resultado);

        return $stringCorrigida;
    }

    public static function addGlutenAlert(array $cardapio): array
    {
        $stringsFormatadas = [];

        foreach ($cardapio as $prato) {
            $resultado = preg_replace('/(glúten)/i', ' ($1) ', $prato);

            $stringsFormatadas[] = $resultado;
        }


        return $stringsFormatadas;
    }

    public static function addLactoseAlert(array $cardapio): array
    {
        $stringsFormatadas = [];

        foreach ($cardapio as $prato) {
            $resultado = preg_replace('/(lactose)/i', ' ($1) ', $prato);

            $stringsFormatadas[] = $resultado;
        }

        return $stringsFormatadas;
    }

    public static function normalizeSpaces(array $cardapio): array
    {
        $stringCorrigida = [];

        foreach ($cardapio as $prato) {
            $resultado = preg_replace('/\s{2,}/u', ' ', $prato);

            $stringCorrigida[] = $resultado;


        }
        return $stringCorrigida;
    }

    public static function GetCardapioKeys(): array
    {
        return ['Saladas', 'Prato Principal', 'Ovolactovegetariano', 'Guarnição', 'Acompanhamentos', 'Sobremesa'];
    }

    public static function getDiasDaSemanaKeys(): array
    {
        return ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta'];
    }
}
