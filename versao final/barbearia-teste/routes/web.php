<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AgendamentoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServicoController;
use Illuminate\Support\Facades\Route;

// Área do cliente
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/agendar/{servico}', [AgendamentoController::class, 'create'])->name('agendamentos.create');
Route::post('/agendar/{servico}', [AgendamentoController::class, 'store'])->name('agendamentos.store');

// Área dos funcionários (futuro projeto: proteger com autenticação)
Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda.index');
Route::delete('/agenda/{agendamento}', [AgendaController::class, 'destroy'])->name('agenda.destroy');

Route::get('/servicos', [ServicoController::class, 'index'])->name('servicos.index');
Route::post('/servicos', [ServicoController::class, 'store'])->name('servicos.store');
Route::delete('/servicos/{servico}', [ServicoController::class, 'destroy'])->name('servicos.destroy');
