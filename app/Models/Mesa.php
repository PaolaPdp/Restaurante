<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    protected $table = 'mesas';

    protected $fillable = [
        'numero',
        'capacidad',
        'estado',
        'observaciones',
    ];

    public const ESTADO_LIBRE = 'libre';
    public const ESTADO_OCUPADA = 'ocupada';
    public const ESTADO_CUENTA = 'en_cuenta';
    public const ESTADO_BLOQUEADA = 'bloqueada';

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function scopeDisponibles($query)
    {
        return $query->where('estado', self::ESTADO_LIBRE);
    }
}
