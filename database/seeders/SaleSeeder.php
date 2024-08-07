<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;


class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $total = $this->faker->randomFloat(2, 0, 5000);
        $discountRand = $this->faker->randomElement([0, 2, 8, 10]);
        $dto = intval(($total * $discountRand) / 100);

        return [
                'total' => $total,
                'items' => $this->faker->numberBetween(1, 10),
                'cash' => $total + 10,
                'change' => $total - 10,
                'user_id' => User::all()->random()->id           
        ];
    }
}
