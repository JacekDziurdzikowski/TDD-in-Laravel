<?php

namespace Tests\Feature;

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewConcertListingTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_view_a_published_concert_listing()
    {
        $concert = Concert::factory()->published()->create([
            'title' => 'The Paradox',
            'subtitle' => 'and the friends',
            'date' => Carbon::parse('December 13, 2016, 8:00 pm'),
            'ticket_price' => 3250,
            'venue' => 'MGT Arena',
            'venue_address' => 'Mgt. Street, 16th valley',
            'city' => 'LA',
            'state' => 'CA',
            'zip' => '19-300',
            'additional_information' => 'For tickets, call +48 (555) 555-5555.',
        ]);

        $response = $this->get('/concerts/'.$concert->id);

        $response->assertOk();
        $response->assertSee('The Paradox');
        $response->assertSee('and the friends');
        $response->assertSee('December 13, 2016');
        $response->assertSee('8:00pm');
        $response->assertSee('32.50');
        $response->assertSee('MGT Arena');
        $response->assertSee('Mgt. Street, 16th valley');
        $response->assertSee('LA');
        $response->assertSee('CA');
        $response->assertSee('19-300');
        $response->assertSee('For tickets, call +48 (555) 555-5555.');
    }


    /** @test */
    public function user_cannot_view_unpublished_concert()
    {
        $concert = Concert::factory()->unpublished()->create();

        $response = $this->get('/concerts/'.$concert->id);

        $response->assertNotFound();
    }


}
