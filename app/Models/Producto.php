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
    public const ESTADO_ACTIVO = 'activo';
    public const ESTADO_INACTIVO = 'inactivo';

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public static function categoriasDisponibles(): array
    {
        return [
            self::CATEGORIA_ENTRADA => 'Entrada',
            self::CATEGORIA_MENU => 'Menú',
            self::CATEGORIA_EXTRA => 'Extra',
            self::CATEGORIA_BEBIDA => 'Bebida',
            self::CATEGORIA_EJECUTIVO => 'Ejecutivo',
        ];
    }

    public static function categoriaLabel(string $categoria): string
    {
        return self::categoriasDisponibles()[$categoria] ?? ucfirst($categoria);
    }

    public static function estadosDisponibles(): array
    {
        return [
            self::ESTADO_ACTIVO => 'Activo',
            self::ESTADO_INACTIVO => 'Inactivo',
        ];
    }

    public static function estadoLabel(string $estado): string
    {
        return self::estadosDisponibles()[$estado] ?? ucfirst($estado);
    }

    public function categoria()
{
    return $this->belongsTo(\App\Models\Categoria::class, 'categoria_id');
}

}
