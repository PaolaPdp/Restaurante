<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
   use HasFactory;

    protected $fillable = [
        'mesa_id',
        'usuario_id',
        'estado',
        'total',
        'notas',
        'enviado_a_cocina_at',
        'entregado_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'enviado_a_cocina_at' => 'datetime',
        'entregado_at' => 'datetime',
    ];

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function mozo()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function venta()
    {
        return $this->hasOne(Venta::class);
    }

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_EN_COCINA = 'en_cocina';
    public const ESTADO_LISTO = 'listo';
    public const ESTADO_SERVIDO = 'servido';
    public const ESTADO_PAGADO = 'pagado';
    public const ESTADO_ANULADO = 'anulado';

    public function scopeAbiertos($query)
    {
        return $query->whereNotIn('estado', [self::ESTADO_PAGADO, self::ESTADO_ANULADO]);
    }

}
