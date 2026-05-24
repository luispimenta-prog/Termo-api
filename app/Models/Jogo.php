<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jogo extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'palavra', 'tentativas_restantes', 'venceu'
    ];

    protected $casts = [
        'venceu' => 'boolean',
    ];
}