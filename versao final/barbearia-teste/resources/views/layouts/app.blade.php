<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo') – Barbearia SC</title>
    <link rel="stylesheet" href="{{ asset('css/barbearia.css') }}">
</head>
<body>
<div class="poste"></div>
<header><div class="in">
    <strong>Barbearia SC</strong>
    <nav aria-label="Principal">
        <a href="{{ route('home') }}">Início</a>
        <a href="{{ route('agenda.index') }}">Agenda do dia</a>
        <a href="{{ route('servicos.index') }}">Cadastro de serviços</a>
    </nav>
</div></header>
<main>
    @if (session('sucesso'))
        <div class="aviso ok" role="status">{{ session('sucesso') }}</div>
    @endif

    @if ($errors->any())
        <div class="aviso erro" role="alert">
            <ul style="margin:0;padding-left:18px">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('conteudo')
</main>
</body>
</html>
