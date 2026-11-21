<?php

namespace App\Models;

use App\Enums\SituacaoMulta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Membro interno do agregado Empréstimo: criado por Emprestimo::registrarDevolucao()
 * e quitado por Emprestimo::quitarMulta(). Dias de atraso, valor total e
 * situação são derivados.
 */
class Multa extends Model
{
    const CREATED_AT = 'criado_em';

    const UPDATED_AT = 'atualizado_em';

    protected $table = 'multas';

    protected $primaryKey = 'emprestimo_id';

    public $incrementing = false;

    protected $fillable = ['valor_por_dia'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valor_por_dia' => 'decimal:2',
            'data_pagamento' => 'date',
        ];
    }

    /**
     * @param  Builder<Multa>  $consulta
     */
    public function scopePendentes(Builder $consulta): void
    {
        $consulta->whereNull('data_pagamento');
    }

    /**
     * @return BelongsTo<Emprestimo, $this>
     */
    public function emprestimo(): BelongsTo
    {
        return $this->belongsTo(Emprestimo::class);
    }

    /**
     * @return BelongsTo<Bibliotecario, $this>
     */
    public function bibliotecarioPagamento(): BelongsTo
    {
        return $this->belongsTo(Bibliotecario::class, 'bibliotecario_pagamento_id', 'usuario_id');
    }

    public function estaPendente(): bool
    {
        return $this->data_pagamento === null;
    }

    /**
     * Chamado só pela raiz do agregado: Emprestimo::quitarMulta().
     */
    public function quitar(Bibliotecario $bibliotecario): void
    {
        $this->forceFill([
            'data_pagamento' => today(),
            'bibliotecario_pagamento_id' => $bibliotecario->usuario_id,
        ])->save();
    }

    public function situacao(): SituacaoMulta
    {
        return $this->estaPendente() ? SituacaoMulta::Pendente : SituacaoMulta::Paga;
    }

    public function diasDeAtraso(): int
    {
        return $this->emprestimo->diasDeAtraso();
    }

    /**
     * R4: valor por dia × dias de atraso.
     */
    public function valorTotal(): float
    {
        return round($this->diasDeAtraso() * (float) $this->valor_por_dia, 2);
    }
}
