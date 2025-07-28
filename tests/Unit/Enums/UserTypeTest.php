<?php

use App\Enums\Users\UserType;

test('user type enum has correct values', function () {
    expect(UserType::ADMIN->value)->toBe('admin')
        ->and(UserType::AGENT->value)->toBe('agent')
        ->and(UserType::CATEGORY_SUPERVISOR->value)->toBe('category_supervisor')
        ->and(UserType::BUILDING_SUPERVISOR->value)->toBe('building_supervisor');
});

test('user type enum returns correct labels', function () {
    expect(UserType::ADMIN->getLabel())->toBe('Admin')
        ->and(UserType::AGENT->getLabel())->toBe('Agent')
        ->and(UserType::CATEGORY_SUPERVISOR->getLabel())->toBe('Category Supervisor')
        ->and(UserType::BUILDING_SUPERVISOR->getLabel())->toBe('Building Supervisor');
});

test('user type enum returns correct colors', function () {
    expect(UserType::ADMIN->getColor())->toBe('danger')
        ->and(UserType::AGENT->getColor())->toBe('primary')
        ->and(UserType::CATEGORY_SUPERVISOR->getColor())->toBe('warning')
        ->and(UserType::BUILDING_SUPERVISOR->getColor())->toBe('success');
});

test('user type enum returns correct icons', function () {
    expect(UserType::ADMIN->getIcon())->toBe('heroicon-o-shield-check')
        ->and(UserType::AGENT->getIcon())->toBe('heroicon-o-user')
        ->and(UserType::CATEGORY_SUPERVISOR->getIcon())->toBe('heroicon-o-folder-open')
        ->and(UserType::BUILDING_SUPERVISOR->getIcon())->toBe('heroicon-o-building-office');
});

test('user type enum returns correct descriptions', function () {
    expect(UserType::ADMIN->getDescription())->toBe('Full system access and user management')
        ->and(UserType::AGENT->getDescription())->toBe('Handle tickets and customer support')
        ->and(UserType::CATEGORY_SUPERVISOR->getDescription())->toBe('Supervise specific categories and their tickets')
        ->and(UserType::BUILDING_SUPERVISOR->getDescription())->toBe('Manage building-specific maintenance issues');
});

test('user type enum options method returns correct array', function () {
    $options = UserType::options();

    expect($options)->toBeArray()
        ->and($options)->toHaveKey('admin', 'Admin')
        ->and($options)->toHaveKey('agent', 'Agent')
        ->and($options)->toHaveKey('category_supervisor', 'Category Supervisor')
        ->and($options)->toHaveKey('building_supervisor', 'Building Supervisor');
});

test('user type is admin method works correctly', function () {
    expect(UserType::ADMIN->isAdmin())->toBeTrue()
        ->and(UserType::AGENT->isAdmin())->toBeFalse()
        ->and(UserType::CATEGORY_SUPERVISOR->isAdmin())->toBeFalse()
        ->and(UserType::BUILDING_SUPERVISOR->isAdmin())->toBeFalse();
});

test('user type can supervise method works correctly', function () {
    expect(UserType::ADMIN->canSupervise())->toBeTrue()
        ->and(UserType::CATEGORY_SUPERVISOR->canSupervise())->toBeTrue()
        ->and(UserType::BUILDING_SUPERVISOR->canSupervise())->toBeTrue()
        ->and(UserType::AGENT->canSupervise())->toBeFalse();
});

test('user type handles tickets method works correctly', function () {
    expect(UserType::AGENT->handlesTickets())->toBeTrue()
        ->and(UserType::CATEGORY_SUPERVISOR->handlesTickets())->toBeTrue()
        ->and(UserType::BUILDING_SUPERVISOR->handlesTickets())->toBeTrue()
        ->and(UserType::ADMIN->handlesTickets())->toBeFalse();
});

test('user type enum can be created from string', function () {
    expect(UserType::from('admin'))->toBe(UserType::ADMIN)
        ->and(UserType::from('agent'))->toBe(UserType::AGENT)
        ->and(UserType::from('category_supervisor'))->toBe(UserType::CATEGORY_SUPERVISOR)
        ->and(UserType::from('building_supervisor'))->toBe(UserType::BUILDING_SUPERVISOR);
});
