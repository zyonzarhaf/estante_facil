<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Editora extends Model
{
    const CREATED_AT = 'criado_em';

    const UPDATED_AT = 'atualizado_em';

    protected $table = 'editoras';

    protected $fillable = ['nome'];

    /**
     * @return HasMany<Edicao, $this>
     */
    public function edicoes(): HasMany
    {
        return $this->hasMany(Edicao::class);
    }
}
