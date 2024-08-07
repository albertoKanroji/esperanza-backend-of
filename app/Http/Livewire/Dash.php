<?php

namespace App\Http\Livewire;


use DateTime;
use App\Models\Sale;
use Livewire\Component;
use App\Models\SaleDetail;
use Illuminate\Support\Facades\DB;

class Dash extends Component
{

    public $salesByMonth_Data = [], $year, $listYears = [], $top5Data = [], $weekSales_Data = [];

    public function mount()
    {
        $this->year = date('Y');
    }

    public function render()
    {

        $this->getWeekSales();
        $this->getTop5();
        $this->getSalesMonth();

        return view('livewire.dash.component')->extends('layouts.theme.app')
            ->section('content');
    }

    public function getTop5()
    {
        $this->top5Data = SaleDetail::join('products as p', 'sale_details.product_id', 'p.id')
            ->select(
                DB::raw("p.name AS product, SUM(sale_details.quantity * sale_details.price)AS total"),
            )->whereYear("sale_details.created_at", $this->year)
            ->groupBy('p.name')
            ->orderBy(DB::raw("SUM(sale_details.quantity * sale_details.price) "), 'desc')
            ->get()->take(5)->toArray();

        $contDif = (5 - count($this->top5Data));
        if ($contDif > 0) {
            for ($i = 1; $i <= $contDif; $i++) {
                array_push($this->top5Data, ["product" => "-", 'total' => 0]);
            }
        }
    }

    public function getWeekSales()
    {
        $dt = new DateTime(); // 2022-05-16 12:26:40.830580
        $startDate = null;
        $finishDate = null;

        for ($d = 1; $d <= 7; $d++) {

            // norma ISO 8601 year/mes/dia  =>  (año, semana, dia de la semana)

            $dt->setISODate($dt->format('o'), $dt->format('W'), $d);

            $startDate = $dt->format('Y-m-d') . ' 00:00:00';
            $finishDate = $dt->format('Y-m-d') . ' 23:59:59';
            $wsale = Sale::whereBetween('created_at', [$startDate, $finishDate])->sum('total');

            array_push($this->weekSales_Data, $wsale);
        }
    }

    public function getSalesMonth()
    {
        $this->sales = [];

        $salesByMonth = DB::select(
            DB::raw("SELECT coalesce(total,0)as total
                FROM (SELECT 'january' AS month UNION SELECT 'february' AS month UNION SELECT 'march' AS month UNION SELECT 'april' AS month UNION SELECT 'may' AS month UNION SELECT 'june' AS month UNION SELECT 'july' AS month UNION SELECT 'august' AS month UNION SELECT 'september' AS month UNION SELECT 'october' AS month UNION SELECT 'november' AS month UNION SELECT 'december' AS month ) m LEFT JOIN (SELECT MONTHNAME(created_at) AS MONTH, COUNT(*) AS orders, SUM(total)AS total 
                FROM sales WHERE year(created_at)= $this->year
                GROUP BY MONTHNAME(created_at),MONTH(created_at) 
                ORDER BY MONTH(created_at)) c ON m.MONTH =c.MONTH;")
        );

        foreach ($salesByMonth as $sale) {
            array_push($this->salesByMonth_Data, $sale->total);
        }
    }
}
