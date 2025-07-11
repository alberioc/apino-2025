<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    protected $table = 'vendas';

    // Se quiser, coloque os campos fillable:
    protected $fillable = [
        'produto',          // nome do produto
        'quantidade',
        'preco_total',
        'emissao_carbono',  // campo de emissão de CO2 em kg
        'data_venda'
    ];

    public $timestamps = false; // se não usa created_at / updated_at
}
