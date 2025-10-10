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
    User::create(['name' => 'test', 'email' => 'test@gmail.com', 'password' => 'testtest','role'=>'admin']);
    User::create(['name' => 'testMouzaf', 'email' => 'testMouzaf@gmail.com', 'password' => 'testMouzaftestMouzaf',]);
  }
}
