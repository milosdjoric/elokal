<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\TaxRate;
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

        TaxRate::firstOrCreate(
            ['country' => 'RS', 'is_default' => true],
            ['name' => 'PDV 20%', 'rate' => 20.00, 'is_active' => true],
        );

        // Feature flagovi
        $features = config('features', []);
        foreach ($features as $key => $default) {
            Setting::setValue('features', $key, $default ? 'true' : 'false');
        }
    }
}
