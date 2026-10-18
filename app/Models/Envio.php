<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Envio extends Model
{
    use HasFactory;

    protected $table = 'envios';

    protected $fillable = [
        'venta_id',
        'direccion_id',
        'estado',
        'fecha_pactada',
        'fecha_entrega',
        'cadete_o_transporte',
        'notas_envio',
    ];

    protected $casts = [
        'fecha_pactada' => 'date',
        'fecha_entrega' => 'datetime',
    ];

    public function venta(){
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function direccion(){
        return $this->belongsTo(Direccion::class,'direccion_id');
    }

}
