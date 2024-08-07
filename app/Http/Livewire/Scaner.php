<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Traits\CartTrait;


class Scaner extends Component
{
    use CartTrait;

    protected  $listeners = [        
        'scan-code' => 'changePage'
    ];

    public function changePage($code)
    {       
        dd($code);
        $this->ScanearCode($code);
        return redirect()->to('/pos');
    }
}
