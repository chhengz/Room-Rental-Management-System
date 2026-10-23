<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Permission;
use App\Models\RentalContract;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UtilityMeter;
use App\Models\UtilityRate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super_administrator'],
            ['label' => 'Super Administrator']
        );
        $managerRole = Role::firstOrCreate(
            ['name' => 'owner_manager'],
            ['label' => 'Owner / Manager']
        );
        $accountantRole = Role::firstOrCreate(
            ['name' => 'accountant'],
            ['label' => 'Accountant']
        );
        $staffRole = Role::firstOrCreate(
            ['name' => 'rental_staff'],
            ['label' => 'Rental Staff']
        );

        foreach ([
            ['name' => 'dashboard.view', 'label' => 'View dashboard'],
            ['name' => 'buildings.manage', 'label' => 'Manage buildings'],
            ['name' => 'rooms.manage', 'label' => 'Manage rooms'],
            ['name' => 'tenants.manage', 'label' => 'Manage tenants'],
            ['name' => 'contracts.manage', 'label' => 'Manage contracts'],
            ['name' => 'invoices.manage', 'label' => 'Manage invoices'],
            ['name' => 'payments.manage', 'label' => 'Manage payments'],
            ['name' => 'utilities.manage', 'label' => 'Manage utilities'],
            ['name' => 'expenses.manage', 'label' => 'Manage expenses'],
            ['name' => 'settings.manage', 'label' => 'Manage settings'],
        ] as $permissionData) {
            Permission::firstOrCreate(['name' => $permissionData['name']], $permissionData);
        }

        $superAdminRole->permissions()->sync(Permission::pluck('id')->all());
        $managerRole->permissions()->sync(Permission::whereIn('name', [
            'dashboard.view',
            'buildings.manage',
            'rooms.manage',
            'tenants.manage',
            'contracts.manage',
            'invoices.manage',
            'payments.manage',
            'utilities.manage',
            'expenses.manage',
        ])->pluck('id')->all());
        $accountantRole->permissions()->sync(Permission::whereIn('name', [
            'dashboard.view',
            'invoices.manage',
            'payments.manage',
            'expenses.manage',
            'utilities.manage',
        ])->pluck('id')->all());
        $staffRole->permissions()->sync(Permission::whereIn('name', [
            'dashboard.view',
            'rooms.manage',
            'tenants.manage',
            'contracts.manage',
        ])->pluck('id')->all());

        $admin = User::updateOrCreate(
            ['email' => 'admin@rental.local'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'phone' => '010100010',
                'email_verified_at' => now(),
            ]
        );
        $admin->roles()->sync([$superAdminRole->id]);

        $manager = User::updateOrCreate(
            ['email' => 'manager@rental.local'],
            [
                'name' => 'Building Manager',
                'password' => Hash::make('password'),
                'is_active' => true,
                'phone' => '010100020',
                'email_verified_at' => now(),
            ]
        );
        $manager->roles()->sync([$managerRole->id]);

        $buildingA = Building::firstOrCreate(
            ['code' => 'A'],
            ['name' => 'Building A', 'address' => 'Central Avenue', 'total_floors' => 5, 'is_active' => true]
        );
        $buildingB = Building::firstOrCreate(
            ['code' => 'B'],
            ['name' => 'Building B', 'address' => 'Riverside', 'total_floors' => 4, 'is_active' => true]
        );

        $standardType = RoomType::firstOrCreate(
            ['name' => 'Standard'],
            ['default_capacity' => 2, 'description' => 'Standard room']
        );
        $deluxeType = RoomType::firstOrCreate(
            ['name' => 'Deluxe'],
            ['default_capacity' => 3, 'description' => 'Deluxe room']
        );

        $roomA101 = Room::firstOrCreate(
            ['building_id' => $buildingA->id, 'room_number' => 'A-101'],
            ['room_type_id' => $standardType->id, 'floor' => 1, 'capacity' => 2, 'rental_rate' => 180, 'status' => 'occupied']
        );
        $roomA102 = Room::firstOrCreate(
            ['building_id' => $buildingA->id, 'room_number' => 'A-102'],
            ['room_type_id' => $standardType->id, 'floor' => 1, 'capacity' => 2, 'rental_rate' => 170, 'status' => 'available']
        );
        $roomB201 = Room::firstOrCreate(
            ['building_id' => $buildingB->id, 'room_number' => 'B-201'],
            ['room_type_id' => $deluxeType->id, 'floor' => 2, 'capacity' => 3, 'rental_rate' => 250, 'status' => 'occupied']
        );

        $tenant1 = Tenant::firstOrCreate(
            ['phone' => '070111111'],
            ['full_name' => 'Chan Dara', 'email' => 'dara@example.local', 'status' => 'active']
        );
        $tenant2 = Tenant::firstOrCreate(
            ['phone' => '070222222'],
            ['full_name' => 'Sok Nika', 'email' => 'nika@example.local', 'status' => 'active']
        );

        $contract1 = RentalContract::firstOrCreate(
            ['contract_number' => 'CTR-0001'],
            [
                'tenant_id' => $tenant1->id,
                'room_id' => $roomA101->id,
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->addYear()->subDay()->toDateString(),
                'rent_amount' => 180,
                'deposit_amount' => 180,
                'billing_frequency' => 'monthly',
                'due_day' => 5,
                'status' => 'active',
            ]
        );
        $contract2 = RentalContract::firstOrCreate(
            ['contract_number' => 'CTR-0002'],
            [
                'tenant_id' => $tenant2->id,
                'room_id' => $roomB201->id,
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->addYear()->subDay()->toDateString(),
                'rent_amount' => 250,
                'deposit_amount' => 250,
                'billing_frequency' => 'monthly',
                'due_day' => 5,
                'status' => 'active',
            ]
        );

        $invoice = Invoice::firstOrCreate(
            ['invoice_number' => 'INV-0001'],
            [
                'tenant_id' => $tenant1->id,
                'rental_contract_id' => $contract1->id,
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->endOfMonth()->toDateString(),
                'status' => 'issued',
                'subtotal' => 200,
                'discount_total' => 0,
                'late_fee_total' => 0,
                'total_amount' => 200,
                'paid_amount' => 100,
                'balance_amount' => 100,
                'created_by' => $admin->id,
            ]
        );

        InvoiceItem::firstOrCreate(
            ['invoice_id' => $invoice->id, 'description' => 'Monthly rent'],
            ['category' => 'rent', 'quantity' => 1, 'unit_price' => 180, 'line_total' => 180]
        );
        InvoiceItem::firstOrCreate(
            ['invoice_id' => $invoice->id, 'description' => 'Water utility'],
            ['category' => 'utility', 'quantity' => 1, 'unit_price' => 20, 'line_total' => 20]
        );

        $payment = Payment::firstOrCreate(
            ['payment_number' => 'PAY-0001'],
            [
                'tenant_id' => $tenant1->id,
                'payment_date' => now()->toDateString(),
                'amount' => 100,
                'allocated_amount' => 100,
                'method' => 'cash',
                'status' => 'recorded',
                'recorded_by' => $admin->id,
            ]
        );
        PaymentAllocation::firstOrCreate(
            ['payment_id' => $payment->id, 'invoice_id' => $invoice->id],
            ['amount' => 100]
        );

        $waterRate = UtilityRate::firstOrCreate(
            ['utility_type' => 'water', 'effective_from' => now()->startOfYear()->toDateString()],
            ['unit_rate' => 0.8, 'fixed_charge' => 2, 'effective_to' => null]
        );
        $electricRate = UtilityRate::firstOrCreate(
            ['utility_type' => 'electricity', 'effective_from' => now()->startOfYear()->toDateString()],
            ['unit_rate' => 0.25, 'fixed_charge' => 1, 'effective_to' => null]
        );

        $waterMeter = UtilityMeter::firstOrCreate(
            ['meter_code' => 'W-A101'],
            ['room_id' => $roomA101->id, 'type' => 'water', 'is_active' => true]
        );
        $electricMeter = UtilityMeter::firstOrCreate(
            ['meter_code' => 'E-A101'],
            ['room_id' => $roomA101->id, 'type' => 'electricity', 'is_active' => true]
        );

        MeterReading::firstOrCreate(
            ['utility_meter_id' => $waterMeter->id, 'reading_date' => now()->toDateString()],
            [
                'previous_reading' => 120,
                'current_reading' => 145,
                'consumption' => 25,
                'unit_rate' => $waterRate->unit_rate,
                'fixed_charge' => $waterRate->fixed_charge,
                'charge_amount' => 22,
                'recorded_by' => $admin->id,
            ]
        );
        MeterReading::firstOrCreate(
            ['utility_meter_id' => $electricMeter->id, 'reading_date' => now()->toDateString()],
            [
                'previous_reading' => 420,
                'current_reading' => 500,
                'consumption' => 80,
                'unit_rate' => $electricRate->unit_rate,
                'fixed_charge' => $electricRate->fixed_charge,
                'charge_amount' => 21,
                'recorded_by' => $admin->id,
            ]
        );

        $maintenanceCategory = ExpenseCategory::firstOrCreate(
            ['name' => 'Maintenance'],
            ['description' => 'Building and room maintenance']
        );
        Expense::firstOrCreate(
            ['reference' => 'EXP-0001'],
            [
                'expense_category_id' => $maintenanceCategory->id,
                'building_id' => $buildingA->id,
                'expense_date' => now()->toDateString(),
                'amount' => 75,
                'description' => 'Plumbing maintenance',
                'recorded_by' => $admin->id,
            ]
        );
    }
}
