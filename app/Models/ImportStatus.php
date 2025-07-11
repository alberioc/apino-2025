<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportStatus extends Model
{
    protected $table = 'import_status';

    protected $casts = [
        'iniciado_em' => 'datetime',
        'processado_em' => 'datetime',
    ];

    protected $fillable = [
        'nome_arquivo',
        'email',
        'status',
        'iniciado_em',
        'processado_em',
        'linhas_processadas',
        'mensagem_erro',
    ];
}
