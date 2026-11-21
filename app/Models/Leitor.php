<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Especialização de Usuário: toma exemplares emprestados e faz reservas.
 */
class Leitor extends Model
{
    const CREATED_AT = 'criado_em';

    const UPDATED_AT = 'atualizado_em';

    protected $table = 'leitores';

    protected $primaryKey = 'usuario_id';

    public $incrementing = false;

    protected $fillable = ['usuario_id'];

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * @return HasMany<Emprestimo, $this>
     */
    public function emprestimos(): HasMany
    {
        return $this->hasMany(Emprestimo::class, 'leitor_id');
    }

    /**
     * @return HasMany<Reserva, $this>
     */
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'leitor_id');
    }

    /**
     * R8: multa pendente, empréstimo atrasado ou usuário inativo.
     */
    public function estaBloqueado(): bool
    {
        if (! $this->usuario->estaAtivo()) {
            return true;
        }

        $temMultaPendente = Multa::query()
            ->pendentes()
            ->whereHas('emprestimo', fn ($consulta) => $consulta->where('leitor_id', $this->usuario_id))
            ->exists();

        return $temMultaPendente || $this->emprestimos()->atrasados()->exists();
    }

    /**
     * R5, R15: limite de empréstimos ativos simultâneos.
     */
    public function atingiuLimite(): bool
    {
        return $this->emprestimos()->ativos()->count() >= config('biblioteca.limite_emprestimos_ativos');
    }
}
