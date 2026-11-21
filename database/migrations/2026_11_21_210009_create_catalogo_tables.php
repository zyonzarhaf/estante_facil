<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Catálogo: obras, autores, categorias, editoras, edições e exemplares
     * (Modelo Físico, seção Catálogo).
     */
    public function up(): void
    {
        Schema::create('obras', function (Blueprint $table) {
            $table->integer('id')->generatedAs()->always()->primary();
            $table->string('titulo', 255);
            $table->string('subtitulo', 255)->nullable();
            $table->string('idioma_original', 50)->nullable();
            $table->string('url_acesso', 500)->nullable();
            $table->timestampTz('criado_em')->nullable();
            $table->timestampTz('atualizado_em')->nullable();
        });

        Schema::create('autores', function (Blueprint $table) {
            $table->integer('id')->generatedAs()->always()->primary();
            $table->string('nome', 200);
            $table->timestampTz('criado_em')->nullable();
            $table->timestampTz('atualizado_em')->nullable();
        });

        Schema::create('obras_autores', function (Blueprint $table) {
            $table->integer('obra_id');
            $table->integer('autor_id');
            $table->primary(['obra_id', 'autor_id']);
            $table->foreign('obra_id')->references('id')->on('obras')->cascadeOnDelete();
            $table->foreign('autor_id')->references('id')->on('autores');
            $table->index('autor_id', 'ix_obras_autores_autor');
        });

        Schema::create('categorias', function (Blueprint $table) {
            $table->integer('id')->generatedAs()->always()->primary();
            $table->string('nome', 100)->unique();
            $table->timestampTz('criado_em')->nullable();
            $table->timestampTz('atualizado_em')->nullable();
        });

        Schema::create('obras_categorias', function (Blueprint $table) {
            $table->integer('obra_id');
            $table->integer('categoria_id');
            $table->primary(['obra_id', 'categoria_id']);
            $table->foreign('obra_id')->references('id')->on('obras')->cascadeOnDelete();
            $table->foreign('categoria_id')->references('id')->on('categorias');
            $table->index('categoria_id', 'ix_obras_categorias_categoria');
        });

        Schema::create('editoras', function (Blueprint $table) {
            $table->integer('id')->generatedAs()->always()->primary();
            $table->string('nome', 200)->unique();
            $table->timestampTz('criado_em')->nullable();
            $table->timestampTz('atualizado_em')->nullable();
        });

        Schema::create('edicoes', function (Blueprint $table) {
            $table->integer('id')->generatedAs()->always()->primary();
            $table->char('isbn', 13)->unique();
            $table->integer('obra_id');
            $table->integer('editora_id');
            $table->smallInteger('numero_edicao');
            $table->smallInteger('ano_publicacao')->nullable();
            $table->smallInteger('numero_paginas')->nullable();
            $table->string('idioma', 50)->nullable();
            $table->timestampTz('criado_em')->nullable();
            $table->timestampTz('atualizado_em')->nullable();
            $table->unique(['obra_id', 'editora_id', 'numero_edicao']);
            $table->foreign('obra_id')->references('id')->on('obras');
            $table->foreign('editora_id')->references('id')->on('editoras');
            $table->index('editora_id', 'ix_edicoes_editora');
        });

        Schema::create('exemplares', function (Blueprint $table) {
            $table->integer('id')->generatedAs()->always()->primary();
            $table->string('codigo_tombo', 30)->unique();
            $table->integer('edicao_id');
            $table->date('data_aquisicao')->default(DB::raw('CURRENT_DATE'));
            $table->string('estado_conservacao', 20);
            $table->boolean('indisponivel')->default(false);
            $table->timestampTz('criado_em')->nullable();
            $table->timestampTz('atualizado_em')->nullable();
            $table->foreign('edicao_id')->references('id')->on('edicoes');
            $table->index('edicao_id', 'ix_exemplares_edicao');
        });

        DB::statement("ALTER TABLE edicoes ADD CONSTRAINT ck_edicoes_isbn CHECK (isbn ~ '^[0-9]{13}$')");
        DB::statement('ALTER TABLE edicoes ADD CONSTRAINT ck_edicoes_numero_edicao CHECK (numero_edicao > 0)');
        DB::statement('ALTER TABLE edicoes ADD CONSTRAINT ck_edicoes_ano_publicacao CHECK (ano_publicacao > 0)');
        DB::statement('ALTER TABLE edicoes ADD CONSTRAINT ck_edicoes_numero_paginas CHECK (numero_paginas > 0)');
        DB::statement("ALTER TABLE exemplares ADD CONSTRAINT ck_exemplares_estado_conservacao CHECK (estado_conservacao IN ('NOVO', 'BOM', 'REGULAR', 'RUIM'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exemplares');
        Schema::dropIfExists('edicoes');
        Schema::dropIfExists('editoras');
        Schema::dropIfExists('obras_categorias');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('obras_autores');
        Schema::dropIfExists('autores');
        Schema::dropIfExists('obras');
    }
};
