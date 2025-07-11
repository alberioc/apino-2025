<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    protected $table = 'vendas';

    protected $fillable = [
        'venda_numero',
        'vendedor',
        'data_venda',
        'pagante',
        'data_inicio',
        'produto',
        'fornecedor',
        'representante',
        'valor_total',
        'segmento',
        'tipo_de_viajante_eventos',
        'data_fim',
        'hora_inicio',
        'hora_fim',
        'diarias',
        'quantidade',
        'documento',
        'tipo_acomodacao',
        'regime',
        'categoria_quarto',
        'categoria_veiculo',
        'local_retirada',
        'local_devolucao',
        'destino',
        'tipo_pessoa',
        'situacao',
        'solicitante',
        'receitas',
        'faturamento',
        'cpf',
        'cnpj',
        'aprovador',
        'email',
        'celular',
        'telefone',
        'numero_notas_fiscais',
        'passageiros',
        'cidade_fornecedor',
        'trechos',
    ];

    public $timestamps = false; // ou true, dependendo do seu esquema
}
