<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgendamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['telefone' => preg_replace('/\D/', '', (string) $this->input('telefone'))]);
    }

    public function rules(): array
    {
        return [
            'data'     => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'horario'  => ['required', 'integer'],
            'cliente'  => ['required', 'string', 'max:80'],
            'telefone' => ['required', 'digits_between:10,11'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.after_or_equal'     => 'Escolha uma data a partir de hoje.',
            'horario.required'        => 'Escolha um horário.',
            'cliente.required'        => 'Informe seu nome.',
            'telefone.digits_between' => 'Informe o telefone com DDD (10 ou 11 dígitos).',
        ];
    }
}
