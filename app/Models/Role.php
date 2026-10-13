<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Relación: Un Rol tiene muchos Usuarios.
     */
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'role_id');
    }
}