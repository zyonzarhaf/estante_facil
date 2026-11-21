<?php

namespace App\Models;

use Database\Factories\EnderecoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Raiz do agregado Endereço; pode ser compartilhado por vários usuários.
 */
class Endereco extends Model
{
    /** @use HasFactory<EnderecoFactory> */
    use HasFactory;

    const CREATED_AT = 'criado_em';

    const UPDATED_AT = 'atualizado_em';

    protected $table = 'enderecos';

    protected $fillable = ['logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf', 'cep'];

    /**
     * @return HasMany<Usuario, $this>
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class);
    }
}
