<?php

use App\Models\Group;
use App\Models\User;

test('group has correct fillable attributes', function () {
    $fillable = [
        'name',
        'description',
        'category_id',
    ];

    expect((new Group)->getFillable())->toBe($fillable);
});

test('group can be created with valid attributes', function () {
    $group = Group::factory()->create([
        'name' => 'Support Team',
        'description' => 'Customer support department',
    ]);

    expect($group)->toBeInstanceOf(Group::class)
        ->and($group->name)->toBe('Support Team')
        ->and($group->description)->toBe('Customer support department');
});

test('group belongs to many users', function () {
    $group = Group::factory()->create();
    $users = User::factory()->count(3)->create();

    $group->users()->attach($users->pluck('id'));

    expect($group->users)->toHaveCount(3)
        ->and($group->users->first())->toBeInstanceOf(User::class);
});

test('group uses ulid for primary key', function () {
    $group = Group::factory()->create();

    expect($group->getKeyName())->toBe('id')
        ->and(strlen($group->id))->toBe(26);
});

test('group has timestamps', function () {
    $group = Group::factory()->create();

    expect($group->created_at)->not->toBeNull()
        ->and($group->updated_at)->not->toBeNull();
});
