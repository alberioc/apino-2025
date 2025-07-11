<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresaMonde extends Model
{
    protected $table = 'empresa_monde';

    protected $fillable = [
        'systemAccountId',
        'nome',
    ];
}
