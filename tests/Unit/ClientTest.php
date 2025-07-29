<?php

use App\Models\Client;
use App\Models\Group;
use App\Models\Ticket;

test('client has correct fillable attributes', function () {
    $fillable = [
        'name',
        'unique_id',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'locale',
        'timezone',
        'is_active',
        'otp_code',
        'otp_expires_at',
    ];

    expect((new Client)->getFillable())->toBe($fillable);
});

test('client has correct hidden attributes', function () {
    $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    expect((new Client)->getHidden())->toBe($hidden);
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
        ->and($array)->not->toHaveKey('remember_token')
        ->and($array)->not->toHaveKey('otp_code');
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

test('client can generate otp', function () {
    $client = Client::factory()->create();
    
    $otp = $client->generateOtp();
    
    expect($otp)->toHaveLength(6)
        ->and($otp)->toMatch('/^\d{6}$/')
        ->and($client->fresh()->otp_code)->toBe($otp)
        ->and($client->fresh()->otp_expires_at)->not->toBeNull();
});

test('client can verify valid otp', function () {
    $client = Client::factory()->create();
    $otp = $client->generateOtp();
    
    expect($client->verifyOtp($otp))->toBeTrue();
});

test('client cannot verify invalid otp', function () {
    $client = Client::factory()->create();
    $client->generateOtp();
    
    expect($client->verifyOtp('123456'))->toBeFalse();
});

test('client cannot verify expired otp', function () {
    $client = Client::factory()->create();
    $client->generateOtp();
    
    // Manually set expiry to past
    $client->update(['otp_expires_at' => now()->subMinutes(20)]);
    
    expect($client->verifyOtp($client->otp_code))->toBeFalse();
});

test('client can mark email as verified', function () {
    $client = Client::factory()->create(['is_active' => false]);
    $client->generateOtp();
    
    $client->markEmailAsVerified();
    
    $client = $client->fresh();
    
    expect($client->email_verified_at)->not->toBeNull()
        ->and($client->otp_code)->toBeNull()
        ->and($client->otp_expires_at)->toBeNull()
        ->and($client->is_active)->toBeTrue();
});

test('client has verified email returns correct status', function () {
    $unverifiedClient = Client::factory()->create(['email_verified_at' => null]);
    $verifiedClient = Client::factory()->create(['email_verified_at' => now()]);
    
    expect($unverifiedClient->hasVerifiedEmail())->toBeFalse()
        ->and($verifiedClient->hasVerifiedEmail())->toBeTrue();
});
