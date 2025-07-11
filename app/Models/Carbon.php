<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carbon extends Model
{
    protected $table = 'vendas';

    // Se quiser, coloque os campos fillable:
    protected $fillable = [
        'pagante',          // nome do produto
        'passageiros',
        'documento',
        'emissao_carbono',  // campo de emissão de CO2 em kg
        'data_venda'
    ];

    public $timestamps = false; // se não usa created_at / updated_at
}
