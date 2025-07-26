<?php

use App\Models\Note;
use App\Models\Ticket;
use App\Traits\HasNotes;
use Illuminate\Database\Eloquent\Model;

test('has notes trait provides notes relationship', function () {
    $ticket = Ticket::factory()->create();
    
    expect(method_exists($ticket, 'notes'))->toBeTrue()
        ->and($ticket->notes())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class);
});

test('has notes trait can create notes', function () {
    $ticket = Ticket::factory()->create();
    
    $note = Note::factory()->create([
        'noteable_type' => Ticket::class,
        'noteable_id' => $ticket->id,
        'body' => 'Test note content',
    ]);
    
    expect($ticket->notes)->toHaveCount(1)
        ->and($ticket->notes->first()->body)->toBe('Test note content');
});

test('has notes trait can have multiple notes', function () {
    $ticket = Ticket::factory()->create();
    
    Note::factory()->count(3)->create([
        'noteable_type' => Ticket::class,
        'noteable_id' => $ticket->id,
    ]);
    
    expect($ticket->notes)->toHaveCount(3);
});

test('notes are polymorphic and work with different models', function () {
    $ticket = Ticket::factory()->create();
    $client = \App\Models\Client::factory()->create();
    
    Note::factory()->create([
        'noteable_type' => Ticket::class,
        'noteable_id' => $ticket->id,
        'body' => 'Ticket note',
    ]);
    
    Note::factory()->create([
        'noteable_type' => \App\Models\Client::class,
        'noteable_id' => $client->id,
        'body' => 'Client note',
    ]);
    
    expect($ticket->notes)->toHaveCount(1)
        ->and($client->notes)->toHaveCount(1)
        ->and($ticket->notes->first()->body)->toBe('Ticket note')
        ->and($client->notes->first()->body)->toBe('Client note');
});