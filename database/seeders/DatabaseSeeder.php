<?php

namespace Database\Seeders;

use App\Models\Concert;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Concert::factory()->published()->create()->addTickets(10);
    }
}
