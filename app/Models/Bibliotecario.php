<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Especialização de Usuário: registra os atendimentos (R16).
 */
class Bibliotecario extends Model
{
    const CREATED_AT = 'criado_em';

    const UPDATED_AT = 'atualizado_em';

    protected $table = 'bibliotecarios';

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
}
