<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_group_venda', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_group_id');
            $table->string('pagante');
            $table->timestamps();

            $table->foreign('company_group_id')->references('id')->on('company_groups')->onDelete('cascade');
            $table->unique(['company_group_id', 'pagante']); // Evita duplicidade
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_group_venda');
    }
};
