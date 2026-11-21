<?php

namespace App\Http\Requests\Bibliotecario;

use Illuminate\Foundation\Http\FormRequest;

/**
 * RF08: empréstimo direto, identificado pelo CPF do leitor e pelo tombo.
 */
class EmprestarExemplarRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['cpf' => preg_replace('/\D/', '', (string) $this->input('cpf'))]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'cpf' => ['required', 'digits:11', 'exists:usuarios,cpf'],
            'codigo_tombo' => ['required', 'string', 'exists:exemplares,codigo_tombo'],
        ];
    }
}
