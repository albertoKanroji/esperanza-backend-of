<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Blank extends Component
{
    public function render()
    {
        return view('livewire.blank')
        ->extends('layouts.theme.app')
		->section('content');
    }
}
