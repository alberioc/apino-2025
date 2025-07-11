<?php

// app/Models/CompanyGroupVenda.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyGroupVenda extends Model
{
    protected $table = 'company_group_venda';

    protected $fillable = [
        'company_group_id',
        'pagante',
    ];
}
