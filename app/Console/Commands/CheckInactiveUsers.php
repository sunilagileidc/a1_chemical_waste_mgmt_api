<?php
namespace App\Console\Commands;

use App\Models\LoginAudit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckInactiveUsers extends Command
{
    protected $signature = 'check:inactive-users';

    protected $description = 'Deactivate users who have not logged in for 6 months';

    public function handle()
    {
        $this->info('Starting inactive user check...');

        $sixMonthsAgo = Carbon::now()->subMonths(6);

        $users = User::where('status', 1)->get();

        $count = 0;

        foreach ($users as $user) {

            $lastLogin = LoginAudit::where('user_id', $user->id)
                ->latest('created_at')
                ->first();
            if (! $lastLogin) {
                $this->line("User ID {$user->id} skipped (no login record)");
                continue;
            }

            if (Carbon::parse($lastLogin->login_at)->toDateString() <= $sixMonthsAgo->toDateString()) {

                $user->status  = 0;
                $user->expired = 1;
                $user->save();

                $count++;

                $this->info("User ID {$user->id} deactivated (last login: {$lastLogin->login_at})");
            }
        }

        $this->info("Completed. Total users deactivated: {$count}");
    }
}
