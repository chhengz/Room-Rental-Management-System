<?php

use App\Models\Building;
use App\Models\Invoice;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rejects allocations that exceed payment amount', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'accountant', 'label' => 'Accountant']);
    $permission = Permission::create(['name' => 'payments.manage', 'label' => 'Manage Payments']);
    $role->permissions()->attach($permission->id);
    $user->roles()->attach($role->id);

    $building = Building::create(['name' => 'Building A', 'code' => 'A', 'total_floors' => 3, 'is_active' => true]);
    $roomType = RoomType::create(['name' => 'Standard', 'default_capacity' => 2]);
    Room::create([
        'building_id' => $building->id,
        'room_type_id' => $roomType->id,
        'room_number' => 'A-101',
        'floor' => 1,
        'capacity' => 2,
        'rental_rate' => 180,
        'status' => 'occupied',
    ]);

    $tenant = Tenant::create(['full_name' => 'Tenant One', 'status' => 'active']);
    $invoice = Invoice::create([
        'invoice_number' => 'INV-100',
        'tenant_id' => $tenant->id,
        'issue_date' => '2026-01-01',
        'due_date' => '2026-01-10',
        'period_start' => '2026-01-01',
        'period_end' => '2026-01-31',
        'status' => 'issued',
        'subtotal' => 200,
        'discount_total' => 0,
        'late_fee_total' => 0,
        'total_amount' => 200,
        'paid_amount' => 0,
        'balance_amount' => 200,
    ]);

    $this->actingAs($user)->postJson('/api/v1/payments', [
        'payment_number' => 'PAY-1',
        'tenant_id' => $tenant->id,
        'payment_date' => '2026-01-05',
        'amount' => 100,
        'method' => 'cash',
        'status' => 'recorded',
        'allocations' => [
            ['invoice_id' => $invoice->id, 'amount' => 120],
        ],
    ])->assertUnprocessable();
});
