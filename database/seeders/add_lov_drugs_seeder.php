<?php
namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_lov_drugs_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('drugs')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = Carbon::now();

        DB::table('drugs')->insert([
            [
                'drug_name'  => 'Lenalidomide',
                'status'     => 1,
                'slug'       => 'lenalidomide',
                'validity'   => 24,
                'sequence'   => 1,
                'drug_form'  => 'Tablet',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'drug_name'  => 'Pomalidomide',
                'status'     => 1,
                'slug'       => 'pomalidomide',
                'validity'   => 24,
                'sequence'   => 2,
                'drug_form'  => 'Tablet',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'drug_name'  => '50mg - Thalidomide',
                'status'     => 1,
                'slug'       => '50thalidomide',
                'validity'   => 24,
                'sequence'   => 3,
                'drug_form'  => 'Capsule',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'drug_name'  => '100mg - Thalidomide Tablet',
                'status'     => 1,
                'slug'       => '100thalidomide',
                'validity'   => 24,
                'sequence'   => 4,
                'drug_form'  => 'Capsule',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

        ]);
    }
}
