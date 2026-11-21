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
     * Usuários: endereços, usuários e as especializações leitor e bibliotecário
     * (Modelo Físico, seção Usuários). A exclusividade da especialização (R17)
     * é garantida pela FK composta (usuario_id, perfil).
     */
    public function up(): void
    {
        // Violação aceita da 3NF: cep → cidade, uf
        Schema::create('enderecos', function (Blueprint $table) {
            $table->integer('id')->generatedAs()->always()->primary();
            $table->string('logradouro', 200);
            $table->string('numero', 10);
            $table->string('complemento', 100)->nullable();
            $table->string('bairro', 100);
            $table->string('cidade', 100);
            $table->char('uf', 2);
            $table->char('cep', 8);
            $table->timestampTz('criado_em')->nullable();
            $table->timestampTz('atualizado_em')->nullable();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->integer('id')->generatedAs()->always()->primary();
            $table->string('nome', 200);
            $table->char('cpf', 11)->unique();
            $table->string('email', 254)->unique();
            $table->string('telefone', 20)->nullable();
            $table->integer('endereco_id');
            $table->date('data_cadastro')->default(DB::raw('CURRENT_DATE'));
            $table->string('senha', 255);
            $table->string('token_lembrar', 100)->nullable();
            $table->string('perfil', 15);
            $table->string('situacao', 10)->default('ATIVO');
            $table->timestampTz('criado_em')->nullable();
            $table->timestampTz('atualizado_em')->nullable();
            $table->unique(['id', 'perfil']);
            $table->foreign('endereco_id')->references('id')->on('enderecos');
            $table->index('endereco_id', 'ix_usuarios_endereco');
        });

        Schema::create('leitores', function (Blueprint $table) {
            $table->integer('usuario_id')->primary();
            $table->string('perfil', 15)->default('LEITOR');
            $table->timestampTz('criado_em')->nullable();
            $table->timestampTz('atualizado_em')->nullable();
            $table->foreign(['usuario_id', 'perfil'])->references(['id', 'perfil'])->on('usuarios')->cascadeOnDelete();
        });

        Schema::create('bibliotecarios', function (Blueprint $table) {
            $table->integer('usuario_id')->primary();
            $table->string('perfil', 15)->default('BIBLIOTECARIO');
            $table->timestampTz('criado_em')->nullable();
            $table->timestampTz('atualizado_em')->nullable();
            $table->foreign(['usuario_id', 'perfil'])->references(['id', 'perfil'])->on('usuarios')->cascadeOnDelete();
        });

        DB::statement("ALTER TABLE enderecos ADD CONSTRAINT ck_enderecos_uf CHECK (uf IN (
            'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA',
            'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'))");
        DB::statement("ALTER TABLE enderecos ADD CONSTRAINT ck_enderecos_cep CHECK (cep ~ '^[0-9]{8}$')");
        DB::statement("ALTER TABLE usuarios ADD CONSTRAINT ck_usuarios_cpf CHECK (cpf ~ '^[0-9]{11}$')");
        DB::statement("ALTER TABLE usuarios ADD CONSTRAINT ck_usuarios_email CHECK (email LIKE '_%@_%')");
        DB::statement("ALTER TABLE usuarios ADD CONSTRAINT ck_usuarios_perfil CHECK (perfil IN ('LEITOR', 'BIBLIOTECARIO'))");
        DB::statement("ALTER TABLE usuarios ADD CONSTRAINT ck_usuarios_situacao CHECK (situacao IN ('ATIVO', 'INATIVO'))");
        DB::statement("ALTER TABLE leitores ADD CONSTRAINT ck_leitores_perfil CHECK (perfil = 'LEITOR')");
        DB::statement("ALTER TABLE bibliotecarios ADD CONSTRAINT ck_bibliotecarios_perfil CHECK (perfil = 'BIBLIOTECARIO')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bibliotecarios');
        Schema::dropIfExists('leitores');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('enderecos');
    }
};
