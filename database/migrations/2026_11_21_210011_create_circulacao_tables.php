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
     * Circulação: empréstimos, renovações, multas, reservas e o vínculo
     * reserva → empréstimo (Modelo Físico, seção Circulação).
     */
    public function up(): void
    {
        Schema::create('emprestimos', function (Blueprint $table) {
            $table->integer('id')->generatedAs()->always()->primary();
            $table->integer('exemplar_id');
            $table->integer('leitor_id');
            $table->string('situacao', 20);
            $table->date('data_limite_retirada')->nullable();
            $table->date('data_retirada')->nullable();
            $table->date('data_prevista_devolucao')->nullable();
            $table->date('data_devolucao')->nullable();
            $table->integer('bibliotecario_retirada_id')->nullable();
            $table->integer('bibliotecario_devolucao_id')->nullable();
            $table->timestampTz('criado_em')->nullable();
            $table->timestampTz('atualizado_em')->nullable();
            $table->foreign('exemplar_id')->references('id')->on('exemplares');
            $table->foreign('leitor_id')->references('usuario_id')->on('leitores');
            $table->foreign('bibliotecario_retirada_id')->references('usuario_id')->on('bibliotecarios');
            $table->foreign('bibliotecario_devolucao_id')->references('usuario_id')->on('bibliotecarios');
            $table->index('exemplar_id', 'ix_emprestimos_exemplar');
            $table->index('leitor_id', 'ix_emprestimos_leitor');
            $table->index('bibliotecario_retirada_id', 'ix_emprestimos_bibliotecario_retirada');
            $table->index('bibliotecario_devolucao_id', 'ix_emprestimos_bibliotecario_devolucao');
        });

        Schema::create('renovacoes', function (Blueprint $table) {
            $table->integer('emprestimo_id');
            $table->smallInteger('numero');
            $table->date('data_renovacao')->default(DB::raw('CURRENT_DATE'));
            $table->timestampTz('criado_em')->nullable();
            $table->timestampTz('atualizado_em')->nullable();
            $table->primary(['emprestimo_id', 'numero']);
            $table->foreign('emprestimo_id')->references('id')->on('emprestimos')->cascadeOnDelete();
        });

        Schema::create('multas', function (Blueprint $table) {
            $table->integer('emprestimo_id')->primary();
            $table->decimal('valor_por_dia', 6, 2)->default('0.50');
            $table->date('data_pagamento')->nullable();
            $table->integer('bibliotecario_pagamento_id')->nullable();
            $table->timestampTz('criado_em')->nullable();
            $table->timestampTz('atualizado_em')->nullable();
            $table->foreign('emprestimo_id')->references('id')->on('emprestimos')->cascadeOnDelete();
            $table->foreign('bibliotecario_pagamento_id')->references('usuario_id')->on('bibliotecarios');
            $table->index('bibliotecario_pagamento_id', 'ix_multas_bibliotecario_pagamento');
        });

        Schema::create('reservas', function (Blueprint $table) {
            $table->integer('id')->generatedAs()->always()->primary();
            $table->integer('exemplar_id');
            $table->integer('leitor_id');
            $table->timestampTz('data_hora_reserva', 6)->useCurrent();
            $table->string('situacao', 20)->default('NA_FILA');
            $table->timestampTz('criado_em')->nullable();
            $table->timestampTz('atualizado_em')->nullable();
            $table->unique(['exemplar_id', 'data_hora_reserva']);
            $table->foreign('exemplar_id')->references('id')->on('exemplares');
            $table->foreign('leitor_id')->references('usuario_id')->on('leitores');
            $table->index('leitor_id', 'ix_reservas_leitor');
        });

        Schema::create('emprestimos_reservas', function (Blueprint $table) {
            $table->integer('emprestimo_id')->primary();
            $table->integer('reserva_id')->unique();
            $table->foreign('emprestimo_id')->references('id')->on('emprestimos')->cascadeOnDelete();
            $table->foreign('reserva_id')->references('id')->on('reservas')->cascadeOnDelete();
        });

        DB::statement("ALTER TABLE emprestimos ADD CONSTRAINT ck_emprestimos_situacao CHECK (situacao IN ('AGUARDANDO_RETIRADA', 'ATIVO', 'ENCERRADO'))");
        DB::statement("ALTER TABLE emprestimos ADD CONSTRAINT ck_emprestimos_datas_por_situacao CHECK (
            (situacao = 'AGUARDANDO_RETIRADA'
                AND data_limite_retirada IS NOT NULL
                AND data_retirada IS NULL
                AND data_prevista_devolucao IS NULL
                AND data_devolucao IS NULL)
            OR (situacao = 'ATIVO'
                AND data_retirada IS NOT NULL
                AND data_prevista_devolucao IS NOT NULL
                AND data_devolucao IS NULL)
            OR (situacao = 'ENCERRADO'
                AND data_limite_retirada IS NOT NULL
                AND data_retirada IS NULL
                AND data_prevista_devolucao IS NULL
                AND data_devolucao IS NULL)
            OR (situacao = 'ENCERRADO'
                AND data_retirada IS NOT NULL
                AND data_prevista_devolucao IS NOT NULL
                AND data_devolucao IS NOT NULL))");
        DB::statement('ALTER TABLE emprestimos ADD CONSTRAINT ck_emprestimos_ordem_datas CHECK (
            data_prevista_devolucao >= data_retirada AND data_devolucao >= data_retirada)');
        DB::statement('ALTER TABLE emprestimos ADD CONSTRAINT ck_emprestimos_bibliotecarios CHECK (
            (bibliotecario_retirada_id IS NULL) = (data_retirada IS NULL)
            AND (bibliotecario_devolucao_id IS NULL) = (data_devolucao IS NULL))');
        // R6: no máximo um empréstimo em curso por exemplar
        DB::statement("CREATE UNIQUE INDEX ux_emprestimos_exemplar_em_curso ON emprestimos (exemplar_id)
            WHERE situacao IN ('AGUARDANDO_RETIRADA', 'ATIVO')");

        DB::statement('ALTER TABLE renovacoes ADD CONSTRAINT ck_renovacoes_numero CHECK (numero BETWEEN 1 AND 3)');

        DB::statement('ALTER TABLE multas ADD CONSTRAINT ck_multas_valor_por_dia CHECK (valor_por_dia > 0)');
        DB::statement('ALTER TABLE multas ADD CONSTRAINT ck_multas_pagamento CHECK (
            (bibliotecario_pagamento_id IS NULL) = (data_pagamento IS NULL))');

        DB::statement("ALTER TABLE reservas ADD CONSTRAINT ck_reservas_situacao CHECK (situacao IN ('NA_FILA', 'ATENDIDA', 'CANCELADA'))");
        // R10: no máximo uma reserva na fila por leitor e exemplar
        DB::statement("CREATE UNIQUE INDEX ux_reservas_leitor_exemplar_na_fila ON reservas (leitor_id, exemplar_id)
            WHERE situacao = 'NA_FILA'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emprestimos_reservas');
        Schema::dropIfExists('reservas');
        Schema::dropIfExists('multas');
        Schema::dropIfExists('renovacoes');
        Schema::dropIfExists('emprestimos');
    }
};
