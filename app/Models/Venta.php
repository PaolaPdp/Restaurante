<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'pedido_id',
        'total',
        'tipo_pago',
        'registrado_por',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'total' => 'decimal:2',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
