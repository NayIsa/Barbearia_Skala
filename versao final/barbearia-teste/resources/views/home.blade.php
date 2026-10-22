@extends('layouts.app')
@section('titulo', 'Início')

@section('conteudo')
<h1>Escolha seu serviço</h1>
<p class="sub">Atendimento das {{ $expediente }}. Veja os horários livres e clique em Agendar.</p>

<section>
    <form method="get" action="{{ route('home') }}">
        <label for="data">Ver horários do dia</label>
        <input id="data" type="date" name="data" value="{{ $data->toDateString() }}" min="{{ today()->toDateString() }}" required>
        <button type="submit">Atualizar horários</button>
    </form>
</section>

@if ($servicos->isEmpty())
    <section><p class="vazio">Nenhum serviço disponível no momento.</p></section>
@else
<div class="cards">
    @foreach ($servicos as $s)
        @php $base = ['servico' => $s, 'data' => $data->toDateString()]; @endphp
        <article class="card">
            <div class="topo">
                <h2>{{ $s->nome }}</h2>
                <span class="preco">{{ $s->preco_formatado }}</span>
            </div>
            <p class="meta">Duração: {{ $s->duracao }} min</p>

            @if (empty($s->livres))
                <p class="vazio">Sem horários livres em {{ $data->format('d/m/Y') }}. Escolha outro dia acima.</p>
            @else
                <strong style="font-size:14px">Horários livres em {{ $data->format('d/m/Y') }}</strong>
                <div class="chips">
                    @foreach (array_slice($s->livres, 0, $maxHorarios) as $m)
                        <a href="{{ route('agendamentos.create', $base + ['horario' => $m]) }}">{{ \App\Services\Disponibilidade::hora($m) }}</a>
                    @endforeach
                    @if (count($s->livres) > $maxHorarios)
                        <a href="{{ route('agendamentos.create', $base) }}">+{{ count($s->livres) - $maxHorarios }} horários</a>
                    @endif
                </div>
                <a class="btn" href="{{ route('agendamentos.create', $base) }}">Agendar {{ $s->nome }}</a>
            @endif
        </article>
    @endforeach
</div>
@endif
@endsection
