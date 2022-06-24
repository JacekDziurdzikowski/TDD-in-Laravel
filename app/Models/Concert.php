<?php declare(strict_types=1);

namespace App\Models;

use App\Billing\PaymentFailedException;
use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $dates = ['date'];


    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute(): string
    {
        return $this->date->format('g:ia');
    }

    public function getTicketPriceInDollarsAttribute(): string
    {
        return number_format($this->ticket_price / 100, 2);
    }

    public function orderTickets(string $email, Collection $tickets): Order
    {
        return Order::forTickets($tickets, $email, $tickets->sum('price'));
    }

    public function reserveTickets(int $quantity, string $email): Reservation
    {
        $tickets = $this->findTickets($quantity)->each(function(Ticket $ticket) {
            $ticket->reserve();
        });

        return new Reservation($tickets, $email);
    }

    public function findTickets($quantity): Collection
    {
        $tickets = $this->tickets()->available()->take($quantity)->get();

        if ($tickets->count() < $quantity) {
            throw new NotEnoughTicketsException("available tickets is: {$tickets->count()}, order quantity is: {$quantity}");
        }

        return $tickets;
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'tickets');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function addTickets($quantity): self
    {
        foreach(range(1, $quantity) as $i) {
            $this->tickets()->create();
        }
        return $this;
    }

    public function ticketsRemaining(): int
    {
        return $this->tickets()->available()->count();
    }

    public function hasOrderFor(string $email): bool
    {
        return $this->orders()->where('email', $email)->count() > 0;
    }

    public function ordersFor(string $email): Collection
    {
        return $this->orders()->where('email', $email)->get();
    }
}
