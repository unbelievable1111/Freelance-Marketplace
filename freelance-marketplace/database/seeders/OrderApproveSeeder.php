<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;

class OrderApproveSeeder extends Seeder
{
    /** Run the database seeds. **/
    public function run(): void
    {
        $latestOrder = Order::latest()->firstOrFail();
        $userRoleId = UserRole::where('name', 'executor')->value('id');

        $executors = User::where('user_role_id', $userRoleId)->get();

        $intros = [
            "Hi there!", "Hello!", "Good day!", "Hey!"
        ];

        $bodies = [
            "I have experience with similar projects.",
            "I can complete this task efficiently.",
            "This task matches my skills.",
            "I have worked on similar tasks before.",
        ];

        $endings = [
            "Ready to start immediately.",
            "Looking forward to working with you.",
            "Can start right away.",
            "Let me know if you're interested.",
        ];

        foreach ($executors as $executor) 
        {
            $days = rand(1, 14);
            $budget = rand(50, 500);

            $comment =  $intros[array_rand($intros)] . ' ' .
                        $bodies[array_rand($bodies)] . ' ' .
                        $endings[array_rand($endings)];

            $latestOrder->orderApproves()->create([
                'order_id' => $latestOrder->id,
                'user_id' => $executor->id,
                'comment' => $comment,
                'proposed_deadline_in_days' => $days,
                'proposed_budget' => $budget,
                'created_at' => now()->subMinutes(rand(60, 300))
            ]);
        }
    }
}