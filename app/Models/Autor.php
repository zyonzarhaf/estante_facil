<?php

namespace App\Models;

use Database\Factories\AutorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Autor extends Model
{
    /** @use HasFactory<AutorFactory> */
    use HasFactory;

    const CREATED_AT = 'criado_em';

    const UPDATED_AT = 'atualizado_em';

    protected $table = 'autores';

    protected $fillable = ['nome'];

    /**
     * @return BelongsToMany<Obra, $this>
     */
    public function obras(): BelongsToMany
    {
        return $this->belongsToMany(Obra::class, 'obras_autores');
    }
}
