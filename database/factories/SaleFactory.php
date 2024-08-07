<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{


    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sale::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $total = $this->faker->randomFloat(2, 0, 5000);
        //$discountRand = $this->faker->randomElement([0, 2, 8, 10]);
       // $dto = intval(($total * $discountRand) / 100);

        return [
            'total' => $total,            
            'items' => $this->faker->numberBetween(1, 10),         
            'cash' => 0,
            'change' => 10,
            'user_id' => User::all()->random()->id            
        ];
    }
}
