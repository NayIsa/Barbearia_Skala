<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Services\Disponibilidade;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function index(Request $request, Disponibilidade $disp)
    {
        $data = $disp->dataOuHoje($request->query('data'), permitirPassado: true);

        $agendamentos = Agendamento::with('servico')
            ->whereDate('data', $data)
            ->orderBy('inicio')
            ->get();

        return view('agenda.index', [
            'data'         => $data,
            'agendamentos' => $agendamentos,
            'total'        => $agendamentos->sum(fn ($a) => (float) $a->servico->preco),
        ]);
    }

    public function destroy(Agendamento $agendamento)
    {
        $data = $agendamento->data->toDateString();
        $agendamento->delete();

        return redirect()->route('agenda.index', ['data' => $data])
            ->with('sucesso', 'Agendamento cancelado.');
    }
}
