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

            ['name' => 'My card', 'card_number' => '1732542384315623', 'user_id' => '2'],
            ['name' => 'My second card', 'card_number' => '253254778431233', 'user_id' => '2'],
            ['name' => 'My third card', 'card_number' => '3732447734314764', 'user_id' => '2'],

            ['name' => '1st card', 'card_number' => '1732542384313323', 'user_id' => '3'],
            ['name' => 'My 2nd card', 'card_number' => '2532423778431233', 'user_id' => '3'],
            ['name' => 'My 3rd card', 'card_number' => '3732444234314764', 'user_id' => '3'],
        ]);
    }
}
