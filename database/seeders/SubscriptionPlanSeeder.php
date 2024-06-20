<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');

        SubscriptionPlan::insert([
            [
                'name' => 'Monthly',
                'stripe_price_id' => 'price_1PElK0Cxt4BF7lUlKLZmIFZ8',
                'trial_days' => 5,
                'amount' => 12,
                'type' => 0,
                'enabled' => 1,
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime
            ],
            [
                'name' => 'Yearly',
                'stripe_price_id' => 'price_1PElL6Cxt4BF7lUlROlBVYQr',
                'trial_days' => 5,
                'amount' => 100,
                'type' => 1,
                'enabled' => 1,
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime
            ],
            [
                'name' => 'Lifetime',
                'stripe_price_id' => 'price_1PElNyCxt4BF7lUlN4ctQBCs',
                'trial_days' => 5,
                'amount' => 400,
                'type' => 2,
                'enabled' => 1,
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime
            ],
        ]);

    }
}
