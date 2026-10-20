<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $guarded = [];

    protected $fillable = [
        'nombre',
        'marca',
        'modelo',
        'descripcion',
        'costo',
        'precio_venta',
        'stock',
        'activo',
        'garantia',
        'medidas',
    ];

    /**
     * Casts de atributos para forzar tipos de datos nativos en PHP
     */
    protected $casts = [
        'costo' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'stock' => 'integer',
        'activo' => 'boolean',
    ];

    /**
     * Scope para filtrar solo productos activos y disponibles
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function ventas(){
        return $this->belongsToMany(Venta::class, 'detalle_venta', 'producto_id', 'venta_id')
                ->withPivot('cantidad', 'precio_unitario', 'subtotal')
                ->withTimestamps();
    }

    // Relación con las ventas pasadas
    public function detallesVenta()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id');
    }

}
