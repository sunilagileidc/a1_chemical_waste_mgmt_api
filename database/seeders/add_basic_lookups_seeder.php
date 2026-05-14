<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_basic_lookups_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lookups')->insert([

            [
                'shortname' => 'SALUTATION',
                'longname' => 'SALUTATION',
                'seq' => 1,
                'parent_id' => 0,
                'icon' => '',
                'status' => 1,
                'slug' => 'salutation',
            ],
            [
                'shortname' => 'GENDER',
                'longname' => 'GENDER',
                'seq' => 1,
                'parent_id' => 0,
                'icon' => '',
                'status' => 1,
                'slug' => 'gender',
            ],
            [
                'shortname' => 'TEMPLATE_TYPE',
                'longname' => 'TEMPLATE_TYPE',
                'seq' => 1,
                'parent_id' => 0,
                'icon' => '',
                'status' => 1,
                'slug' => 'template-type',
            ],
            [
                'shortname' => 'ACTION_CATEGORIES',
                'longname' => 'ACTION_CATEGORIES',
                'seq' => 1,
                'parent_id' => 0,
                'icon' => '',
                'status' => 1,
                'slug' => 'action-categories',
            ],
        ]);

        // Salutations
        $sal_parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'SALUTATION')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => 'Mr',
                'longname' => 'Mr',
                'seq' => 1,
                'parent_id' => $sal_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'mr',
            ],
            [
                'shortname' => 'Ms',
                'longname' => 'Ms',
                'seq' => 2,
                'parent_id' => $sal_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'ms',
            ],
            [
                'shortname' => 'Mrs',
                'longname' => 'Mrs',
                'seq' => 3,
                'parent_id' => $sal_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'mrs',
            ],
            [
                'shortname' => 'Miss',
                'longname' => 'Miss',
                'seq' => 4,
                'parent_id' => $sal_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'miss',
            ],
            [
                'shortname' => 'Dr',
                'longname' => 'Dr',
                'seq' => 5,
                'parent_id' => $sal_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'dr',
            ],
        ]);

        // Gender
        $g_parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'GENDER')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => 'Male',
                'longname' => 'Male',
                'seq' => 1,
                'parent_id' => $g_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'male',
            ],
            [
                'shortname' => 'Female',
                'longname' => 'Female',
                'seq' => 2,
                'parent_id' => $g_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'female',
            ],
            [
                'shortname' => 'Others',
                'longname' => 'Others',
                'seq' => 3,
                'parent_id' => $g_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'others',
            ],
        ]);

        // Email Template
        $temp_parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'TEMPLATE_TYPE')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => 'Email',
                'longname' => 'Email',
                'seq' => 1,
                'parent_id' => $temp_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'Email',
            ],
            [
                'shortname' => 'SMS/Notification',
                'longname' => 'SMS/Notification',
                'seq' => 2,
                'parent_id' => $temp_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'sms-notification',
            ],
        ]);

        // Action Cat
        $ac_parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'ACTION_CATEGORIES')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => 'Others',
                'longname' => 'Others',
                'seq' => 1,
                'parent_id' => $ac_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'others',
            ],
            [
                'shortname' => 'Institution',
                'longname' => 'Institution',
                'seq' => 2,
                'parent_id' => $ac_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'institution',
            ],
            [
                'shortname' => 'Drugs',
                'longname' => 'Drugs',
                'seq' => 3,
                'parent_id' => $ac_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'drugs',
            ],
            [
                'shortname' => 'Patient',
                'longname' => 'Patient',
                'seq' => 4,
                'parent_id' => $ac_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'patient',
            ],
        ]);
    }
}
