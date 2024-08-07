<?php

namespace App\Http\Livewire;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use DB;
use Livewire\Component;

class DashController extends Component
{
    public $year = 2021;

    public function render()
    {   
        // sales by month    
        $month = ['1','2','3','4','5','6','7','8','9','10','11','12'];         

        $sales = [];
        foreach ($month as $value) {
            $sales[] = Sale::whereMonth("created_at", $value)
            ->whereYear('created_at', $this->year)
            ->sum('total');
        }
        //dd($sales);
// ->leftJoin('products as p', 'p.id')

        // top 5 best products
        /*
        $top5 = Product::with('sales')
                    ->leftJoin('sale_details as sd','sd.sale_id','sales.id')                   
                    ->select('products.name as product', DB::raw('sum(sd.quantity)as qty','sum(sd.price * sd.quantity) total'))
                    ->groupBy('p.id')
                    ->orderBy('total','desc')
                    ->take(5)
                    ->get();
                    */


                    $d= Product::whereHas('ventas')
                    ->join('sale_details as d','products.id','d.product_id')
                    ->select('products.name',DB::raw('sum(d.quantity)as items'),DB::raw('sum(d.price * d.quantity) as total'))
                    ->groupBy('products.id')
                    ->orderBy('total','desc')
                    ->take(5)
                    ->get();

                    dd($d);

                    //dd(User::whereHas('sales')->get());


                    return view('livewire.dash');
                }
            }
