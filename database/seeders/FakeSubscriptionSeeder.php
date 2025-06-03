<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class FakeSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = 'password'; 
        $this->command->info("Using default password for test users: '{$defaultPassword}'");

        // --- 1. مستخدم لخطة Starter ---
        $starterUserEmail = 'user.starter@example.com';
        $starterPlanSlug = 'starter';

        $userStarter = User::firstOrCreate(
            ['email' => $starterUserEmail],
            [
                'name' => 'Starter Plan User',
                'password' => Hash::make($defaultPassword),
                'role' => 'customer', 
                'email_verified_at' => now(),
            ]
        );
        $planStarter = Plan::where('slug', $starterPlanSlug)->first();

        if ($userStarter && $planStarter) {
            $userStarter->subscriptions()->delete();

            Subscription::create([
                'user_id' => $userStarter->id,
                'plan_id' => $planStarter->id,
                'starts_at' => now(),
                'ends_at' => now()->addDays($planStarter->duration_in_days),
                'status' => 'active',
                'properties_listed_count' => 0,
                'metadata' => $planStarter->features,
            ]);
            $userStarter->role = 'property_lister';
            $userStarter->save();
            $this->command->info("User '{$userStarter->email}' subscribed to '{$planStarter->name}'. Role: property_lister.");
        } else {
            $this->command->error("Could not set up Starter User or Plan.");
        }

        // --- 2. مستخدم لخطة Professional ---
        $profUserEmail = 'user.professional@example.com';
        $profPlanSlug = 'professional';

        $userProf = User::firstOrCreate(
            ['email' => $profUserEmail],
            [
                'name' => 'Professional Plan User',
                'password' => Hash::make($defaultPassword),
                'role' => 'customer',
                'email_verified_at' => now(),
            ]
        );
        $planProf = Plan::where('slug', $profPlanSlug)->first();

        if ($userProf && $planProf) {
            $userProf->subscriptions()->delete();
            Subscription::create([
                'user_id' => $userProf->id,
                'plan_id' => $planProf->id,
                'starts_at' => now(),
                'ends_at' => now()->addDays($planProf->duration_in_days),
                'status' => 'active',
                'properties_listed_count' => 0,
                'metadata' => $planProf->features,
            ]);
            $userProf->role = 'property_lister';
            $userProf->save();
            $this->command->info("User '{$userProf->email}' subscribed to '{$planProf->name}'. Role: property_lister.");
        } else {
            $this->command->error("Could not set up Professional User or Plan.");
        }

        // --- 3. مستخدم لخطة Enterprise ---
        $entUserEmail = 'user.enterprise@example.com';
        $entPlanSlug = 'enterprise';

        $userEnt = User::firstOrCreate(
            ['email' => $entUserEmail],
            [
                'name' => 'Enterprise Plan User',
                'password' => Hash::make($defaultPassword),
                'role' => 'customer',
                'email_verified_at' => now(),
            ]
        );
        $planEnt = Plan::where('slug', $entPlanSlug)->first();

        if ($userEnt && $planEnt) {
            $userEnt->subscriptions()->delete();
            Subscription::create([
                'user_id' => $userEnt->id,
                'plan_id' => $planEnt->id,
                'starts_at' => now(),
                'ends_at' => now()->addDays($planEnt->duration_in_days),
                'status' => 'active',
                'properties_listed_count' => 0,
                'metadata' => $planEnt->features,
            ]);
            $userEnt->role = 'property_lister';
            $userEnt->save();
            $this->command->info("User '{$userEnt->email}' subscribed to '{$planEnt->name}'. Role: property_lister.");
        } else {
            $this->command->error("Could not set up Enterprise User or Plan.");
        }

        $this->command->info("Fake subscriptions for paid plans created successfully. Default password: '{$defaultPassword}'");
    }
}