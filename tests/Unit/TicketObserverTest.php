<?php

namespace Tests\Unit;

use App\Enums\Tickets\TicketStatus;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_closing_date_is_set_when_status_changes_to_closed(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::OPEN,
            'ticket_closing_date' => null,
        ]);

        $this->assertNull($ticket->ticket_closing_date);

        $ticket->update(['status' => TicketStatus::CLOSED]);

        $this->assertNotNull($ticket->fresh()->ticket_closing_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $ticket->fresh()->ticket_closing_date);
    }

    public function test_ticket_closing_date_is_not_set_when_status_changes_to_non_closed(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::OPEN,
            'ticket_closing_date' => null,
        ]);

        $ticket->update(['status' => TicketStatus::PENDING]);

        $this->assertNull($ticket->fresh()->ticket_closing_date);
    }

    public function test_ticket_closing_date_is_not_updated_when_other_fields_change(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::RESOLVED,
            'ticket_closing_date' => null,
        ]);

        $ticket->update(['subject' => 'Updated subject']);

        $this->assertNull($ticket->fresh()->ticket_closing_date);
    }
}
