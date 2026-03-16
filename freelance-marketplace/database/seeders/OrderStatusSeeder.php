<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderStatusSeeder extends Seeder
{
    /**Run the database seeds.**/
    public function run(): void 
    {
        DB::table('order_statuses')->insert([
            ['name' => 'published'],
            ['name' => 'in_progress'],
            ['name' => 'completed'],
            ['name' => 'cancelled'],
        ]);
    }
}