<?php

namespace App\Models;

use Database\Factories\CategoriaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Categoria extends Model
{
    /** @use HasFactory<CategoriaFactory> */
    use HasFactory;

    const CREATED_AT = 'criado_em';

    const UPDATED_AT = 'atualizado_em';

    protected $table = 'categorias';

    protected $fillable = ['nome'];

    /**
     * @return BelongsToMany<Obra, $this>
     */
    public function obras(): BelongsToMany
    {
        return $this->belongsToMany(Obra::class, 'obras_categorias');
    }
}
