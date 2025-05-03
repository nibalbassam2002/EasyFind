<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $mainCategoriesData = [
            ['name' => 'Lands/Plots',       'slug' => 'lands'],         
            ['name' => 'Apartments/Flats',  'slug' => 'apartments'],     
            ['name' => 'Houses',            'slug' => 'houses'],         
            ['name' => 'Villas',            'slug' => 'villas'],         
            ['name' => 'Commercial',        'slug' => 'commercial'],   
            ['name' => 'Tents',             'slug' => 'tents'],          
            ['name' => 'Caravans',          'slug' => 'caravans'],     
        ];

        $createdMainCategories = [];
        echo "\n--- Seeding Main Categories ---\n";
        foreach ($mainCategoriesData as $catData) {
            $category = Category::firstOrCreate(
                ['slug' => $catData['slug']],
                ['name' => $catData['name'], 'parent_id' => null]
            );
            $createdMainCategories[$catData['slug']] = $category; // تخزين الكائن بالـ slug
            echo "Main Category '{$category->name}' ensured/created with ID: {$category->id}\n";
        }
        echo "-----------------------------\n";

        echo "\n--- Seeding Sub Categories ---\n";

        if (isset($createdMainCategories['commercial'])) {
            $parentId = $createdMainCategories['commercial']->id; 

            $sub = Category::firstOrCreate(['slug' => 'office'], ['name' => 'Office', 'parent_id' => $parentId]);
            echo "Sub Category '{$sub->name}' ensured/created with ID: {$sub->id} (Parent: Commercial)\n";

            $sub = Category::firstOrCreate(['slug' => 'shop'], ['name' => 'Shop', 'parent_id' => $parentId]);
             echo "Sub Category '{$sub->name}' ensured/created with ID: {$sub->id} (Parent: Commercial)\n";

             $sub = Category::firstOrCreate(['slug' => 'warehouse'], ['name' => 'Warehouse', 'parent_id' => $parentId]);
             echo "Sub Category '{$sub->name}' ensured/created with ID: {$sub->id} (Parent: Commercial)\n";
        } else {
            echo "Warning: Main category 'commercial' not found, cannot create sub-categories.\n";
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        echo "\n--- Category Seeding Finished ---\n";

        // طباعة ملخص للـ IDs الرئيسية لسهولة الرجوع إليها
        echo "\n--- Main Category IDs Summary ---\n";
        foreach ($createdMainCategories as $slug => $category) {
            echo "{$slug} ('{$category->name}'): ID = {$category->id}\n";
        }
        echo "---------------------------------\n";
    }
    }

