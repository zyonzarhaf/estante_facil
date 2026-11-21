<?php

namespace App\Http\Requests\Bibliotecario;

use App\Models\Leitor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * RF14: cadastro e edição de leitor, com endereço.
 */
class SalvarLeitorRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'cpf' => preg_replace('/\D/', '', (string) $this->input('cpf')),
            'cep' => preg_replace('/\D/', '', (string) $this->input('cep')),
            'uf' => mb_strtoupper((string) $this->input('uf')),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $leitor = $this->route('leitor');
        $idDoUsuario = $leitor instanceof Leitor ? $leitor->usuario_id : null;

        return [
            'nome' => ['required', 'string', 'max:200'],
            'cpf' => ['required', 'digits:11', Rule::unique('usuarios', 'cpf')->ignore($idDoUsuario)],
            'email' => ['required', 'email', 'max:254', Rule::unique('usuarios', 'email')->ignore($idDoUsuario)],
            'telefone' => ['nullable', 'string', 'max:20'],
            'senha' => [$idDoUsuario ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'logradouro' => ['required', 'string', 'max:200'],
            'numero' => ['required', 'string', 'max:10'],
            'complemento' => ['nullable', 'string', 'max:100'],
            'bairro' => ['required', 'string', 'max:100'],
            'cidade' => ['required', 'string', 'max:100'],
            'uf' => ['required', Rule::in(['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA',
                'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'])],
            'cep' => ['required', 'digits:8'],
        ];
    }
}
