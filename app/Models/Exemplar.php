<?php

namespace App\Models;

use App\Enums\EstadoConservacao;
use App\Enums\SituacaoCirculacao;
use App\Enums\SituacaoEmprestimo;
use Database\Factories\ExemplarFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Raiz do agregado Exemplar: cópia física de uma Edição. A situação de
 * circulação não é armazenada; deriva do empréstimo em curso.
 */
class Exemplar extends Model
{
    /** @use HasFactory<ExemplarFactory> */
    use HasFactory;

    const CREATED_AT = 'criado_em';

    const UPDATED_AT = 'atualizado_em';

    protected $table = 'exemplares';

    protected $fillable = ['codigo_tombo', 'edicao_id', 'data_aquisicao', 'estado_conservacao'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estado_conservacao' => EstadoConservacao::class,
            'indisponivel' => 'boolean',
            'data_aquisicao' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Edicao, $this>
     */
    public function edicao(): BelongsTo
    {
        return $this->belongsTo(Edicao::class);
    }

    /**
     * @return HasMany<Emprestimo, $this>
     */
    public function emprestimos(): HasMany
    {
        return $this->hasMany(Emprestimo::class);
    }

    /**
     * R6: no máximo um empréstimo AGUARDANDO_RETIRADA ou ATIVO.
     *
     * @return HasOne<Emprestimo, $this>
     */
    public function emprestimoEmCurso(): HasOne
    {
        return $this->hasOne(Emprestimo::class)->emCurso();
    }

    /**
     * @return HasMany<Reserva, $this>
     */
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    /**
     * Reservas NA_FILA por ordem de chegada.
     *
     * @return HasMany<Reserva, $this>
     */
    public function filaDeReservas(): HasMany
    {
        return $this->reservas()->naFila()->orderBy('data_hora_reserva');
    }

    public function estaIndisponivel(): bool
    {
        return $this->indisponivel;
    }

    /**
     * Exemplar livre: não indisponível e sem empréstimo em curso.
     */
    public function estaLivre(): bool
    {
        return ! $this->indisponivel && ! $this->emprestimos()->emCurso()->exists();
    }

    /**
     * R10: o leitor já está com o exemplar, aguardando sua retirada ou na fila dele.
     */
    public function temReservaOuEmprestimoDe(Leitor $leitor): bool
    {
        return $this->reservas()->naFila()->where('leitor_id', $leitor->usuario_id)->exists()
            || $this->emprestimos()->emCurso()->where('leitor_id', $leitor->usuario_id)->exists();
    }

    public function situacaoCirculacao(): SituacaoCirculacao
    {
        if ($this->indisponivel) {
            return SituacaoCirculacao::Indisponivel;
        }

        return match ($this->emprestimoEmCurso?->situacao) {
            SituacaoEmprestimo::Ativo => SituacaoCirculacao::Emprestado,
            SituacaoEmprestimo::AguardandoRetirada => SituacaoCirculacao::AguardandoRetirada,
            default => SituacaoCirculacao::Disponivel,
        };
    }

    /**
     * R14: o exemplar sai de circulação.
     */
    public function marcarIndisponivel(): void
    {
        $this->forceFill(['indisponivel' => true])->save();
    }

    public function marcarDisponivel(): void
    {
        $this->forceFill(['indisponivel' => false])->save();
    }
}
