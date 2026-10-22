@extends('layouts.app')
@section('titulo', 'Agenda do dia')

@section('conteudo')
<h1>Agenda do dia</h1>
<div class="aviso info" role="note">Isto será visto apenas pelos funcionários em um futuro projeto</div>
<p class="sub">{{ $data->format('d/m/Y') }}</p>

<section>
    <form method="get" action="{{ route('agenda.index') }}">
        <label for="data">Escolher dia</label>
        <input id="data" type="date" name="data" value="{{ $data->toDateString() }}" required>
        <button type="submit">Ver agenda</button>
    </form>
</section>

<section>
    <h2>Agendamentos</h2>

    @if ($agendamentos->isEmpty())
        <p class="vazio">Nenhum agendamento em {{ $data->format('d/m/Y') }}.</p>
    @else
        <div class="tabela-wrap"><table>
            <thead><tr><th>Horário</th><th>Cliente</th><th>Telefone</th><th>Serviço</th><th>Valor</th><th></th></tr></thead>
            <tbody>
            @foreach ($agendamentos as $a)
                <tr>
                    <td class="hr">{{ $a->hora_inicio }}–{{ $a->hora_fim }}</td>
                    <td>{{ $a->cliente }}</td>
                    <td>{{ $a->telefone_formatado }}</td>
                    <td>{{ $a->servico->nome }}</td>
                    <td>{{ $a->servico->preco_formatado }}</td>
                    <td>
                        <form method="post" action="{{ route('agenda.destroy', $a) }}" onsubmit="return confirm('Cancelar este agendamento?')">
                            @csrf
                            @method('DELETE')
                            <button class="perigo" type="submit">Cancelar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
        <p style="margin:14px 0 0">
            <strong>{{ $agendamentos->count() }}</strong> atendimento(s) · previsto
            <strong>R$ {{ number_format($total, 2, ',', '.') }}</strong>
        </p>
    @endif
</section>
@endsection
