<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('company_groups', function (Blueprint $table) {
            $table->id(); // Cria 'id' como chave primária auto_increment
            $table->string('name');
            $table->timestamps(); // Cria 'created_at' e 'updated_at'
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_groups');
    }
}
