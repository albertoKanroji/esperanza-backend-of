<?php

namespace App\Http\Livewire;

use Darryldecode\Cart\Facades\CartFacade as Cart;
use App\Models\Denomination;
use App\Models\SaleDetail;
use Livewire\Component;
use App\Traits\CartTrait;
use App\Models\Product;
use App\Models\Sale;
use App\Traits\Utils;
use DB;

class PosController extends Component
{
	use Utils;
	use CartTrait;

	public $total, $itemsQuantity, $efectivo, $change;


	public function mount()
	{
		$this->efectivo = 0;
		$this->change = 0;
		$this->total  = Cart::getTotal();
		$this->itemsQuantity = Cart::getTotalQuantity();
	}

	public function render()
	{

		return view('livewire.pos.component', [
			'denominations' => Denomination::orderBy('value', 'desc')->get(),
			'cart' => Cart::getContent()->sortBy('name')
		])
			->extends('layouts.theme.app')
			->section('content');
	}

	// agregar efectivo / denominations
	public function ACash($value)
	{
		$this->efectivo += ($value == 0 ? $this->total : $value);
		$this->change = ($this->efectivo - $this->total);
	}

	// escuchar eventos
	protected $listeners = [
		'scan-code'  =>  'ScanCode',
		'removeItem' => 'removeItem',
		'clearCart'  => 'clearCart',
		'saveSale'   => 'saveSale',
		'refresh' => '$refresh',
		'scan-code-byid' => 'ScanCodeById'
	];

	public function ScanCodeById(Product $product)
	{
		$this->IncreaseQuantity($product);
	}

	

	// buscar y agregar producto por escaner y/o manual
	public function ScanCode($barcode, $cant = 1)
	{
		//dd('scan-code');
		$this->ScanearCode($barcode, $cant);
	}

	// incrementar cantidad item en carrito
	public function increaseQty(Product $product, $cant = 1)
	{
		$this->IncreaseQuantity($product, $cant);
	}

	// actualizar cantidad item en carrito
	public function updateQty(Product $product, $cant = 1)
	{
		if ($cant <= 0)
			$this->removeItem($product->id);
		else
			$this->UpdateQuantity($product, $cant);
	}

	// decrementar cantidad item en carrito
	public function decreaseQty($productId)
	{
		$this->decreaseQuantity($productId);
	}

	// vaciar carrito
	public function clearCart()
	{
		$this->trashCart();
	}


	public function cleanValue($value)
	{
		return  number_format(str_replace(",", "", $value), 2, '.', '');
	}


	// guardar venta
	public function saveSale()
	{
		if ($this->total <= 0) {
			$this->emit('sale-error', 'AGEGA PRODUCTOS A LA VENTA');
			return;
		}
		if ($this->efectivo <= 0) {
			$this->emit('sale-error', 'INGRESA EL EFECTIVO');
			return;
		}
		if ($this->total > $this->efectivo) {
			$this->emit('sale-error', 'EL EFECTIVO DEBE SER MAYOR O IGUAL AL TOTAL');
			return;
		}

		DB::beginTransaction();

		try {

			$sale = Sale::create([
				'total' => $this->total,
				'items' => $this->itemsQuantity,
				'cash' => $this->efectivo,
				'change' => $this->change,
				'user_id' => Auth()->user()->id
			]);

			if ($sale) {
				$items = Cart::getContent();
				foreach ($items as  $item) {
					SaleDetail::create([
						'price' => $item->price,
						'quantity' => $item->quantity,
						'product_id' => $item->id,
						'sale_id' => $sale->id,
					]);

					//update stock
					$product = Product::find($item->id);
					$product->stock = $product->stock - $item->quantity;
					$product->save();
				}
			}


			DB::commit();
			//$this->printTicket($sale->id);
			Cart::clear();
			$this->efectivo = 0;
			$this->change = 0;
			$this->total = Cart::getTotal();
			$this->itemsQuantity = Cart::getTotalQuantity();
			$this->emit('sale-ok', 'Venta registrada con éxito');
			
			$ticket = $this->buildTicket($sale);
			$d = $this->Encrypt($ticket);
			$this->emit('print-ticket', $d);
			
			//$this->emit('print-ticket', $sale->id);

		} catch (Exception $e) {
			DB::rollback();
			$this->emit('sale-error', $e->getMessage());
		}
	}


	public function printTicket($ventaId)
	{
		return \Redirect::to("print://$ventaId");
	}



	public function buildTicket($sale)
	{
		$details = SaleDetail::join('products as p', 'p.id', 'sale_details.product_id')
			->select('sale_details.*', 'p.name')
			->where('sale_id', $sale->id)
			->get();

		// opcion 1
		/*
		$products ='';
		$info = "folio: $sale->id|";
		$info .= "date: $sale->created_at|";		
		$info .= "cashier: {$sale->user->name}|";
		$info .= "total: $sale->total|";
		$info .= "items: $sale->items|";
		$info .= "cash: $sale->cash|";
		$info .= "change: $sale->change|";
		foreach ($details as $product) {
			$products .= $product->name .'}';
			$products .= $product->price .'}';
			$products .= $product->quantity .'}#';
		}

		$info .=$products;
		return $info;
		*/

		// opcion 2
		$sale->user_id = $sale->user->name;
		$r = $sale->toJson() . '|' . $details->toJson();
		//$array[] = json_decode($sale, true);
		//$array[] = json_decode($details, true);
		//$result = json_encode($array, JSON_PRETTY_PRINT);

		//dd($r);
		return $r;
	}


	public function printLast()
	{
		$lastSale = Sale::latest()->first();

		if ($lastSale)
			$this->emit('print-last-id', $lastSale->id);
	}
}
