<?php

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Ticket;
use App\Models\User;

test('category has correct fillable attributes', function () {
    $fillable = [
        'name',
        'description',
        'color',
        'icon',
        'is_active',
        'sort_order',
        'category_supervisor_id',
    ];

    expect((new Category)->getFillable())->toBe($fillable);
});

test('category can be created with valid attributes', function () {
    $supervisor = User::factory()->create();

    $category = Category::factory()->create([
        'name' => 'Hardware Issues',
        'description' => 'Issues related to hardware',
        'color' => '#ff0000',
        'icon' => 'heroicon-o-computer-desktop',
        'is_active' => true,
        'sort_order' => 1,
        'category_supervisor_id' => $supervisor->id,
    ]);

    expect($category)->toBeInstanceOf(Category::class)
        ->and($category->name)->toBe('Hardware Issues')
        ->and($category->description)->toBe('Issues related to hardware')
        ->and($category->color)->toBe('#ff0000')
        ->and($category->icon)->toBe('heroicon-o-computer-desktop')
        ->and($category->is_active)->toBeTrue()
        ->and($category->sort_order)->toBe(1);
});

test('category has many sub categories', function () {
    $category = Category::factory()->create();
    $subCategories = SubCategory::factory()->count(3)->create(['category_id' => $category->id]);

    expect($category->subCategories)->toHaveCount(3)
        ->and($category->subCategories->first())->toBeInstanceOf(SubCategory::class);
});

test('category has many tickets', function () {
    $category = Category::factory()->create();
    $tickets = Ticket::factory()->count(2)->create(['category_id' => $category->id]);

    expect($category->tickets)->toHaveCount(2)
        ->and($category->tickets->first())->toBeInstanceOf(Ticket::class);
});

test('category belongs to supervisor', function () {
    $supervisor = User::factory()->create();
    $category = Category::factory()->create(['category_supervisor_id' => $supervisor->id]);

    expect($category->supervisor)->toBeInstanceOf(User::class)
        ->and($category->supervisor->id)->toBe($supervisor->id);
});

test('category active scope returns only active categories', function () {
    $initialActiveCount = Category::active()->count();

    $createdActive = Category::factory()->create(['is_active' => true]);
    Category::factory()->create(['is_active' => false]);

    $activeCategories = Category::active()->get();

    expect($activeCategories)->toHaveCount($initialActiveCount + 1)
        ->and($activeCategories->contains('id', $createdActive->id))->toBeTrue()
        ->and($activeCategories->where('id', $createdActive->id)->first()->is_active)->toBeTrue();
});

test('category ordered scope sorts by sort order and name', function () {
    Category::factory()->create(['name' => 'Z Category', 'sort_order' => 1]);
    Category::factory()->create(['name' => 'A Category', 'sort_order' => 2]);

    $orderedCategories = Category::ordered()->get();

    expect($orderedCategories->first()->sort_order)->toBe(1)
        ->and($orderedCategories->last()->sort_order)->toBe(2);
});

test('category casts attributes correctly', function () {
    $category = Category::factory()->create([
        'is_active' => true,
        'sort_order' => 5,
    ]);

    expect($category->is_active)->toBeBool()
        ->and($category->sort_order)->toBeInt();
});
