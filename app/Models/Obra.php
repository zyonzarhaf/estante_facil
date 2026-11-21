<?php

namespace App\Models;

use Database\Factories\ObraFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Raiz do agregado Obra: a criação intelectual (título, autores, categorias).
 */
class Obra extends Model
{
    /** @use HasFactory<ObraFactory> */
    use HasFactory;

    const CREATED_AT = 'criado_em';

    const UPDATED_AT = 'atualizado_em';

    protected $table = 'obras';

    protected $fillable = ['titulo', 'subtitulo', 'idioma_original', 'url_acesso'];

    /**
     * @return BelongsToMany<Autor, $this>
     */
    public function autores(): BelongsToMany
    {
        return $this->belongsToMany(Autor::class, 'obras_autores');
    }

    /**
     * @return BelongsToMany<Categoria, $this>
     */
    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'obras_categorias');
    }

    /**
     * @return HasMany<Edicao, $this>
     */
    public function edicoes(): HasMany
    {
        return $this->hasMany(Edicao::class);
    }
}
