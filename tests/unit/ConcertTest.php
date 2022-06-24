<?php

namespace Tests\Unit;

use App\Billing\PaymentFailedException;
use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function can_get_formatted_date()
    {
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('2016-12-01, 8:00 pm')
        ]);

        $this->assertEquals('December 1, 2016', $concert->formatted_date);
    }

    /** @test */
    public function can_get_formatted_start_time()
    {
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('2016-12-01, 17:00:00')
        ]);

        $this->assertEquals('5:00pm', $concert->formatted_start_time);
    }

    /** @test */
    public function can_get_ticket_price_in_dollars()
    {
        $concert = Concert::factory()->make([
            'ticket_price' => 6750
        ]);

        $this->assertEquals('67.50', $concert->ticket_price_in_dollars);
    }

    /** @test */
    public function concerts_with_a_published_at_date_are_published()
    {
        $publishedConcertA = Concert::factory()->create(['published_at' => Carbon::parse('-1 week')]);
        $publishedConcertB = Concert::factory()->create(['published_at' => Carbon::parse('-1 week')]);
        $unpublishedConcert = Concert::factory()->create(['published_at' => null]);

        /** @var Collection $publishedConcerts */
        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcert));
    }

    /** @test */
    public function can_order_concert_tickets()
    {
        $concert = Concert::factory()->create()->addTickets(100);

        $order = $concert->orderTickets('mary.jane@example.com', $concert->findTickets(3));

        $this->assertEquals('mary.jane@example.com', $order->email);
        $this->assertEquals(3, $order->ticketsQuantity());
    }

    /** @test */
    public function can_add_tickets()
    {
        $concert = Concert::factory()->create();

        $concert->addTickets(50);
        $concert->addTickets(50);

        $this->assertEquals(100, $concert->ticketsRemaining());
    }

    /** @test
     * @throws NotEnoughTicketsException
     */
    public function tickets_remaining_does_not_include_tickets_associated_with_an_concert_order()
    {
        $concert = Concert::factory()->create()->addTickets(50);
        $concert->orderTickets('mary.jane@example.com', $concert->findTickets(30));
        $this->assertEquals(20, $concert->ticketsRemaining());
    }

    /** @test */
    public function trying_to_purchase_more_tickets_than_remain_throws_an_exception()
    {
        $concert = Concert::factory()->create()->addTickets(10);

        try {
            $concert->orderTickets('mary.jane@example.com', $concert->findTickets(11));
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor('mary.jane@example.com'));
            $this->assertEquals(10, $concert->ticketsRemaining());
            return;
        }

        $this->fail('ordered more tickets than available did not thrown an exception.');
    }

    /** @test */
    public function cannot_order_tickets_that_have_already_been_purchased()
    {
        $concert = Concert::factory()->create()->addTickets(10);

        try{
            $concert->orderTickets('mary.jane@example.com', $concert->findTickets(8));
            $concert->orderTickets('oscar.plane@example.com', $concert->findTickets(3));
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor('oscar.plane@example.com'));
            $this->assertEquals(2, $concert->ticketsRemaining());
            return;
        }

        $this->fail('ordered more tickets than available did not thrown an exception.');
    }

    /** @test */
    public function can_reserve_available_tickets()
    {
        $concert = Concert::factory()->create()->addTickets(3);
        self::assertEquals(3, $concert->ticketsRemaining());

        $reservation = $concert->reserveTickets(2, 'test@example.com');

        self::assertCount(2, $reservation->tickets());
        self::assertEquals('test@example.com', $reservation->email());
        self::assertEquals(1, $concert->ticketsRemaining());
    }

    /** @test */
    public function cannot_reserve_tickets_that_have_already_been_purchase()
    {
        $concert = Concert::factory()->create()->addTickets(3);
        $concert->orderTickets('test@example.com', $concert->findTickets(2));

        try {
            $concert->reserveTickets(2, 'test@example.com');
        } catch (NotEnoughTicketsException $e) {
            self::assertEquals(1, $concert->ticketsRemaining());
            return;
        }

        self::fail("Reserving tickets succeeded even though the tickets were already sold.");
    }

    /** @test */
    public function cannot_reserve_tickets_that_have_already_been_reserved()
    {
        $concert = Concert::factory()->create()->addTickets(3);
        $concert->reserveTickets(2, 'test@example.com');

        try {
            $concert->reserveTickets(2, 'test@example.com');
        } catch (NotEnoughTicketsException $e) {
            self::assertEquals(1, $concert->ticketsRemaining());
            return;
        }

        self::fail("Reserving tickets succeeded even though the tickets were already sold.");
    }
}
