<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServicoRequest;
use App\Models\Servico;

class ServicoController extends Controller
{
    public function index()
    {
        return view('servicos.index', ['servicos' => Servico::orderBy('nome')->get()]);
    }

    public function store(StoreServicoRequest $request)
    {
        Servico::create($request->validated());

        return redirect()->route('servicos.index')->with('sucesso', 'Serviço cadastrado.');
    }

    public function destroy(Servico $servico)
    {
        if ($servico->agendamentos()->exists()) {
            return back()->withErrors(['servico' => 'Este serviço tem agendamentos e não pode ser excluído.']);
        }

        $servico->delete();

        return redirect()->route('servicos.index')->with('sucesso', 'Serviço excluído.');
    }
}
