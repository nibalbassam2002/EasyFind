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
            'name' => 'Free',
            'currency' => 'USD',
            'price' => 0.00,
            'duration_in_days' => 30,
            'description' => 'Explore properties or list 5 tent properties for 30 days.',
            'features' => [
                'max_properties' => 5,
                'featured_slots' => 0,
                'property_view' => true,
                'property_details' => true, 
                'simple_search' => true,
                'advanced_search_filters' => false,
                'agent_profile' => false,
                'direct_contact_info' => false,
                'analytics_dashboard' => false,
                'support_level' => "community", 
                'allowed_types' => ['tents'], 
            ],
            'is_active' => true,
        ]);

       
        Plan::updateOrCreate(['slug' => 'starter'], [
            'name' => 'Starter',
            'currency' => 'USD',
            'price' => 19.99,
            'duration_in_days' => 30,
            'description' => 'Perfect for individuals listing up to 10 properties with essential tools and visibility.',
            'features' => [
                'max_properties' => 10,
                'featured_slots' => 1,
                'property_view' => true,
                'property_details' => true,
                'simple_search' => true,
                'advanced_search_filters' => true, 
                'agent_profile' => false, 
                'direct_contact_info' => true,
                'analytics_dashboard' => false,
                'support_level' => "basic_email",
                'allowed_types' => ['tents', 'caravans', 'apartments'],
            ],
            'is_active' => true,
        ]);

       
        Plan::updateOrCreate(['slug' => 'professional'], [
            'name' => 'Professional',
            'currency' => 'USD',
            'price' => 49.99,
            'duration_in_days' => 30,
            'description' => 'Ideal for agents & small agencies listing up to 25 properties with advanced tools and support.',
            'features' => [
                'max_properties' => 25,
                'featured_slots' => 5,
                'property_view' => true,
                'property_details' => true,
                'simple_search' => true,
                'advanced_search_filters' => true, 
                'agent_profile' => true, 
                'direct_contact_info' => true,
                'analytics_dashboard' => true,
                'support_level' => "priority_email",
                'allowed_types' => ['tents', 'caravans', 'apartments', 'houses', 'lands'], 
            ],
            'is_active' => true,
        ]);

        // خطة الشركات
        Plan::updateOrCreate(['slug' => 'enterprise'], [
            'name' => 'Enterprise',
            'currency' => 'USD',
            'price' => 99.99,
            'duration_in_days' => 30,
            'description' => 'Comprehensive solution for large agencies with up to 100 listings and premium features.',
            'features' => [
                'max_properties' => 100,
                'featured_slots' => 15,
                'property_view' => true,
                'property_details' => true,
                'simple_search' => true,
                'advanced_search_filters' => true, // فلاتر بحث متكاملة
                'agent_profile' => true, // ملف شخصي مميز مع خيارات تخصيص
                'direct_contact_info' => true,
                'analytics_dashboard' => true, // لوحة إحصائيات متقدمة
                'support_level' => "dedicated_manager",
                'allowed_types' => ['all'], // أو قائمة بكل الأنواع
            ],
            'is_active' => true,
        ]);
    }
}
