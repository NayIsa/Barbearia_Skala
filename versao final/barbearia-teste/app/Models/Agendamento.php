<?php

namespace App\Models;

use App\Services\Disponibilidade;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agendamento extends Model
{
    protected $table = 'agendamentos';

    protected $fillable = ['servico_id', 'cliente', 'telefone', 'data', 'inicio', 'fim'];

    protected $casts = [
        'data'   => 'date',
        'inicio' => 'integer',
        'fim'    => 'integer',
    ];

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }

    protected function horaInicio(): Attribute
    {
        return Attribute::get(fn () => Disponibilidade::hora($this->inicio));
    }

    protected function horaFim(): Attribute
    {
        return Attribute::get(fn () => Disponibilidade::hora($this->fim));
    }

    protected function telefoneFormatado(): Attribute
    {
        return Attribute::get(function () {
            $t = $this->telefone;
            return strlen($t) === 11
                ? sprintf('(%s) %s-%s', substr($t, 0, 2), substr($t, 2, 5), substr($t, 7))
                : sprintf('(%s) %s-%s', substr($t, 0, 2), substr($t, 2, 4), substr($t, 6));
        });
    }
}
