<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    User::create(['name' => 'dev', 'email' => 'dev@gmail.com', 'password' => 'Qqwwee332211','role'=>'admin']);
    User::create(['name' => 'devMouzaf', 'email' => 'devMouzaf@gmail.com', 'password' => 'Qqwwee332211',]);
  }
}
