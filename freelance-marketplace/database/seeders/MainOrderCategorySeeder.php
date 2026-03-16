<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MainOrderCategorySeeder extends Seeder
{
    /** Run the database seeds. **/
    public function run(): void
    {
        DB::table('main_order_categories')->insert([
            ['name' => 'IT and development'],
            ['name' => 'Design and creativity'],
            ['name' => 'Texts and content'],
            ['name' => 'Marketing and promotion'],
            ['name' => 'Video and audio'],
            ['name' => 'Training and assistance'],
            ['name' => 'Other'],
        ]);
    }
}