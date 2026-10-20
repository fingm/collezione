<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable,SoftDeletes;

    // Nombre explícito de la tabla en español
    protected $table = 'usuarios';

    protected $fillable = [
        'role_id',
        'nombre',
        'apellido',
        'tipo_documento',
        'documento',
        'telefono',
        'email',
        'password',
        'activo',
        'notas',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    // Accesor para nombre completo
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }

    // Relaciones
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function direcciones()
    {
        return $this->hasMany(Direccion::class, 'usuario_id');
    }

    public function direccionPrincipal()
    {
        return $this->hasOne(Direccion::class, 'usuario_id')->where('es_principal', true);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }

    // Helpers de rol
    public function esAdmin(): bool
    {
        return $this->role?->nombre === 'admin';
    }

    public function esVendedor(): bool
    {
        return $this->role?->nombre === 'vendedor';
    }

    public function esCliente(): bool
    {
        return $this->role?->nombre === 'cliente';
    }
    // En app/Models/Usuario.php

    // Ventas donde actúa como Vendedor
    public function ventasRealizadas()
    {
        return $this->hasMany(Venta::class, 'vendedor_id');
    }
}