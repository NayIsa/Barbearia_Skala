@extends('layouts.app')
@section('titulo', 'Agendar ' . $servico->nome)

@section('conteudo')
<h1>Agendar {{ $servico->nome }}</h1>
<p class="sub">Atendimento das {{ $expediente }}.</p>

<section class="resumo">
    <h2>Seu serviço</h2>
    <dl>
        <dt>Serviço</dt><dd>{{ $servico->nome }}</dd>
        <dt>Duração</dt><dd>{{ $servico->duracao }} min</dd>
        <dt>Preço</dt><dd>{{ $servico->preco_formatado }}</dd>
        <dt>Data</dt><dd>{{ $data->format('d/m/Y') }}</dd>
    </dl>
</section>

<section>
    <h2>Data</h2>
    <form method="get" action="{{ route('agendamentos.create', $servico) }}">
        <label for="data">Trocar a data</label>
        <input id="data" type="date" name="data" value="{{ $data->toDateString() }}" min="{{ today()->toDateString() }}" required>
        <button type="submit" class="btn sec">Ver horários desta data</button>
    </form>
</section>

<section>
    <h2>Horário e seus dados</h2>

    @if (empty($livres))
        <p class="vazio">Não há horários livres em {{ $data->format('d/m/Y') }}. Escolha outra data acima.</p>
    @else
    <form method="post" action="{{ route('agendamentos.store', $servico) }}">
        @csrf
        <input type="hidden" name="data" value="{{ $data->toDateString() }}">

        <fieldset style="border:0;padding:0;margin:0">
            <legend style="font-weight:600">Horários livres em {{ $data->format('d/m/Y') }}</legend>
            <div class="horarios">
                @foreach ($livres as $m)
                    <label style="margin:0;font-weight:400">
                        <input type="radio" name="horario" value="{{ $m }}"
                               @checked((int) old('horario', $horario) === $m) @required($loop->first)>
                        <span>{{ \App\Services\Disponibilidade::hora($m) }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <label for="cliente">Nome</label>
        <input id="cliente" name="cliente" maxlength="80" required autocomplete="name" value="{{ old('cliente') }}">

        <label for="telefone">Telefone com DDD</label>
        <input id="telefone" name="telefone" inputmode="tel" required autocomplete="tel" placeholder="(51) 99999-9999" value="{{ old('telefone') }}">

        <button type="submit">Confirmar agendamento</button>
        <a class="btn sec" href="{{ route('home') }}">Voltar</a>
    </form>
    @endif
</section>
@endsection
