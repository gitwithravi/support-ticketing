<?php

use App\Models\Client;
use App\Models\Group;
use App\Models\Ticket;

test('client has correct fillable attributes', function () {
    $fillable = [
        'name',
        'unique_id',
        'email',
        'password',
        'phone',
        'locale',
        'timezone',
        'is_active',
    ];

    expect((new Client())->getFillable())->toBe($fillable);
});

test('client has correct hidden attributes', function () {
    $hidden = [
        'password',
        'remember_token',
    ];

    expect((new Client())->getHidden())->toBe($hidden);
});

test('client can be created with valid attributes', function () {
    $client = Client::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'unique_id' => 'EMP001',
        'phone' => '+1234567890',
        'is_active' => true,
    ]);

    expect($client)->toBeInstanceOf(Client::class)
        ->and($client->name)->toBe('John Doe')
        ->and($client->email)->toBe('john@example.com')
        ->and($client->unique_id)->toBe('EMP001')
        ->and($client->phone)->toBe('+1234567890')
        ->and($client->is_active)->toBeTrue();
});

test('client has many tickets', function () {
    $client = Client::factory()->create();
    $tickets = Ticket::factory()->count(3)->create(['requester_id' => $client->id]);

    expect($client->tickets)->toHaveCount(3)
        ->and($client->tickets->first())->toBeInstanceOf(Ticket::class);
});

test('client belongs to many groups', function () {
    $client = Client::factory()->create();
    $groups = Group::factory()->count(2)->create();
    
    $client->groups()->attach($groups->pluck('id'));

    expect($client->groups)->toHaveCount(2)
        ->and($client->groups->first())->toBeInstanceOf(Group::class);
});

test('client can authenticate', function () {
    $client = Client::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);

    expect($client)->toBeInstanceOf(\Illuminate\Foundation\Auth\User::class);
});

test('client password is hidden in serialization', function () {
    $client = Client::factory()->create(['password' => bcrypt('secret')]);
    $array = $client->toArray();

    expect($array)->not->toHaveKey('password')
        ->and($array)->not->toHaveKey('remember_token');
});

test('client uses ulid for primary key', function () {
    $client = Client::factory()->create();

    expect($client->getKeyName())->toBe('id')
        ->and(strlen($client->id))->toBe(26);
});

test('client has avatar attribute', function () {
    $client = Client::factory()->create(['email' => 'test@example.com']);

    expect($client->avatar)->toBeString();
});