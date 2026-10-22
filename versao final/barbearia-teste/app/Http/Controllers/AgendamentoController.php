<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAgendamentoRequest;
use App\Models\Servico;
use App\Services\Disponibilidade;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgendamentoController extends Controller
{
    public function create(Request $request, Servico $servico, Disponibilidade $disp)
    {
        $data   = $disp->dataOuHoje($request->query('data'));
        $livres = $disp->horariosLivres($servico, $data);

        $horario = (int) $request->query('horario', -1);

        return view('agendamentos.create', [
            'servico'    => $servico,
            'data'       => $data,
            'livres'     => $livres,
            'horario'    => in_array($horario, $livres, true) ? $horario : null,
            'expediente' => $disp->expediente(),
        ]);
    }

    public function store(StoreAgendamentoRequest $request, Servico $servico, Disponibilidade $disp)
    {
        $data   = Carbon::createFromFormat('Y-m-d', $request->input('data'))->startOfDay();
        $inicio = (int) $request->input('horario');
        $fim    = $inicio + $servico->duracao;

        if (! $disp->dentroDoExpediente($data, $inicio, $fim)) {
            throw ValidationException::withMessages([
                'horario' => "Horário fora do atendimento ({$disp->expediente()}) ou já passou.",
            ]);
        }

        // Verificação e gravação na mesma transação, com bloqueio nas linhas do dia.
        DB::transaction(function () use ($disp, $servico, $request, $data, $inicio, $fim) {
            if ($disp->temConflito($data, $inicio, $fim, travar: true)) {
                throw ValidationException::withMessages([
                    'horario' => 'Esse horário acabou de ser ocupado. Escolha outro.',
                ]);
            }

            $servico->agendamentos()->create([
                'cliente'  => $request->input('cliente'),
                'telefone' => $request->input('telefone'),
                'data'     => $data,
                'inicio'   => $inicio,
                'fim'      => $fim,
            ]);
        });

        return redirect()->route('home')->with(
            'sucesso',
            sprintf(
                'Agendamento confirmado: %s em %s às %s (termina às %s).',
                $servico->nome,
                $data->format('d/m/Y'),
                Disponibilidade::hora($inicio),
                Disponibilidade::hora($fim),
            )
        );
    }
}
