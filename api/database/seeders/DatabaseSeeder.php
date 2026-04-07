<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            ShippingSeeder::class,
        ]);

        User::create([
            'name' => 'Marko Marković',
            'email' => 'marko@webshop.test',
            'password' => 'password',
            'phone' => '+381641234567',
        ]);
    }
}
