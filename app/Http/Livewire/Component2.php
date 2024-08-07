<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Component2 extends Component
{
    public $parametro;
    public function render()
    {
        return view('livewire.component2');
    }

   protected $listeners = [
        'evento1' => 'mostrar'
    ];


    public function mostrar($param)
    {
        $this->parametro = $param;        
    }
}
