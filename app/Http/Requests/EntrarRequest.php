<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EntrarRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'senha' => ['required', 'string'],
        ];
    }
}
