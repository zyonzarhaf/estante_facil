<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Membro interno do agregado Empréstimo: criado só por Emprestimo::renovar().
 * Chave primária composta (emprestimo_id, numero).
 */
class Renovacao extends Model
{
    const CREATED_AT = 'criado_em';

    const UPDATED_AT = 'atualizado_em';

    protected $table = 'renovacoes';

    protected $primaryKey = null;

    public $incrementing = false;

    protected $fillable = ['numero', 'data_renovacao'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_renovacao' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Emprestimo, $this>
     */
    public function emprestimo(): BelongsTo
    {
        return $this->belongsTo(Emprestimo::class);
    }
}
