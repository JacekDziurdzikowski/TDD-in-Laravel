<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketsQuantity(): int
    {
        return $this->tickets()->count();
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'quantity' => $this->ticketsQuantity(),
            'amount' => $this->amount
        ];
    }

    static function forTickets($tickets, $email, $amount): self
    {
        $order = self::create([
            'email' => $email,
            'amount' => $amount
        ]);

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

}
