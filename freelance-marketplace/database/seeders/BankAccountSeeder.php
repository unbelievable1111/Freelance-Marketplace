<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('bank_accounts')->insert([
            ['name' => 'My main card', 'card_number' => '5732542384311314', 'user_id' => '1'],
            ['name' => 'My additionary card', 'card_number' => '5732547784315131', 'user_id' => '1'],
            ['name' => 'My third card', 'card_number' => '5732547784318764', 'user_id' => '1'],

            ['name' => 'My card', 'card_number' => '5732542384315623', 'user_id' => '2'],
            ['name' => 'My second card', 'card_number' => '453254778431233', 'user_id' => '2'],
            ['name' => 'My third card', 'card_number' => '6732447734314764', 'user_id' => '2'],
        ]);
    }
}
