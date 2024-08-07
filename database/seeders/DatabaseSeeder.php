<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call(DenominationSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(UserSeeder::class);

        Sale::factory(1000)->create()->each(function ($sale) {
            $sale->details()->create([
                'sale_id' => $sale->id,
                'product_id' => Product::all()->random()->id,
                'quantity' => $sale->items,
                'price' => $sale->total / $sale->items
            ]);
        });
    }
}
