<?php declare(strict_types=1);

namespace App\Models;

use App\Billing\PaymentGateway;
use Illuminate\Support\Collection;

class Reservation
{
    private Collection $tickets;

    private string $email;

    public function __construct(Collection $tickets, string $email)
    {
        $this->tickets = $tickets;
        $this->email = $email;
    }

    public function totalCost()
    {
        return $this->tickets->sum('price');
    }

    public function tickets(): Collection
    {
        return $this->tickets;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function cancel()
    {
        foreach ($this->tickets as $ticket) {
            $ticket->release();
        }
    }

    public function complete(PaymentGateway $paymentGateway, string $paymentToken): Order
    {
        $paymentGateway->charge($this->totalCost(), $paymentToken);
        return Order::forTickets($this->tickets(), $this->email(), $this->totalCost());
    }
}
