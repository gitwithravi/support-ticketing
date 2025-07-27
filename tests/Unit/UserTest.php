<?php

use App\Enums\Users\UserType;
use App\Models\Group;
use App\Models\User;

test('user has correct fillable attributes', function () {
    $fillable = [
        'name',
        'email',
        'user_type',
        'password',
        'is_active',
    ];

    expect((new User())->getFillable())->toBe($fillable);
});

test('user has correct hidden attributes', function () {
    $hidden = [
        'password',
        'remember_token',
    ];

    expect((new User())->getHidden())->toBe($hidden);
});

test('user can be created with valid attributes', function () {
    $user = User::factory()->create([
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'user_type' => UserType::AGENT,
        'is_active' => true,
    ]);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Jane Smith')
        ->and($user->email)->toBe('jane@example.com')
        ->and($user->user_type)->toBe(UserType::AGENT)
        ->and($user->is_active)->toBeTrue();
});

test('user belongs to many groups', function () {
    $user = User::factory()->create();
    $groups = Group::factory()->count(2)->create();
    
    $user->groups()->attach($groups->pluck('id'));

    expect($user->groups)->toHaveCount(2)
        ->and($user->groups->first())->toBeInstanceOf(Group::class);
});

test('user can check if is admin', function () {
    $adminUser = User::factory()->create(['user_type' => UserType::ADMIN]);
    $agentUser = User::factory()->create(['user_type' => UserType::AGENT]);

    expect($adminUser->isAdmin())->toBeTrue()
        ->and($agentUser->isAdmin())->toBeFalse();
});

test('user can check if can supervise', function () {
    $categorySupervisor = User::factory()->create(['user_type' => UserType::CATEGORY_SUPERVISOR]);
    $buildingSupervisor = User::factory()->create(['user_type' => UserType::BUILDING_SUPERVISOR]);
    $agent = User::factory()->create(['user_type' => UserType::AGENT]);

    expect($categorySupervisor->canSupervise())->toBeTrue()
        ->and($buildingSupervisor->canSupervise())->toBeTrue()
        ->and($agent->canSupervise())->toBeFalse();
});

test('user can check if handles tickets', function () {
    $agent = User::factory()->create(['user_type' => UserType::AGENT]);
    $admin = User::factory()->create(['user_type' => UserType::ADMIN]);

    expect($agent->handlesTickets())->toBeTrue()
        ->and($admin->handlesTickets())->toBeFalse();
});

test('user scope of type filters correctly', function () {
    $initialAgentCount = User::ofType(UserType::AGENT)->count();
    $initialAdminCount = User::ofType(UserType::ADMIN)->count();
    
    User::factory()->create(['user_type' => UserType::ADMIN]);
    User::factory()->create(['user_type' => UserType::AGENT]);
    User::factory()->create(['user_type' => UserType::CATEGORY_SUPERVISOR]);

    $agents = User::ofType(UserType::AGENT)->get();
    $admins = User::ofType(UserType::ADMIN)->get();

    expect($agents)->toHaveCount($initialAgentCount + 1)
        ->and($admins)->toHaveCount($initialAdminCount + 1);
});

test('user scope admins returns only admin users', function () {
    User::factory()->create(['user_type' => UserType::ADMIN]);
    User::factory()->create(['user_type' => UserType::AGENT]);
    User::factory()->create(['user_type' => UserType::CATEGORY_SUPERVISOR]);

    $admins = User::admins()->get();

    expect($admins)->toHaveCount(1)
        ->and($admins->first()->user_type)->toBe(UserType::ADMIN);
});

test('user scope agents returns only agent users', function () {
    $initialAgentCount = User::agents()->count();
    
    User::factory()->create(['user_type' => UserType::ADMIN]);
    $createdAgent = User::factory()->create(['user_type' => UserType::AGENT]);
    User::factory()->create(['user_type' => UserType::CATEGORY_SUPERVISOR]);

    $agents = User::agents()->get();

    expect($agents)->toHaveCount($initialAgentCount + 1)
        ->and($agents->contains('id', $createdAgent->id))->toBeTrue()
        ->and($agents->where('id', $createdAgent->id)->first()->user_type)->toBe(UserType::AGENT);
});

test('user scope supervisors returns supervisor users', function () {
    $initialSupervisorCount = User::supervisors()->count();
    
    User::factory()->create(['user_type' => UserType::ADMIN]);
    User::factory()->create(['user_type' => UserType::AGENT]);
    User::factory()->create(['user_type' => UserType::CATEGORY_SUPERVISOR]);
    User::factory()->create(['user_type' => UserType::BUILDING_SUPERVISOR]);

    $supervisors = User::supervisors()->get();

    expect($supervisors)->toHaveCount($initialSupervisorCount + 2);
});

test('user casts user type correctly', function () {
    $user = User::factory()->create(['user_type' => UserType::AGENT]);

    expect($user->user_type)->toBeInstanceOf(UserType::class)
        ->and($user->user_type)->toBe(UserType::AGENT);
});

test('user password is hashed automatically', function () {
    $user = User::factory()->create(['password' => 'plaintext']);

    expect($user->password)->not->toBe('plaintext')
        ->and(password_verify('plaintext', $user->password))->toBeTrue();
});

test('user can check if is category supervisor', function () {
    $categorySupervisor = User::factory()->create(['user_type' => UserType::CATEGORY_SUPERVISOR]);
    $agent = User::factory()->create(['user_type' => UserType::AGENT]);

    expect($categorySupervisor->isCategorySupervisor())->toBeTrue()
        ->and($agent->isCategorySupervisor())->toBeFalse();
});

test('user has supervised categories relationship', function () {
    $supervisor = User::factory()->create(['user_type' => UserType::CATEGORY_SUPERVISOR]);
    
    expect($supervisor->supervisedCategories())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});