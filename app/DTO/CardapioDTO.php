<?php

namespace App\DTO;

readonly class CardapioDTO
{

    public string $dia_da_semana;
    public string $saladas;
    public string $prato_principal;
    public string $ovolactovegetariano;
    public string $guarnicao;
    public string $acompanhamentos;
    public string $sobremesa;

    public function __construct(
        string $dia_da_semana,
        string $saladas,
        string $prato_principal,
        string $ovolactovegetariano,
        string $guarnicao,
        string $acompanhamentos,
        string $sobremesa
    ) {
        $this->dia_da_semana = $dia_da_semana;
        $this->saladas = $saladas;
        $this->prato_principal = $prato_principal;
        $this->ovolactovegetariano = $ovolactovegetariano;
        $this->guarnicao = $guarnicao;
        $this->acompanhamentos = $acompanhamentos;
        $this->sobremesa = $sobremesa;
    }

}
