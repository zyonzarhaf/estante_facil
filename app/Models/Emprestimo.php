<?php

namespace App\Models;

use App\Enums\MotivoRecusa;
use App\Enums\SituacaoEmprestimo;
use App\Exceptions\OperacaoRecusada;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Raiz do agregado Empréstimo. Renovação e Multa são membros internos: só
 * são criados e alterados por métodos desta classe.
 */
class Emprestimo extends Model
{
    const CREATED_AT = 'criado_em';

    const UPDATED_AT = 'atualizado_em';

    protected $table = 'emprestimos';

    protected $fillable = [
        'exemplar_id', 'leitor_id', 'situacao', 'data_limite_retirada', 'data_retirada',
        'data_prevista_devolucao', 'bibliotecario_retirada_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'situacao' => SituacaoEmprestimo::class,
            'data_limite_retirada' => 'date',
            'data_retirada' => 'date',
            'data_prevista_devolucao' => 'date',
            'data_devolucao' => 'date',
        ];
    }

    /**
     * R9, R11: empréstimo criado quando uma reserva é atendida.
     */
    public static function aguardandoRetirada(Leitor $leitor, Exemplar $exemplar): self
    {
        return self::create([
            'exemplar_id' => $exemplar->id,
            'leitor_id' => $leitor->usuario_id,
            'situacao' => SituacaoEmprestimo::AguardandoRetirada,
            'data_limite_retirada' => today()->addDays(config('biblioteca.prazo_retirada_dias')),
        ]);
    }

    /**
     * R1, R7, R16: empréstimo direto no balcão, já ATIVO.
     */
    public static function noBalcao(Leitor $leitor, Exemplar $exemplar, Bibliotecario $bibliotecario): self
    {
        return self::create([
            'exemplar_id' => $exemplar->id,
            'leitor_id' => $leitor->usuario_id,
            'situacao' => SituacaoEmprestimo::Ativo,
            'data_retirada' => today(),
            'data_prevista_devolucao' => today()->addDays(config('biblioteca.prazo_emprestimo_dias')),
            'bibliotecario_retirada_id' => $bibliotecario->usuario_id,
        ]);
    }

    /**
     * @param  Builder<Emprestimo>  $consulta
     */
    public function scopeAtivos(Builder $consulta): void
    {
        $consulta->where('situacao', SituacaoEmprestimo::Ativo);
    }

    /**
     * @param  Builder<Emprestimo>  $consulta
     */
    public function scopeEmCurso(Builder $consulta): void
    {
        $consulta->whereIn('situacao', [SituacaoEmprestimo::AguardandoRetirada, SituacaoEmprestimo::Ativo]);
    }

    /**
     * @param  Builder<Emprestimo>  $consulta
     */
    public function scopeAtrasados(Builder $consulta): void
    {
        $consulta->where('situacao', SituacaoEmprestimo::Ativo)
            ->whereDate('data_prevista_devolucao', '<', today());
    }

    /**
     * @return BelongsTo<Exemplar, $this>
     */
    public function exemplar(): BelongsTo
    {
        return $this->belongsTo(Exemplar::class);
    }

    /**
     * @return BelongsTo<Leitor, $this>
     */
    public function leitor(): BelongsTo
    {
        return $this->belongsTo(Leitor::class, 'leitor_id', 'usuario_id');
    }

    /**
     * @return BelongsTo<Bibliotecario, $this>
     */
    public function bibliotecarioRetirada(): BelongsTo
    {
        return $this->belongsTo(Bibliotecario::class, 'bibliotecario_retirada_id', 'usuario_id');
    }

    /**
     * @return BelongsTo<Bibliotecario, $this>
     */
    public function bibliotecarioDevolucao(): BelongsTo
    {
        return $this->belongsTo(Bibliotecario::class, 'bibliotecario_devolucao_id', 'usuario_id');
    }

    /**
     * @return HasMany<Renovacao, $this>
     */
    public function renovacoes(): HasMany
    {
        return $this->hasMany(Renovacao::class);
    }

    /**
     * @return HasOne<Multa, $this>
     */
    public function multa(): HasOne
    {
        return $this->hasOne(Multa::class);
    }

    /**
     * Reserva que originou este empréstimo (0..1).
     *
     * @return BelongsToMany<Reserva, $this>
     */
    public function reservas(): BelongsToMany
    {
        return $this->belongsToMany(Reserva::class, 'emprestimos_reservas');
    }

    public function reserva(): ?Reserva
    {
        return $this->reservas()->first();
    }

    public function vincular(Reserva $reserva): void
    {
        $this->reservas()->attach($reserva->id);
    }

    public function estaAtivo(): bool
    {
        return $this->situacao === SituacaoEmprestimo::Ativo;
    }

    public function estaAguardandoRetirada(): bool
    {
        return $this->situacao === SituacaoEmprestimo::AguardandoRetirada;
    }

    public function estaAtrasado(): bool
    {
        return $this->estaAtivo() && $this->data_prevista_devolucao->lt(today());
    }

    /**
     * Dias de atraso até hoje (ativo) ou até a devolução (encerrado).
     */
    public function diasDeAtraso(): int
    {
        if ($this->data_prevista_devolucao === null) {
            return 0;
        }

        $referencia = $this->data_devolucao ?? today();

        return max(0, (int) $this->data_prevista_devolucao->diffInDays($referencia, false));
    }

    /**
     * R7: o prazo de retirada de um empréstimo AGUARDANDO_RETIRADA venceu.
     */
    public function prazoRetiradaVencido(): bool
    {
        return $this->estaAguardandoRetirada() && $this->data_limite_retirada->lt(today());
    }

    public function quantidadeRenovacoes(): int
    {
        return $this->renovacoes()->count();
    }

    /**
     * Só o próprio leitor renova, e só um empréstimo ATIVO.
     */
    public function podeSerRenovadoPor(Leitor $leitor): bool
    {
        return $this->estaAtivo() && $this->leitor_id === $leitor->usuario_id;
    }

    /**
     * R1, R16: AGUARDANDO_RETIRADA → ATIVO.
     */
    public function registrarRetirada(Bibliotecario $bibliotecario): void
    {
        $this->exigirSituacao(SituacaoEmprestimo::AguardandoRetirada);

        $this->forceFill([
            'situacao' => SituacaoEmprestimo::Ativo,
            'data_retirada' => today(),
            'data_prevista_devolucao' => today()->addDays(config('biblioteca.prazo_emprestimo_dias')),
            'bibliotecario_retirada_id' => $bibliotecario->usuario_id,
        ])->save();
    }

    /**
     * R2: cria a renovação e estende o prazo a partir de hoje.
     */
    public function renovar(): void
    {
        $this->exigirSituacao(SituacaoEmprestimo::Ativo);

        $quantidade = $this->quantidadeRenovacoes();

        if ($quantidade >= config('biblioteca.max_renovacoes')) {
            throw new OperacaoRecusada(MotivoRecusa::LimiteRenovacoes);
        }

        $this->renovacoes()->create([
            'numero' => $quantidade + 1,
            'data_renovacao' => today(),
        ]);

        $this->definirDataPrevista(today()->addDays(config('biblioteca.prazo_emprestimo_dias')));
    }

    /**
     * R4, R16: ATIVO → ENCERRADO; gera multa se houver atraso.
     */
    public function registrarDevolucao(Bibliotecario $bibliotecario): void
    {
        $this->exigirSituacao(SituacaoEmprestimo::Ativo);

        $this->forceFill([
            'situacao' => SituacaoEmprestimo::Encerrado,
            'data_devolucao' => today(),
            'bibliotecario_devolucao_id' => $bibliotecario->usuario_id,
        ])->save();

        if ($this->diasDeAtraso() > 0) {
            $this->multa()->create(['valor_por_dia' => config('biblioteca.multa_por_dia')]);
        }
    }

    /**
     * R12, R14: AGUARDANDO_RETIRADA → ENCERRADO, sem data de retirada.
     */
    public function encerrarSemRetirada(): void
    {
        $this->exigirSituacao(SituacaoEmprestimo::AguardandoRetirada);

        $this->forceFill(['situacao' => SituacaoEmprestimo::Encerrado])->save();
    }

    /**
     * R16: quitação da multa pela raiz do agregado.
     */
    public function quitarMulta(Bibliotecario $bibliotecario): void
    {
        $multa = $this->multa;

        if ($multa === null || ! $multa->estaPendente()) {
            throw new OperacaoRecusada(MotivoRecusa::SemMultaPendente);
        }

        $multa->forceFill([
            'data_pagamento' => today(),
            'bibliotecario_pagamento_id' => $bibliotecario->usuario_id,
        ])->save();
    }

    protected function definirDataPrevista(\DateTimeInterface $data): void
    {
        $this->forceFill(['data_prevista_devolucao' => $data])->save();
    }

    /**
     * Transições válidas: AGUARDANDO_RETIRADA → ATIVO | ENCERRADO; ATIVO → ENCERRADO.
     */
    protected function exigirSituacao(SituacaoEmprestimo $situacao): void
    {
        if ($this->situacao !== $situacao) {
            throw new OperacaoRecusada(MotivoRecusa::EmprestimoInvalido);
        }
    }
}
