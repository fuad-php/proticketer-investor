<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Investor Management
            'view investors',
            'create investors',
            'edit investors',
            'delete investors',
            
            // Client Management
            'view clients',
            'create clients',
            'edit clients',
            'delete clients',
            
            // Order Management
            'view orders',
            'create orders',
            'edit orders',
            'delete orders',
            
            // Investment Management
            'view investments',
            'create investments',
            'edit investments',
            'delete investments',
            
            // Approval Management
            'view approvals',
            'create approvals',
            'approve orders',
            'reject orders',
            
            // Transaction Management
            'view transactions',
            'create transactions',
            'edit transactions',
            'delete transactions',
            
            // Inquiry Management
            'view inquiries',
            'create inquiries',
            'edit inquiries',
            'delete inquiries',
            'respond inquiries',
            
            // Reporting
            'view reports',
            'export reports',
            
            // System Management
            'view audit logs',
            'manage settings',
            'manage roles',
            'manage permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $accounts = Role::create(['name' => 'accounts']);
        $accounts->givePermissionTo([
            'view users',
            'view investors',
            'create investors',
            'edit investors',
            'view clients',
            'create clients',
            'edit clients',
            'view orders',
            'create orders',
            'edit orders',
            'view investments',
            'create investments',
            'edit investments',
            'view approvals',
            'create approvals',
            'view transactions',
            'create transactions',
            'edit transactions',
            'view inquiries',
            'edit inquiries',
            'respond inquiries',
            'view reports',
            'export reports',
        ]);

        $director = Role::create(['name' => 'director']);
        $director->givePermissionTo([
            'view orders',
            'view investments',
            'view approvals',
            'approve orders',
            'reject orders',
            'view transactions',
            'view inquiries',
            'view reports',
            'export reports',
        ]);

        $managingDirector = Role::create(['name' => 'managing_director']);
        $managingDirector->givePermissionTo([
            'view orders',
            'view investments',
            'view approvals',
            'approve orders',
            'reject orders',
            'view transactions',
            'view inquiries',
            'view reports',
            'export reports',
        ]);

        $chairman = Role::create(['name' => 'chairman']);
        $chairman->givePermissionTo([
            'view orders',
            'view investments',
            'view approvals',
            'approve orders',
            'reject orders',
            'view transactions',
            'view inquiries',
            'view reports',
            'export reports',
        ]);

        $investor = Role::create(['name' => 'investor']);
        $investor->givePermissionTo([
            'view investments',
            'view transactions',
            'view reports',
            'export reports',
        ]);

        $client = Role::create(['name' => 'client']);
        $client->givePermissionTo([
            'view inquiries',
            'create inquiries',
        ]);

        $auditor = Role::create(['name' => 'auditor']);
        $auditor->givePermissionTo([
            'view users',
            'view investors',
            'view clients',
            'view orders',
            'view investments',
            'view transactions',
            'view inquiries',
            'view reports',
            'export reports',
            'view audit logs',
        ]);
    }
}
