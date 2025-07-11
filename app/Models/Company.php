<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    protected $table = 'companies';

    protected $fillable = [
        'name',
        'systemAccountId',
        // outros campos
    ];

    public function systemAccount(): BelongsTo
    {
        return $this->belongsTo(SystemAccount::class, 'systemAccountId');
    }
}
