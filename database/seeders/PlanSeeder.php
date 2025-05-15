<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::updateOrCreate(['slug' => 'free'], [
            'name' => 'Free', 'price' => 0.00, 'duration_in_days' => 30, 
            'description' => 'For one month. 2 properties allowed to list. It\'s just for tents.',
            'features' => json_encode(['max_properties' => 2, 'allowed_types' => ['tent'], 'property_view' => true, 'property_details' => true, 'simple_search' => true])
        ]);
        Plan::updateOrCreate(['slug' => 'basic'], [
            'name' => 'Basic', 'price' => 49.99, 'duration_in_days' => 30,
            'description' => 'For one month. 2 properties allowed to list. It\'s great for personal use.',
            'features' => json_encode(['max_properties' => 2, 'allowed_types' => ['tent', 'apartment'], 'property_view' => true, 'property_details' => true, 'simple_search' => true]) // مثال
        ]);
    }
}
