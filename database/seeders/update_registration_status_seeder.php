<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class update_registration_status_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update prescriber_details
        DB::table('prescriber_details')
            ->whereNull('reg_status')
            ->update([
                'reg_status' => 'Awaiting Approval',
                'updated_at' => now(),
            ]);

        // Update pharmacist_details
        DB::table('pharmacist_details')
            ->whereNull('reg_status')
            ->update([
                'reg_status' => 'Awaiting Approval',
                'updated_at' => now(),
            ]);
    }
}
