<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use App\Services\Disponibilidade;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    private const MAX_HORARIOS = 12;

    public function index(Request $request, Disponibilidade $disp)
    {
        $data = $disp->dataOuHoje($request->query('data'));

        $servicos = Servico::orderBy('nome')->get()->map(function (Servico $s) use ($disp, $data) {
            $s->livres = $disp->horariosLivres($s, $data);
            return $s;
        });

        return view('home', [
            'servicos'    => $servicos,
            'data'        => $data,
            'expediente'  => $disp->expediente(),
            'maxHorarios' => self::MAX_HORARIOS,
        ]);
    }
}
