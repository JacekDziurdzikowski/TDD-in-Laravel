<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use function PHPUnit\Framework\assertNull;

class TicketTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function ticket_can_be_reserved()
    {
        $ticket = Ticket::factory()->create();
        self::assertNull($ticket->reserved_at);

        $ticket->reserve();

        self::assertNotNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    public function ticket_can_be_released()
    {
        /** @var Ticket $ticket */
        $ticket = Ticket::factory()->reserved()->create();
        self::assertNotNull($ticket->reserved_at);

        $ticket->release();

        self::assertNull($ticket->fresh()->reserved_at);
    }
}
