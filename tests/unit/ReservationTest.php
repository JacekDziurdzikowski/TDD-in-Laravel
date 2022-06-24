<?php

namespace Tests\Unit;

use App\Billing\FakePaymentGateway;
use App\Models\Concert;
use App\Models\Reservation;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery\Mock;
use Mockery\MockInterface;
use Tests\TestCase;

class ReservationTest extends TestCase
{

    use DatabaseMigrations;


    /** @test  */
    public function calculating_total_cost()
    {
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets,'test@example.com');

        self::assertEquals(3600, $reservation->totalCost());
    }

    /** @test */
    public function reserved_tickets_are_released_when_reservation_is_cancelled()
    {
        $tickets = collect([
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
        ]);
        $reservation = new Reservation($tickets,'test@example.com');

        $reservation->cancel();

        foreach ($tickets as $ticket) {
            /** @var $ticket MockInterface */
            $ticket->shouldHaveReceived('release');
        }
    }

    /** @test */
    public function can_get_tickets_from_reservation()
    {
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets, 'test@example.com');

        self::assertEquals($tickets, $reservation->tickets());
    }

    /** @test */
    public function can_get_email_from_reservation()
    {
        $reservation = new Reservation(collect(), 'test@example.com');

        self::assertEquals('test@example.com', $reservation->email());
    }

    /** @test */
    public function completing_a_reservation()
    {
        $concert = Concert::factory()->create(['ticket_price' => 1200]);
        $tickets = Ticket::factory()->count(3)->create(['concert_id' => $concert->id]);
        $reservation = new Reservation($tickets, 'test@example.com');
        $paymentGateway = new FakePaymentGateway();

        $order = $reservation->complete($paymentGateway, $paymentGateway->getValidToken());

        self::assertEquals('test@example.com', $order->email);
        self::assertEquals($tickets->count(), $order->ticketsQuantity());
        self::assertEquals(3600, $order->amount);
        self::assertEquals(3600, $paymentGateway->totalCharges());
    }
}
