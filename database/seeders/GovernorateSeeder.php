<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Governorate;

class GovernorateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Governorate::firstOrCreate(['name' => 'North Gaza']);
        Governorate::firstOrCreate(['name' => 'Gaza']);
        Governorate::firstOrCreate(['name' => 'Middle Area']); 
        Governorate::firstOrCreate(['name' => 'Khan Younis']);
        Governorate::firstOrCreate(['name' => 'Rafah']);
    }
}
