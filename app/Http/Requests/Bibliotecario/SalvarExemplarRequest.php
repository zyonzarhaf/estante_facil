<?php

namespace App\Http\Requests\Bibliotecario;

use App\Enums\EstadoConservacao;
use App\Models\Exemplar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalvarExemplarRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $exemplar = $this->route('exemplar');

        return [
            'codigo_tombo' => ['required', 'string', 'max:30',
                Rule::unique('exemplares', 'codigo_tombo')->ignore($exemplar instanceof Exemplar ? $exemplar->id : null)],
            'edicao_id' => ['required', 'integer', 'exists:edicoes,id'],
            'data_aquisicao' => ['required', 'date', 'before_or_equal:today'],
            'estado_conservacao' => ['required', Rule::enum(EstadoConservacao::class)],
        ];
    }
}
