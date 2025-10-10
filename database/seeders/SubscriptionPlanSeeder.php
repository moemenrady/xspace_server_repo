<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;



class SubscriptionPlanSeeder extends Seeder
{
  public function run()
  {
    SubscriptionPlan::create(['name' => 'أسبوعي', 'visits_count' => 7, 'duration_days' => 14, 'price' => 100]);
    SubscriptionPlan::create(['name' => 'نصف شهري', 'visits_count' => 15, 'duration_days' => 30, 'price' => 180]);
    SubscriptionPlan::create(['name' => 'شهري', 'visits_count' => 30, 'duration_days' => 60, 'price' => 300]);
  }
}

