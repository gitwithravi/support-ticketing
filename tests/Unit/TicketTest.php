<?php

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketType;
use App\Models\Building;
use App\Models\Category;
use App\Models\Client;
use App\Models\Group;
use App\Models\SubCategory;
use App\Models\Ticket;
use App\Models\User;

test('ticket has correct fillable attributes', function () {
    $fillable = [
        'requester_id',
        'assignee_id',
        'group_id',
        'category_id',
        'sub_category_id',
        'building_id',
        'duplicate_of_ticket_id',
        'subject',
        'priority',
        'type',
        'status',
        'is_escalated',
        'room_no',
        'ticket_description',
        'user_status',
        'cat_supervisor_status',
        'build_supervisor_status',
        'verified_by',
        'ticket_closing_date',
    ];

    expect((new Ticket())->getFillable())->toBe($fillable);
});

test('ticket can be created with valid attributes', function () {
    $client = Client::factory()->create();
    $group = Group::factory()->create();
    $category = Category::factory()->create();

    $ticket = Ticket::factory()->create([
        'requester_id' => $client->id,
        'group_id' => $group->id,
        'category_id' => $category->id,
        'subject' => 'Test Ticket',
        'priority' => TicketPriority::NORMAL,
        'type' => TicketType::INCIDENT,
        'status' => TicketStatus::OPEN,
        'ticket_description' => 'Test description',
    ]);

    expect($ticket)->toBeInstanceOf(Ticket::class)
        ->and($ticket->subject)->toBe('Test Ticket')
        ->and($ticket->priority)->toBe(TicketPriority::NORMAL)
        ->and($ticket->type)->toBe(TicketType::INCIDENT)
        ->and($ticket->status)->toBe(TicketStatus::OPEN);
});

test('ticket belongs to requester', function () {
    $client = Client::factory()->create();
    $ticket = Ticket::factory()->create(['requester_id' => $client->id]);

    expect($ticket->requester)->toBeInstanceOf(Client::class)
        ->and($ticket->requester->id)->toBe($client->id);
});

test('ticket belongs to assignee', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create(['assignee_id' => $user->id]);

    expect($ticket->assignee)->toBeInstanceOf(User::class)
        ->and($ticket->assignee->id)->toBe($user->id);
});

test('ticket belongs to group', function () {
    $group = Group::factory()->create();
    $ticket = Ticket::factory()->create(['group_id' => $group->id]);

    expect($ticket->group)->toBeInstanceOf(Group::class)
        ->and($ticket->group->id)->toBe($group->id);
});

test('ticket belongs to category', function () {
    $category = Category::factory()->create();
    $ticket = Ticket::factory()->create(['category_id' => $category->id]);

    expect($ticket->category)->toBeInstanceOf(Category::class)
        ->and($ticket->category->id)->toBe($category->id);
});

test('ticket belongs to sub category', function () {
    $subCategory = SubCategory::factory()->create();
    $ticket = Ticket::factory()->create(['sub_category_id' => $subCategory->id]);

    expect($ticket->subCategory)->toBeInstanceOf(SubCategory::class)
        ->and($ticket->subCategory->id)->toBe($subCategory->id);
});

test('ticket belongs to building', function () {
    $building = Building::factory()->create();
    $ticket = Ticket::factory()->create(['building_id' => $building->id]);

    expect($ticket->building)->toBeInstanceOf(Building::class)
        ->and($ticket->building->id)->toBe($building->id);
});

test('ticket can be a duplicate of another ticket', function () {
    $originalTicket = Ticket::factory()->create();
    $duplicateTicket = Ticket::factory()->create(['duplicate_of_ticket_id' => $originalTicket->id]);

    expect($duplicateTicket->duplicateOf)->toBeInstanceOf(Ticket::class)
        ->and($duplicateTicket->duplicateOf->id)->toBe($originalTicket->id);
});

test('ticket can be verified by user', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create(['verified_by' => $user->id]);

    expect($ticket->verifiedBy)->toBeInstanceOf(User::class)
        ->and($ticket->verifiedBy->id)->toBe($user->id);
});

test('ticket solved scope returns resolved and closed tickets', function () {
    Ticket::factory()->create(['status' => TicketStatus::RESOLVED]);
    Ticket::factory()->create(['status' => TicketStatus::CLOSED]);
    Ticket::factory()->create(['status' => TicketStatus::OPEN]);
    Ticket::factory()->create(['status' => TicketStatus::PENDING]);

    $solvedTickets = Ticket::solved()->get();

    expect($solvedTickets)->toHaveCount(2);
});

test('ticket unsolved scope returns open, pending, and on hold tickets', function () {
    Ticket::factory()->create(['status' => TicketStatus::OPEN]);
    Ticket::factory()->create(['status' => TicketStatus::PENDING]);
    Ticket::factory()->create(['status' => TicketStatus::ON_HOLD]);
    Ticket::factory()->create(['status' => TicketStatus::RESOLVED]);
    Ticket::factory()->create(['status' => TicketStatus::CLOSED]);

    $unsolvedTickets = Ticket::unsolved()->get();

    expect($unsolvedTickets)->toHaveCount(3);
});

test('ticket casts enums correctly', function () {
    $ticket = Ticket::factory()->create([
        'priority' => TicketPriority::HIGH,
        'type' => TicketType::INCIDENT,
        'status' => TicketStatus::OPEN,
        'is_escalated' => true,
    ]);

    expect($ticket->priority)->toBeInstanceOf(TicketPriority::class)
        ->and($ticket->type)->toBeInstanceOf(TicketType::class)
        ->and($ticket->status)->toBeInstanceOf(TicketStatus::class)
        ->and($ticket->is_escalated)->toBeBool();
});