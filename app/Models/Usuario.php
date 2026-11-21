<?php

namespace App\Models;

use App\Enums\Perfil;
use App\Enums\SituacaoUsuario;
use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Raiz do agregado Usuário: dados pessoais e de acesso. Especializado em
 * Leitor ou Bibliotecário, nunca ambos (R17).
 */
class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory;

    const CREATED_AT = 'criado_em';

    const UPDATED_AT = 'atualizado_em';

    protected $table = 'usuarios';

    protected $authPasswordName = 'senha';

    protected $rememberTokenName = 'token_lembrar';

    protected $fillable = ['nome', 'cpf', 'email', 'telefone', 'endereco_id', 'senha', 'perfil', 'situacao'];

    protected $hidden = ['senha', 'token_lembrar'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'senha' => 'hashed',
            'perfil' => Perfil::class,
            'situacao' => SituacaoUsuario::class,
            'data_cadastro' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Endereco, $this>
     */
    public function endereco(): BelongsTo
    {
        return $this->belongsTo(Endereco::class);
    }

    /**
     * @return HasOne<Leitor, $this>
     */
    public function leitor(): HasOne
    {
        return $this->hasOne(Leitor::class, 'usuario_id');
    }

    /**
     * @return HasOne<Bibliotecario, $this>
     */
    public function bibliotecario(): HasOne
    {
        return $this->hasOne(Bibliotecario::class, 'usuario_id');
    }

    public function ehLeitor(): bool
    {
        return $this->perfil === Perfil::Leitor;
    }

    public function ehBibliotecario(): bool
    {
        return $this->perfil === Perfil::Bibliotecario;
    }

    public function estaAtivo(): bool
    {
        return $this->situacao === SituacaoUsuario::Ativo;
    }
}
