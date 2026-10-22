<?php

namespace App\Services;

use App\Models\Agendamento;
use App\Models\Servico;
use Carbon\Carbon;

/**
 * Regras de horário da barbearia: expediente, grade de horários e conflitos.
 */
class Disponibilidade
{
    public static function hora(int $minutos): string
    {
        return sprintf('%02d:%02d', intdiv($minutos, 60), $minutos % 60);
    }

    public function expediente(): string
    {
        return self::hora(config('barbearia.abertura')) . ' às ' . self::hora(config('barbearia.fechamento'));
    }

    /** Converte "AAAA-MM-DD" em Carbon; data inválida ou passada vira hoje. */
    public function dataOuHoje(?string $texto, bool $permitirPassado = false): Carbon
    {
        $hoje = Carbon::today();

        if (! $texto || ! Carbon::hasFormat($texto, 'Y-m-d')) {
            return $hoje;
        }

        $data = Carbon::createFromFormat('Y-m-d', $texto)->startOfDay();

        return (! $permitirPassado && $data->lt($hoje)) ? $hoje : $data;
    }

    /** O serviço cabe no expediente e não começa no passado? */
    public function dentroDoExpediente(Carbon $data, int $inicio, int $fim): bool
    {
        $agora = $data->isToday() ? now()->hour * 60 + now()->minute : -1;

        return $inicio >= config('barbearia.abertura')
            && $fim <= config('barbearia.fechamento')
            && $inicio > $agora;
    }

    /** Há agendamento cruzando o intervalo [inicio, fim) nesse dia? */
    public function temConflito(Carbon $data, int $inicio, int $fim, bool $travar = false): bool
    {
        $consulta = Agendamento::whereDate('data', $data)
            ->where('inicio', '<', $fim)
            ->where('fim', '>', $inicio);

        if ($travar) {
            $consulta->lockForUpdate();
        }

        return $consulta->exists();
    }

    /** Horários de início livres (em minutos) para o serviço no dia. */
    public function horariosLivres(Servico $servico, Carbon $data): array
    {
        $ocupados = Agendamento::whereDate('data', $data)->get(['inicio', 'fim']);
        $agora    = $data->isToday() ? now()->hour * 60 + now()->minute : -1;
        $livres   = [];

        for ($ini = config('barbearia.abertura');
             $ini + $servico->duracao <= config('barbearia.fechamento');
             $ini += config('barbearia.passo')) {

            $fim = $ini + $servico->duracao;

            if ($ini <= $agora) {
                continue;
            }
            if ($ocupados->contains(fn ($o) => $o->inicio < $fim && $o->fim > $ini)) {
                continue;
            }
            $livres[] = $ini;
        }

        return $livres;
    }
}
