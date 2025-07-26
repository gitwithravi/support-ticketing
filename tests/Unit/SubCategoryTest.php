<?php

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Ticket;

test('sub category has correct fillable attributes', function () {
    $fillable = [
        'category_id',
        'name',
        'description',
        'color',
        'icon',
        'is_active',
        'sort_order',
    ];

    expect((new SubCategory())->getFillable())->toBe($fillable);
});

test('sub category can be created with valid attributes', function () {
    $category = Category::factory()->create();
    
    $subCategory = SubCategory::factory()->create([
        'category_id' => $category->id,
        'name' => 'Laptop Issues',
        'description' => 'Issues related to laptops',
        'color' => '#00ff00',
        'icon' => 'heroicon-o-device-phone-mobile',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    expect($subCategory)->toBeInstanceOf(SubCategory::class)
        ->and($subCategory->name)->toBe('Laptop Issues')
        ->and($subCategory->description)->toBe('Issues related to laptops')
        ->and($subCategory->color)->toBe('#00ff00')
        ->and($subCategory->icon)->toBe('heroicon-o-device-phone-mobile')
        ->and($subCategory->is_active)->toBeTrue()
        ->and($subCategory->sort_order)->toBe(1);
});

test('sub category belongs to category', function () {
    $category = Category::factory()->create();
    $subCategory = SubCategory::factory()->create(['category_id' => $category->id]);

    expect($subCategory->category)->toBeInstanceOf(Category::class)
        ->and($subCategory->category->id)->toBe($category->id);
});

test('sub category has many tickets', function () {
    $subCategory = SubCategory::factory()->create();
    $tickets = Ticket::factory()->count(2)->create(['sub_category_id' => $subCategory->id]);

    expect($subCategory->tickets)->toHaveCount(2)
        ->and($subCategory->tickets->first())->toBeInstanceOf(Ticket::class);
});

test('sub category active scope returns only active sub categories', function () {
    SubCategory::factory()->create(['is_active' => true]);
    SubCategory::factory()->create(['is_active' => false]);

    $activeSubCategories = SubCategory::active()->get();

    expect($activeSubCategories)->toHaveCount(1)
        ->and($activeSubCategories->first()->is_active)->toBeTrue();
});

test('sub category ordered scope sorts by sort order and name', function () {
    SubCategory::factory()->create(['name' => 'Z Sub Category', 'sort_order' => 1]);
    SubCategory::factory()->create(['name' => 'A Sub Category', 'sort_order' => 2]);

    $orderedSubCategories = SubCategory::ordered()->get();

    expect($orderedSubCategories->first()->sort_order)->toBe(1)
        ->and($orderedSubCategories->last()->sort_order)->toBe(2);
});

test('sub category casts attributes correctly', function () {
    $subCategory = SubCategory::factory()->create([
        'is_active' => true,
        'sort_order' => 5,
    ]);

    expect($subCategory->is_active)->toBeBool()
        ->and($subCategory->sort_order)->toBeInt();
});