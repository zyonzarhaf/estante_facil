<?php

namespace App\Http\Requests\Bibliotecario;

use Illuminate\Foundation\Http\FormRequest;

/**
 * RF12: obra com pelo menos um autor.
 */
class SalvarObraRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'subtitulo' => ['nullable', 'string', 'max:255'],
            'idioma_original' => ['nullable', 'string', 'max:50'],
            'url_acesso' => ['nullable', 'url', 'max:500'],
            'autores' => ['required', 'array', 'min:1'],
            'autores.*' => ['integer', 'exists:autores,id'],
            'categorias' => ['nullable', 'array'],
            'categorias.*' => ['integer', 'exists:categorias,id'],
        ];
    }
}
