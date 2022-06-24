<?php

namespace Database\Factories;

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConcertFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Concert::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => 'The Paradox',
            'subtitle' => 'and the friends',
            'date' => Carbon::parse('+2 weeks'),
            'ticket_price' => 3250,
            //'total_tickets' => 200,
            'venue' => 'MGT Arena',
            'venue_address' => 'Mgt. Street, 16th valley',
            'city' => 'LA',
            'state' => 'CA',
            'zip' => '19-300',
            'additional_information' => 'For tickets, call +48 (555) 555-5555.',
        ];
    }

    public function published(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => Carbon::parse('-1 week'),
            ];
        });
    }

    public function unpublished(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => null,
            ];
        });
    }
}
