<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     **/
    public function run(): void
    {
        DB::table('report_statuses')->insert([
            ['name' => 'in_progress',   'description' => 'The report is currently being reviewed by the support team.'],
            ['name' => 'completed',     'description' => 'The report has been completed and reviewed.'],
        ]);
    }
}