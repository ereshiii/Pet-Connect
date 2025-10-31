<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckAdminUsers extends Command
{
    protected $signature = 'admin:check';
    protected $description = 'Check for admin users in the database';

    public function handle()
    {
        $this->info('Checking for admin users...');
        
        // Check all users
        $allUsers = User::all(['id', 'email', 'account_type', 'is_admin']);
        $this->info("Total users found: " . $allUsers->count());
        
        $this->table(['ID', 'Email', 'Account Type', 'Is Admin'], 
            $allUsers->map(fn($user) => [
                $user->id, 
                $user->email, 
                $user->account_type ?? 'null', 
                $user->is_admin ? 'YES' : 'NO'
            ])->toArray()
        );
        
        // Find admin users
        $adminUsers = User::where('is_admin', true)->orWhere('account_type', 'admin')->get();
        
        if ($adminUsers->count() > 0) {
            $this->info("Found {$adminUsers->count()} admin user(s):");
            foreach ($adminUsers as $admin) {
                $this->line("- ID: {$admin->id}, Email: {$admin->email}, Type: {$admin->account_type}, Admin Flag: " . ($admin->is_admin ? 'YES' : 'NO'));
            }
        } else {
            $this->warn('No admin users found!');
        }
        
        return 0;
    }
}