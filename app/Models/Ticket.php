<?php

namespace App\Models;

use App\Enums\Tickets\MaintenanceTerm;
use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketStatus;
use App\Enums\Tickets\TicketType;
use App\Enums\Tickets\TicketUserStatus;
use App\Models\Scopes\CategoryScope;
use App\Models\Scopes\ClientScope;
use App\Models\Scopes\GroupScope;
use App\Observers\TicketObserver;
use App\Traits\HasNotes;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([TicketObserver::class])]
#[ScopedBy([CategoryScope::class, GroupScope::class, ClientScope::class])]
class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory, HasNotes, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
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
        'maintenance_term',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'priority' => TicketPriority::class,
            'type' => TicketType::class,
            'status' => TicketStatus::class,
            'is_escalated' => 'boolean',
            'user_status' => TicketUserStatus::class,
            'cat_supervisor_status' => TicketUserStatus::class,
            'build_supervisor_status' => TicketUserStatus::class,
            'ticket_closing_date' => 'datetime',
            'maintenance_term' => MaintenanceTerm::class,
        ];
    }

    /**
     * The client that the ticket belongs to.
     *
     * @return BelongsTo
     */
    public function requester()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * The user that the ticket is assigned to.
     *
     * @return BelongsTo
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * The group that the ticket is assigned to.
     *
     * @return BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * The category that the ticket belongs to.
     *
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The sub-category that the ticket belongs to.
     *
     * @return BelongsTo
     */
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    /**
     * The building that the ticket is related to.
     *
     * @return BelongsTo
     */
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * A ticket has many comments.
     *
     * @return HasMany
     */
    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }

    /**
     * A ticket has many SLAs.
     *
     * @return HasMany
     */
    public function slas()
    {
        return $this->hasMany(TicketSla::class);
    }

    /**
     * A ticket has many fields.
     *
     * @return HasMany
     */
    public function fields()
    {
        return $this->hasMany(TicketField::class);
    }

    /**
     * A ticket has many ticket activity.
     */
    public function activity(): HasMany
    {
        return $this->hasMany(TicketActivity::class);
    }

    /**
     * Get the main ticket that this ticket is a duplicate of.
     *
     * This defines a relationship where the current ticket was marked as a duplicate
     * of another ticket (the main/original one). Useful for tracing merged tickets.
     *
     * @return BelongsTo
     */
    public function duplicateOf()
    {
        return $this->belongsTo(Ticket::class, 'duplicate_of_ticket_id', 'id');
    }

    /**
     * The user who verified/closed the ticket.
     *
     * @return BelongsTo
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the support email address including the ticket ID.
     *
     * This method appends the ticket ID to the default support email address
     * in the format: support+{ticket_id}@example.com.
     */
    public function getSupportEmailWithTicketId(): ?string
    {
        if (! $this->ticket_id || ! config('mail.from.address')) {
            return config('mail.from.address');
        }

        return preg_replace('/^(.*)@/', "support+{$this->ticket_id}@", config('mail.from.address'));
    }

    /**
     * Limit the query to solved tickets.
     */
    public function scopeSolved(Builder $query): void
    {
        $query->whereIn('status', [TicketStatus::RESOLVED->value, TicketStatus::CLOSED->value]);
    }

    /**
     * Limit the query to unsolved tickets.
     */
    public function scopeUnsolved(Builder $query): void
    {
        $query->whereIn('status', [TicketStatus::OPEN->value, TicketStatus::PENDING->value, TicketStatus::ON_HOLD->value]);
    }
}
