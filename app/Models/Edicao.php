<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Raiz do agregado Edição: publicação de uma Obra por uma Editora (ISBN).
 */
class Edicao extends Model
{
    const CREATED_AT = 'criado_em';

    const UPDATED_AT = 'atualizado_em';

    protected $table = 'edicoes';

    protected $fillable = ['isbn', 'obra_id', 'editora_id', 'numero_edicao', 'ano_publicacao', 'numero_paginas', 'idioma'];

    /**
     * @return BelongsTo<Obra, $this>
     */
    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    /**
     * @return BelongsTo<Editora, $this>
     */
    public function editora(): BelongsTo
    {
        return $this->belongsTo(Editora::class);
    }

    /**
     * @return HasMany<Exemplar, $this>
     */
    public function exemplares(): HasMany
    {
        return $this->hasMany(Exemplar::class);
    }
}
