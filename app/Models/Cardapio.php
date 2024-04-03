<?php

namespace App\Models;

use App\DTO\CardapioDTO;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;


class Cardapio extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'cardapio';

    protected $fillable = [
        'dia_da_semana',
        'saladas',
        'prato_principal',
        'ovolactovegetariano',
        'guarnicao',
        'acompanhamentos',
        'sobremesa',
    ];

    public static function toDTO($data): CardapioDTO
    {
        return new CardapioDTO(
            $data['dia_da_semana'],
            $data['saladas'],
            $data['prato_principal'],
            $data['ovolactovegetariano'],
            $data['guarnicao'],
            $data['acompanhamentos'],
            $data['sobremesa'],
        );
    }

}
