<?php

use App\Enums\Buildings\BuildingType;
use App\Models\Building;
use App\Models\Ticket;
use App\Models\User;

test('building has correct fillable attributes', function () {
    $fillable = [
        'name',
        'code',
        'description',
        'address',
        'building_type',
        'floors',
        'total_rooms',
        'construction_year',
        'is_active',
        'building_supervisor_id',
        'contact_info',
        'latitude',
        'longitude',
    ];

    expect((new Building())->getFillable())->toBe($fillable);
});

test('building can be created with valid attributes', function () {
    $supervisor = User::factory()->create();
    
    $building = Building::factory()->create([
        'name' => 'Main Office',
        'code' => 'MOB-001',
        'description' => 'Main office building',
        'address' => '123 Main St',
        'building_type' => BuildingType::ACADEMIC_BLOCK,
        'floors' => 5,
        'total_rooms' => 50,
        'construction_year' => 2020,
        'is_active' => true,
        'building_supervisor_id' => $supervisor->id,
        'latitude' => 40.7128,
        'longitude' => -74.0060,
    ]);

    expect($building)->toBeInstanceOf(Building::class)
        ->and($building->name)->toBe('Main Office')
        ->and($building->code)->toBe('MOB-001')
        ->and($building->description)->toBe('Main office building')
        ->and($building->address)->toBe('123 Main St')
        ->and($building->building_type)->toBe(BuildingType::ACADEMIC_BLOCK)
        ->and($building->floors)->toBe(5)
        ->and($building->total_rooms)->toBe(50)
        ->and($building->construction_year)->toBe(2020)
        ->and($building->is_active)->toBeTrue();
});

test('building belongs to supervisor', function () {
    $supervisor = User::factory()->create();
    $building = Building::factory()->create(['building_supervisor_id' => $supervisor->id]);

    expect($building->supervisor)->toBeInstanceOf(User::class)
        ->and($building->supervisor->id)->toBe($supervisor->id);
});

test('building has many tickets', function () {
    $building = Building::factory()->create();
    $tickets = Ticket::factory()->count(3)->create(['building_id' => $building->id]);

    expect($building->tickets)->toHaveCount(3)
        ->and($building->tickets->first())->toBeInstanceOf(Ticket::class);
});

test('building active scope returns only active buildings', function () {
    Building::factory()->create(['is_active' => true]);
    Building::factory()->create(['is_active' => false]);

    $activeBuildings = Building::active()->get();

    expect($activeBuildings)->toHaveCount(1)
        ->and($activeBuildings->first()->is_active)->toBeTrue();
});

test('building of type scope filters by building type', function () {
    Building::factory()->create(['building_type' => BuildingType::ACADEMIC_BLOCK]);
    Building::factory()->create(['building_type' => BuildingType::BOYS_HOSTEL]);

    $academicBuildings = Building::ofType(BuildingType::ACADEMIC_BLOCK)->get();

    expect($academicBuildings)->toHaveCount(1)
        ->and($academicBuildings->first()->building_type)->toBe(BuildingType::ACADEMIC_BLOCK);
});

test('building ordered scope sorts by code and name', function () {
    Building::factory()->create(['code' => 'B001', 'name' => 'Building B']);
    Building::factory()->create(['code' => 'A001', 'name' => 'Building A']);

    $orderedBuildings = Building::ordered()->get();

    expect($orderedBuildings->first()->code)->toBe('A001')
        ->and($orderedBuildings->last()->code)->toBe('B001');
});

test('building full name attribute combines code and name', function () {
    $building = Building::factory()->create([
        'code' => 'MOB-001',
        'name' => 'Main Office',
    ]);

    expect($building->full_name)->toBe('MOB-001 - Main Office');
});

test('building has location returns true when coordinates are set', function () {
    $buildingWithLocation = Building::factory()->create([
        'latitude' => 40.7128,
        'longitude' => -74.0060,
    ]);
    
    $buildingWithoutLocation = Building::factory()->create([
        'latitude' => null,
        'longitude' => null,
    ]);

    expect($buildingWithLocation->hasLocation())->toBeTrue()
        ->and($buildingWithoutLocation->hasLocation())->toBeFalse();
});

test('building age attribute calculates correctly', function () {
    $currentYear = now()->year;
    
    $building = Building::factory()->create(['construction_year' => $currentYear - 5]);
    $buildingNoYear = Building::factory()->create(['construction_year' => null]);

    expect($building->age)->toBe(5)
        ->and($buildingNoYear->age)->toBeNull();
});

test('building casts attributes correctly', function () {
    $building = Building::factory()->create([
        'building_type' => BuildingType::ACADEMIC_BLOCK,
        'floors' => 5,
        'total_rooms' => 50,
        'construction_year' => 2020,
        'is_active' => true,
        'contact_info' => ['phone' => '123-456-7890'],
        'latitude' => 40.7128,
        'longitude' => -74.0060,
    ]);

    expect($building->building_type)->toBeInstanceOf(BuildingType::class)
        ->and($building->floors)->toBeInt()
        ->and($building->total_rooms)->toBeInt()
        ->and($building->construction_year)->toBeInt()
        ->and($building->is_active)->toBeBool()
        ->and($building->contact_info)->toBeArray()
        ->and($building->latitude)->toBeNumeric()
        ->and($building->longitude)->toBeNumeric();
});