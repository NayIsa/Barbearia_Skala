<?php

namespace Database\Seeders;

use App\Models\Servico;
use Illuminate\Database\Seeder;

class ServicoSeeder extends Seeder
{
    public function run(): void
    {
        Servico::firstOrCreate(['nome' => 'Corte'], ['duracao' => 40, 'preco' => 50.00]);
        Servico::firstOrCreate(['nome' => 'Barba'], ['duracao' => 20, 'preco' => 30.00]);
    }
}
