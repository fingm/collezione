<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'cliente_id',
        'vendedor_id',
        'cliente_nombre',
        'cliente_telefono',
        'cliente_direccion',
        'tipo_entrega',
        'costo_flete',
        'metodo_pago',
        'cuotas',
        'subtotal',
        'descuento',
        'precio_total',
        'fecha_compra',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fecha_compra' => 'datetime',
        'costo_flete' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'precio_total' => 'decimal:2',
        'cuotas' => 'integer',
    ];

    // --- RELACIONES ---


    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'detalle_venta', 'venta_id', 'producto_id')
                    ->withPivot('cantidad', 'precio_unitario', 'subtotal')
                    ->withTimestamps();
    }
    // Relación con el Cliente (Usuario)
    public function cliente()
    {
        return $this->belongsTo(Usuario::class, 'cliente_id');
    }

    public function pagos()
    {
        return $this->hasMany(PagoVenta::class);
    }

    // Método helper útil para verificar si la venta fue cubierta por completo
    public function totalPagado()
    {
        return $this->pagos()->sum('monto');
    }

    public function vendedor(){
        return $this->belongsTo(Usuario::class,'vendedor_id');
    }

}