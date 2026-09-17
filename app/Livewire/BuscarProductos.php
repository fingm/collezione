<?php


namespace App\Livewire;

use App\Models\Producto;
use Livewire\Component;
use Livewire\WithPagination;

class BuscarProductos extends Component
{
    use WithPagination;

    public $buscar = '';

    public function updatingBuscar()
    {
        $this->resetPage();
    }

    public function render()
    {
        $productos = Producto::when($this->buscar, function ($query) {
            return $query->where('nombre', 'like', '%' . $this->buscar . '%')
                         ->orWhere('marca', 'like', '%' . $this->buscar . '%')
                         ->orWhere('modelo', 'like', '%' . $this->buscar . '%');
        })->latest()->paginate(10);

        return view('livewire.buscar-productos', [
            'productos' => $productos,
        ]);
    }
}
