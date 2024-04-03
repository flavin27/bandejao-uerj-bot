<?php

namespace App\Repositories;

use App\DTO\CardapioDTO;
use App\Models\Cardapio;
use App\Services\UerjService;
use Illuminate\Http\JsonResponse;

class UerjRepository
{

    private UerjService $uerjService;

    public function __construct(UerjService $uerjService)
    {
        $this->uerjService = $uerjService;
    }

    public function refreshDatabse(array $data): JsonResponse {

        $diaSemanaData = array_key_first($data);
        $currentData = Cardapio::all()->first();


        $currentDataDTO = new CardapioDTO(
            $currentData->dia_da_semana,
            $currentData->saladas,
            $currentData->prato_principal,
            $currentData->ovolactovegetariano,
            $currentData->guarnicao,
            $currentData->acompanhamentos,
            $currentData->sobremesa
        );

        $newDataDTO = new CardapioDTO(
            $diaSemanaData,
            $data[$diaSemanaData]['Saladas'],
            $data[$diaSemanaData]['Prato Principal'],
            $data[$diaSemanaData]['Ovolactovegetariano'],
            $data[$diaSemanaData]['Guarnição'],
            $data[$diaSemanaData]['Acompanhamentos'],
            $data[$diaSemanaData]['Sobremesa']
        );

        if (
            $currentDataDTO->dia_da_semana === $newDataDTO->dia_da_semana &&
            $currentDataDTO->saladas === $newDataDTO->saladas &&
            $currentDataDTO->prato_principal === $newDataDTO->prato_principal &&
            $currentDataDTO->ovolactovegetariano === $newDataDTO->ovolactovegetariano &&
            $currentDataDTO->guarnicao === $newDataDTO->guarnicao &&
            $currentDataDTO->acompanhamentos === $newDataDTO->acompanhamentos &&
            $currentDataDTO->sobremesa === $newDataDTO->sobremesa
        ) {
            return response()->json(['message' => 'Cardapio already updated!']);
        }






        $responseDeleted = UerjRepository::deleteAll();

        $responseCreated = [];

        foreach ($data as $dia => $cardapio) {
            $responseCreated = $this->store(new CardapioDTO(
                $dia,
                $cardapio['Saladas'],
                $cardapio['Prato Principal'],
                $cardapio['Ovolactovegetariano'],
                $cardapio['Guarnição'],
                $cardapio['Acompanhamentos'],
                $cardapio['Sobremesa']
            ));
        }

        if ($responseDeleted->getStatusCode() == 200 && $responseCreated->getStatusCode() == 200) {
            return response()->json(['message' => 'Cardapio updated successfully!']);
        }

        return response()->json(['message' => 'Error updating cardapio!']);


    }


    public function store(CardapioDTO $data): JsonResponse
    {
        $cardapio = new Cardapio();
        $cardapio->dia_da_semana = $data->dia_da_semana;
        $cardapio->saladas = $data->saladas;
        $cardapio->prato_principal = $data->prato_principal;
        $cardapio->ovolactovegetariano = $data->ovolactovegetariano;
        $cardapio->guarnicao = $data->guarnicao;
        $cardapio->acompanhamentos = $data->acompanhamentos;
        $cardapio->sobremesa = $data->sobremesa;
        $cardapio->save();
        return response()->json(['message' => 'Cardapio created successfully!']);
    }

    public static function deleteAll(): JsonResponse
    {
        $data = Cardapio::all();
        $data->each(fn ($item) => $item->delete());
        return response()->json(['message' => 'All cardapio deleted successfully!']);
    }

    public function getCardapioDoDia(int $dia): array
    {
        $diaDaSemana = $this->uerjService->getDiasDaSemanaKeys()[$dia];
        $cardapio = Cardapio::where('dia_da_semana', $diaDaSemana)->get();
        return $cardapio->toArray();
    }
}
