<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_menu_policiesandquestions_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('menus')->insert([
            [
                'title' => 'Policies & Questions',
                'href' => 'policies_questions_setup',
                'parent_id' => 0,
                'seq' => 10,
                'icon' => 'mdi mdi-help-rhombus',
                'slug' => 'policies-questions-setup',
            ],
        ]);
    }
}
