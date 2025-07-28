<?php

namespace App\Models;

use App\Enums\MaterialRequests\MaterialRequestStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialRequest extends Model
{
    /** @use HasFactory<\Database\Factories\MaterialRequestFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'ticket_id',
        'created_by',
        'request_reason',
        'status',
        'processed_by',
        'user_prf_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => MaterialRequestStatus::class,
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaterialRequestItem::class);
    }
}
