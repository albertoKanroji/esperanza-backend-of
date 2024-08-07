<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Component1 extends Component
{
    public function render()
    {
        return view('livewire.component1')
        ->extends('layouts.theme.app')
        ->section('content');
    }

  

}
