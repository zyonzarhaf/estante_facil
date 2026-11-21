<?php

namespace App\Http\Requests\Bibliotecario;

use App\Models\Edicao;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalvarEdicaoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['isbn' => preg_replace('/\D/', '', (string) $this->input('isbn'))]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $edicao = $this->route('edicao');
        $id = $edicao instanceof Edicao ? $edicao->id : null;

        return [
            'isbn' => ['required', 'digits:13', Rule::unique('edicoes', 'isbn')->ignore($id)],
            'obra_id' => ['required', 'integer', 'exists:obras,id'],
            'editora_id' => ['required', 'integer', 'exists:editoras,id'],
            'numero_edicao' => ['required', 'integer', 'min:1',
                Rule::unique('edicoes')->ignore($id)->where(fn ($consulta) => $consulta
                    ->where('obra_id', $this->input('obra_id'))
                    ->where('editora_id', $this->input('editora_id'))),
            ],
            'ano_publicacao' => ['nullable', 'integer', 'min:1', 'max:'.now()->year],
            'numero_paginas' => ['nullable', 'integer', 'min:1'],
            'idioma' => ['nullable', 'string', 'max:50'],
        ];
    }
}
