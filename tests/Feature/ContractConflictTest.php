<?php

use App\Models\Building;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rejects overlapping active contracts for the same room', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'super_administrator', 'label' => 'Super Administrator']);
    $permission = Permission::create(['name' => 'contracts.manage', 'label' => 'Manage Contracts']);
    $role->permissions()->attach($permission->id);
    $user->roles()->attach($role->id);

    $building = Building::create(['name' => 'Building A', 'code' => 'A', 'total_floors' => 3, 'is_active' => true]);
    $roomType = RoomType::create(['name' => 'Standard', 'default_capacity' => 2]);
    $room = Room::create([
        'building_id' => $building->id,
        'room_type_id' => $roomType->id,
        'room_number' => 'A-101',
        'floor' => 1,
        'capacity' => 2,
        'rental_rate' => 180,
        'status' => 'occupied',
    ]);
    $tenant1 = Tenant::create(['full_name' => 'Tenant One', 'status' => 'active']);
    $tenant2 = Tenant::create(['full_name' => 'Tenant Two', 'status' => 'active']);

    $this->actingAs($user)->postJson('/api/v1/contracts', [
        'contract_number' => 'CTR-1',
        'tenant_id' => $tenant1->id,
        'room_id' => $room->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'rent_amount' => 200,
        'deposit_amount' => 200,
        'billing_frequency' => 'monthly',
        'due_day' => 5,
        'status' => 'active',
    ])->assertCreated();

    $this->actingAs($user)->postJson('/api/v1/contracts', [
        'contract_number' => 'CTR-2',
        'tenant_id' => $tenant2->id,
        'room_id' => $room->id,
        'start_date' => '2026-06-01',
        'end_date' => '2027-05-31',
        'rent_amount' => 200,
        'deposit_amount' => 200,
        'billing_frequency' => 'monthly',
        'due_day' => 5,
        'status' => 'active',
    ])->assertUnprocessable();
});
