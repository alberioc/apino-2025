<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyGroup extends Model
{
    protected $fillable = ['name'];

    public function pagantes()
    {
        return $this->hasMany(\App\Models\CompanyGroupVenda::class, 'company_group_id');
    }
}
