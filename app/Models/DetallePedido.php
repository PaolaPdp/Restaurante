<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'estado',
        'nota_cocina',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_EN_PREPARACION = 'en_preparacion';
    public const ESTADO_LISTO = 'listo';
    public const ESTADO_ENTREGADO = 'entregado';
    public const ESTADO_ANULADO = 'anulado';
}
