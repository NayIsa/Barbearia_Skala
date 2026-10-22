@extends('layouts.app')
@section('titulo', 'Cadastro de serviços')

@section('conteudo')
<h1>Cadastro de serviços</h1>
<div class="aviso info" role="note">Isto será visto apenas pelos funcionários em um futuro projeto</div>

<section>
    <h2>Cadastrar serviço</h2>
    <form method="post" action="{{ route('servicos.store') }}">
        @csrf
        <label for="nome">Nome</label>
        <input id="nome" name="nome" maxlength="60" required placeholder="Ex.: Corte" value="{{ old('nome') }}">
        <div class="linha">
            <div>
                <label for="duracao">Duração (minutos)</label>
                <input id="duracao" name="duracao" type="number" min="5" max="540" step="5" required placeholder="40" value="{{ old('duracao') }}">
            </div>
            <div>
                <label for="preco">Preço (R$)</label>
                <input id="preco" name="preco" inputmode="decimal" required placeholder="50,00" value="{{ old('preco') }}">
            </div>
        </div>
        <button type="submit">Cadastrar serviço</button>
    </form>
</section>

<section>
    <h2>Serviços cadastrados</h2>
    @if ($servicos->isEmpty())
        <p class="vazio">Nenhum serviço cadastrado. Use o formulário acima para criar o primeiro.</p>
    @else
        <div class="tabela-wrap"><table>
            <thead><tr><th>Serviço</th><th>Duração</th><th>Preço</th><th></th></tr></thead>
            <tbody>
            @foreach ($servicos as $s)
                <tr>
                    <td>{{ $s->nome }}</td>
                    <td>{{ $s->duracao }} min</td>
                    <td>{{ $s->preco_formatado }}</td>
                    <td>
                        <form method="post" action="{{ route('servicos.destroy', $s) }}" onsubmit="return confirm('Excluir este serviço?')">
                            @csrf
                            @method('DELETE')
                            <button class="perigo" type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
    @endif
</section>
@endsection
