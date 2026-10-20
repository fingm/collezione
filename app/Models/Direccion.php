<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Direccion extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'direcciones';

    protected $fillable = [
        'usuario_id',
        'alias',
        'direccion',
        'numero_puerta',
        'apartamento',
        'esquina',
        'barrio',
        'ciudad',
        'departamento',
        'indicaciones',
        'es_principal',
    ];

    protected function casts(): array
    {
        return [
            'es_principal' => 'boolean',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function envios(){
        return $this->hasMany(Envio::class, 'direccion_id');
    }

    public function getDireccionCompletaAttribute(): string
    {
        $texto = $this->direccion;

        if ($this->numero_puerta) {
            $texto .= ' ' . $this->numero_puerta;
        }

        if ($this->apartamento) {
            $texto .= ' (Apto ' . $this->apartamento . ')';
        }

        if ($this->esquina) {
            $texto .= ' esq. ' . $this->esquina;
        }

        return $texto;
    }
}