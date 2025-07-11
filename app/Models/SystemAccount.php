<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemAccount extends Model
{
    protected $table = 'system_accounts';

    protected $fillable = [
        'name',
        // outros campos da tabela
    ];

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'systemAccountId'); 
        // 'systemAccountId' é o campo FK na tabela companies
    }
}
