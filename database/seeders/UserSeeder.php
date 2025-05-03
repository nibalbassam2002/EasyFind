<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; 
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'], // <-- البحث بهذا الإيميل
            [
                'name' => 'Admin User', 
                'password' => Hash::make('password'), 
                'role' => 'admin',
                'status' => 'active',
                
            ]
        );
        User::firstOrCreate(
            ['email' => 'adham@example.com'],
            [
                'name' => 'adham bassam',
                'password' => Hash::make('password'),
                'role' => 'property_lister',
                'status' => 'active',
            ]
        );
        
    }
}
