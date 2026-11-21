<?php

namespace App\Http\Requests\Bibliotecario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Cadastros que têm só o nome: autores, categorias e editoras.
 */
class SalvarNomeRequest extends FormRequest
{
    /**
     * Tabelas em que o nome é único.
     *
     * @var array<string, int>
     */
    private const LIMITES = ['autores' => 200, 'categorias' => 100, 'editoras' => 200];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $registro = collect($this->route()->parameters())->first(fn ($valor) => $valor instanceof Model);
        $tabela = $this->route()->getName() !== null ? explode('.', $this->route()->getName())[1] : 'autores';

        $regras = ['required', 'string', 'max:'.self::LIMITES[$tabela]];

        if ($tabela !== 'autores') {
            $regras[] = Rule::unique($tabela, 'nome')->ignore($registro?->getKey());
        }

        return ['nome' => $regras];
    }
}
