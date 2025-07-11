<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportStatusTable extends Migration
{
    public function up()
    {
        Schema::create('import_status', function (Blueprint $table) {
            $table->id();
            $table->string('nome_arquivo');
            $table->enum('status', ['pendente', 'processando', 'sucesso', 'erro'])->default('pendente');
            $table->integer('linhas_processadas')->nullable();
            $table->text('mensagem_erro')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('import_status');
    }
}
