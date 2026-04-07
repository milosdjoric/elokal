<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingSeeder extends Seeder
{
    public function run(): void
    {
        $serbia = ShippingZone::create([
            'name' => 'Srbija',
            'countries' => ['RS'],
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $serbia->methods()->create([
            'name' => 'Standardna dostava',
            'type' => 'flat',
            'cost' => 350,
            'free_above' => 5000,
            'estimated_days' => '2-4',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $serbia->methods()->create([
            'name' => 'Express dostava',
            'type' => 'flat',
            'cost' => 700,
            'free_above' => null,
            'estimated_days' => '1-2',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }
}
