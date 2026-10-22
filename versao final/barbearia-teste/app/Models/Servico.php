<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servico extends Model
{
    protected $fillable = ['nome', 'duracao', 'preco'];

    protected $casts = [
        'duracao' => 'integer',
        'preco'   => 'decimal:2',
    ];

    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class);
    }

    protected function precoFormatado(): Attribute
    {
        return Attribute::get(fn () => 'R$ ' . number_format((float) $this->preco, 2, ',', '.'));
    }
}
