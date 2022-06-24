<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Ticket;
use Database\Factories\TicketFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseMigrations;


    /** @test */
    public function converting_to_array()
    {
        $concert = Concert::factory()->create(['ticket_price' => 12000])->addTickets(5);
        $order = $concert->orderTickets('mary.jane@example.com', $concert->findTickets(5));

        $this->assertEquals([
            'email' => 'mary.jane@example.com',
            'quantity' => 5,
            'amount' => 60000
        ], $order->toArray());
    }

    /** @test  */
    public function creating_an_order_for_tickets_and_email()
    {
        $concert = Concert::factory()->create()->addTickets(10);
        self::assertEquals(10, $concert->ticketsRemaining());

        $order1 = Order::forTickets($concert->findTickets(2), 'test1@example.com', 2000);
        $order2 = Order::forTickets($concert->findTickets(4), 'test2@example.com', 4000);

        self::assertEquals(4, $concert->ticketsRemaining()); // to nie jest testowanie  zamówienia
        self::assertEquals('test1@example.com', $order1->email);
        self::assertEquals('test2@example.com', $order2->email);
        self::assertEquals(2000, $order1->amount);
        self::assertEquals(4000, $order2->amount);
        self::assertEquals(2, $order1->ticketsQuantity());
        self::assertEquals(4, $order2->ticketsQuantity());
        self::assertEquals(2, Order::all()->count());
    }

}
