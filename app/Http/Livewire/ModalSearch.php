<?php

namespace App\Http\Livewire;

use App\Models\Product;
//use App\Traits\CartTrait;
use Livewire\Component;

class ModalSearch extends Component
{    
    //use CartTrait;

    public $search, $products = [];

    protected $paginationTheme = 'bootstrap';

    public function liveSearch()
    {

        if (strlen($this->search) > 0) {
            $this->products = Product::join('categories as c', 'c.id', 'products.category_id')
                ->select('products.*', 'c.name as category')
                ->where('products.name', 'like', "%{$this->search}%")
                ->orWhere('products.barcode', 'like', "%{$this->search}%")
                ->orWhere('c.name', 'like', "%{$this->search}%")
                ->orderBy('products.name', 'asc')
                ->get()->take(10);
        } else {
            return $this->products = [];
        }
    }


    public function render()
    {
        $this->liveSearch();

        return view('livewire.modalsearch.component');
    }


    public function addAll()
    {
        if (count($this->products) > 0) {
            foreach ($this->products  as $product) {
                $this->emit('scan-code-byid', $product->id);
            }
        }
    }


}
