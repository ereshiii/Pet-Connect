<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class FixAdminAccount extends Command
{
    protected $signature = 'admin:fix';
    protected $description = 'Fix the admin@petconnect.ph account to have proper admin access';

    public function handle()
    {
        $adminEmail = 'admin@petconnect.ph';
        
        $admin = User::where('email', $adminEmail)->first();
        
        if (!$admin) {
            $this->error("Admin account '{$adminEmail}' not found!");
            return 1;
        }
        
        $this->info("Found admin account: {$admin->email} (ID: {$admin->id})");
        $this->info("Current status: Type={$admin->account_type}, Admin=" . ($admin->is_admin ? 'YES' : 'NO'));
        
        if ($this->confirm("Fix admin account to have proper admin access?")) {
            $admin->account_type = 'admin';
            $admin->is_admin = true;
            $admin->save();
            
            $this->info("✅ Admin account '{$adminEmail}' has been fixed!");
            $this->info("Account type is now 'admin' and admin flag is set to true.");
            $this->info("The switch account feature should now work properly.");
        } else {
            $this->info("Operation cancelled.");
        }
        
        return 0;
    }
}