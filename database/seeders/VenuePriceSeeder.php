<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VenuePricing;



class VenuePriceSeeder extends Seeder
{
  public function run()
  {
    VenuePricing::create(['base_hour_price' => 10.0, 'setter_name' => 'المبرمج','is_active'=>1]);
  }


}