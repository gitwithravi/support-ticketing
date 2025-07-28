<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Breakage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'breakage_description',
        'responsible_reg_nos',
        'processed',
    ];

    protected function casts(): array
    {
        return [
            'processed' => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
