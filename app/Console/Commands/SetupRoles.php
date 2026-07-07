<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SetupRoles extends Command
{
    protected $signature = 'app:setup-roles';
    protected $description = 'Create admin role and assign all permissions';

    public function handle()
    {
        // Reset cached roles and permissions
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Create permissions
        Permission::firstOrCreate(['name' => 'delete routes', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete cities', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'restore routes', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'restore cities', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view trashed', 'guard_name' => 'web']);

        // Create admin role with all permissions
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $this->info('Roles and permissions created successfully!');
        $this->warn('Run: php artisan app:make-admin {email} to assign admin role to a user.');

        return Command::SUCCESS;
    }
}
