<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('notification_types')->insert([
            ['name' => 'chat_started',              'description' => 'Chat started'],                            
            ['name' => 'chat_message_received',     'description' => 'Chat message received'],      
            ['name' => 'order_approved',            'description' => 'Order approved'],                      
            ['name' => 'order_comment_received',    'description' => 'Order comment received'],      
            ['name' => 'order_proposal_received',   'description' => 'Order proposal received'],        
            ['name' => 'order_completed',           'description' => 'Order completed'], 
            ['name' => 'order_cancelled',           'description' => 'Order cancelled'], 
            ['name' => 'review_received',           'description' => 'Review received'],
            ['name' => 'report_started',            'description' => 'Report started'],
            ['name' => 'report_answer_received',    'description' => 'Report answer received'],
            ['name' => 'report_completed',          'description' => 'Report completed'],
        ]);
    }
}
?>