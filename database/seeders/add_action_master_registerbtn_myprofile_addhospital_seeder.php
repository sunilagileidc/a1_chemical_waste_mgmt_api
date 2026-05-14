<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_action_master_registerbtn_myprofile_addhospital_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('action_master')->insert([
            [
                'action_name' => 'APPROVE REG USER',
                'category' => 'Others',
                'description' => null,
                'status' => 1,
                'slug' => 'others-approve-reg-user',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'action_name' => 'REJECT REG USER',
                'category' => 'Others',
                'description' => null,
                'status' => 1,
                'slug' => 'others-reject-reg-user',
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'action_name' => 'REGISTER DRUG',
                'category' => 'Drugs',
                'description' => null,
                'status' => 1,
                'slug' => 'drugs-register-drug',
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'action_name' => 'FORCE REREGISTER DRUG',
                'category' => 'Drugs',
                'description' => null,
                'status' => 1,
                'slug' => 'drugs-force-reregister-drug',
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'action_name' => 'ADD NEW HOSPITAL',
                'category' => 'Others',
                'description' => null,
                'status' => 1,
                'slug' => 'institution-add-hospital',
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'action_name' => 'VIEW PROFILE DETAILS',
                'category' => 'Others',
                'description' => null,
                'status' => 1,
                'slug' => 'others-view-profile-details',
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
