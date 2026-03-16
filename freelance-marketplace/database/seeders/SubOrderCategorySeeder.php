<?php

namespace Database\Seeders;

use App\Models\MainOrderCategory;
use Illuminate\Database\Seeder;

class SubOrderCategorySeeder extends Seeder
{
    /** Run the database seeds. */
    public function run(): void
    {
        $categories = [
            'IT and development' => [
                'Web Development',
                'Mobile Applications',
                'Desktop Applications',
                'Backend / API',
                'Frontend',
                'Databases',
                'Bug Fixing',
                'DevOps / Servers',
                'Cybersecurity',
            ],

            'Design and creativity' => [
                'Graphic Design',
                'Web Design',
                'UI / UX Design',
                'Logos & Branding',
                'Banners and Advertising',
                'Illustrations',
                '3D Modeling',
                'Animation',
            ],

            'Texts and content' => [
                'Copywriting',
                'Rewriting',
                'SEO Texts',
                'Website Content',
                'Technical Documentation',
                'Scripts',
                'Translations',
            ],

            'Marketing and promotion' => [
                'Digital Marketing',
                'SMM',
                'SEO Promotion',
                'Context Advertising',
                'Email Marketing',
                'Analytics',
            ],

            'Video and audio' => [
                'Video Editing',
                'Motion Design',
                'Voice Acting',
                'Sound Design',
                'Music Production',
                'Podcast Editing',
            ],

            'Training and assistance' => [
                'Tutoring',
                'Consultations',
                'Homework Help',
                'Coursework Assistance',
                'Technical Mentoring',
            ],

            'Other' => [
                'Virtual Assistant',
                'Data Entry',
                'Customer Support',
                'Other Tasks',
            ],
        ];

        foreach ($categories as $mainCategoryName => $subCategories) {
            $mainCategory = MainOrderCategory::where('name', $mainCategoryName)->first();

            if (!$mainCategory) {
                continue;
            }

            foreach ($subCategories as $subCategory) {
                $mainCategory->subOrderCategories()->create([
                    'name' => $subCategory,
                ]);
            }
        }
    }
}