<?php
namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_action_master_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('action_master')->insert([
            [
                'action_name' => 'APPROVE PAF',
                'category'    => 'PAF',
                'description' => null,
                'status'      => 1,
                'slug'        => 'paf-add-paf',
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'action_name' => 'APPROVE DISPENSE PAF',
                'category'    => 'PAF',
                'description' => null,
                'status'      => 1,
                'slug'        => 'paf-approve-dispense-paf',
                'created_by'  => 1,
                'updated_by'  => null,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'action_name' => 'REJECT PAF',
                'category'    => 'PAF',
                'description' => null,
                'status'      => 1,
                'slug'        => 'paf-reject-paf',
                'created_by'  => 1,
                'updated_by'  => null,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
        ]);
    }
}
