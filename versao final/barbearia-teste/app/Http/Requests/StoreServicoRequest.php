<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Futuro projeto: restringir aos funcionários.
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Aceita "1.250,50" ou "50,00".
        $preco = str_replace(',', '.', str_replace('.', '', (string) $this->input('preco')));
        $this->merge(['preco' => $preco]);
    }

    public function rules(): array
    {
        $max = config('barbearia.fechamento') - config('barbearia.abertura');

        return [
            'nome'    => ['required', 'string', 'max:60'],
            'duracao' => ['required', 'integer', 'min:5', "max:$max"],
            'preco'   => ['required', 'numeric', 'gt:0', 'max:99999'],
        ];
    }

    public function messages(): array
    {
        return [
            'duracao.min' => 'A duração deve ser de pelo menos 5 minutos.',
            'duracao.max' => 'A duração deve caber no horário de atendimento.',
            'preco.gt'    => 'Informe um preço maior que zero.',
        ];
    }

    public function attributes(): array
    {
        return ['nome' => 'nome', 'duracao' => 'duração', 'preco' => 'preço'];
    }
}
