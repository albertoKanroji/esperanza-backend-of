<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
        	'name' => 'Luis Fax',
        	'phone' => '3511159550',
        	'email' => 'luisfaax@gmail.com',
        	'profile' => 'ADMIN',
        	'status' => 'ACTIVE',
        	'password' => bcrypt('123')
        ]);
        User::create([
        	'name' => 'Melisa Albahat',
        	'phone' => '3549873214',
        	'email' => 'melisa@gmail.com',
        	'profile' => 'EMPLOYEE',
        	'status' => 'ACTIVE',
        	'password' => bcrypt('123')
        ]);
    }
}
