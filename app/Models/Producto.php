<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'categoria',
        'precio',
        'estado',
        'descripcion',
        'requiere_cocina',
    ];

    protected $casts = [
        'requiere_cocina' => 'boolean',
        'precio' => 'decimal:2',
    ];

    public const CATEGORIA_ENTRADA = 'entrada';
    public const CATEGORIA_MENU = 'menu';
    public const CATEGORIA_EXTRA = 'extra';
    public const CATEGORIA_BEBIDA = 'bebida';
    public const CATEGORIA_EJECUTIVO = 'ejecutivo';

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function categoria()
{
    return $this->belongsTo(\App\Models\Categoria::class, 'categoria_id');
}

}
