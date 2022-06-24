<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Ticket extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function concert(): BelongsTo
    {
        return $this->belongsTo(Concert::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereNull('order_id')->whereNull('reserved_at');
    }

    public function reserve()
    {
        $this->update(['reserved_at' => Carbon::now()]);
    }

    public function release()
    {
        $this->update(['reserved_at' => null]);
    }

    public function getPriceAttribute()
    {
        return $this->concert->ticket_price;
    }
}
