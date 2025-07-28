<?php

use App\Enums\Tickets\TicketPriority;

test('ticket priority enum has correct values', function () {
    expect(TicketPriority::LOW->value)->toBe('low')
        ->and(TicketPriority::NORMAL->value)->toBe('normal')
        ->and(TicketPriority::HIGH->value)->toBe('high')
        ->and(TicketPriority::URGENT->value)->toBe('urgent');
});

test('ticket priority enum returns correct colors', function () {
    expect(TicketPriority::LOW->getColor())->toBe('success')
        ->and(TicketPriority::NORMAL->getColor())->toBe('info')
        ->and(TicketPriority::HIGH->getColor())->toBe('warning')
        ->and(TicketPriority::URGENT->getColor())->toBe('danger');
});

test('ticket priority enum returns correct labels', function () {
    expect(TicketPriority::LOW->getLabel())->toBe('Low')
        ->and(TicketPriority::NORMAL->getLabel())->toBe('Normal')
        ->and(TicketPriority::HIGH->getLabel())->toBe('High')
        ->and(TicketPriority::URGENT->getLabel())->toBe('Urgent');
});

test('ticket priority enum can be created from string', function () {
    expect(TicketPriority::from('low'))->toBe(TicketPriority::LOW)
        ->and(TicketPriority::from('normal'))->toBe(TicketPriority::NORMAL)
        ->and(TicketPriority::from('high'))->toBe(TicketPriority::HIGH)
        ->and(TicketPriority::from('urgent'))->toBe(TicketPriority::URGENT);
});

test('ticket priority enum has all expected cases', function () {
    $cases = TicketPriority::cases();

    expect($cases)->toHaveCount(4)
        ->and($cases)->toContain(TicketPriority::LOW)
        ->and($cases)->toContain(TicketPriority::NORMAL)
        ->and($cases)->toContain(TicketPriority::HIGH)
        ->and($cases)->toContain(TicketPriority::URGENT);
});
