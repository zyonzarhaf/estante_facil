<?php

namespace App\Models;

use App\Enums\MotivoRecusa;
use App\Enums\SituacaoReserva;
use App\Exceptions\OperacaoRecusada;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use LogicException;

/**
 * Raiz do agregado Reserva: pedido de um Leitor por um Exemplar específico.
 * Transições válidas: NA_FILA → ATENDIDA | CANCELADA.
 */
class Reserva extends Model
{
    const CREATED_AT = 'criado_em';

    const UPDATED_AT = 'atualizado_em';

    protected $table = 'reservas';

    protected $fillable = ['exemplar_id', 'leitor_id'];

    /**
     * Microssegundos em data_hora_reserva: a posição na fila é a ordem de chegada.
     */
    protected $dateFormat = 'Y-m-d H:i:s.uP';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'situacao' => SituacaoReserva::class,
            'data_hora_reserva' => 'datetime',
        ];
    }

    /**
     * Toda reserva nasce NA_FILA.
     */
    public static function naFilaDe(Leitor $leitor, Exemplar $exemplar): self
    {
        $reserva = new self(['exemplar_id' => $exemplar->id, 'leitor_id' => $leitor->usuario_id]);
        $reserva->forceFill([
            'situacao' => SituacaoReserva::NaFila,
            'data_hora_reserva' => now(),
        ])->save();

        return $reserva;
    }

    /**
     * @param  Builder<Reserva>  $consulta
     */
    public function scopeNaFila(Builder $consulta): void
    {
        $consulta->where('situacao', SituacaoReserva::NaFila);
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
     * Empréstimo originado por esta reserva (0..1).
     *
     * @return BelongsToMany<Emprestimo, $this>
     */
    public function emprestimos(): BelongsToMany
    {
        return $this->belongsToMany(Emprestimo::class, 'emprestimos_reservas');
    }

    public function estaNaFila(): bool
    {
        return $this->situacao === SituacaoReserva::NaFila;
    }

    /**
     * Posição na fila do exemplar, pela ordem de chegada.
     */
    public function posicaoNaFila(): ?int
    {
        if (! $this->estaNaFila()) {
            return null;
        }

        return self::query()
            ->naFila()
            ->where('exemplar_id', $this->exemplar_id)
            ->where('data_hora_reserva', '<=', $this->data_hora_reserva->format($this->getDateFormat()))
            ->count();
    }

    public function atender(): void
    {
        if (! $this->estaNaFila()) {
            throw new LogicException('Só uma reserva NA_FILA pode ser atendida.');
        }

        $this->forceFill(['situacao' => SituacaoReserva::Atendida])->save();
    }

    public function cancelar(): void
    {
        if (! $this->estaNaFila()) {
            throw new OperacaoRecusada(MotivoRecusa::ReservaNaoCancelavel);
        }

        $this->forceFill(['situacao' => SituacaoReserva::Cancelada])->save();
    }
}
