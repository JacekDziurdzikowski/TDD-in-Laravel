<?php declare(strict_types=1);

namespace Tests\feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Models\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PurchaseTicketTest extends TestCase
{
    use DatabaseMigrations;

    protected FakePaymentGateway $paymentGateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    private function orderTickets($concert, $params): TestResponse
    {
        $savedRequest = $this->app['request'];
        $response = $this->postJson("concerts/{$concert->id}/orders", $params);
        $this->app['request'] = $savedRequest;

        return $response;
    }

    private function assertValidationError($response, $field)
    {
        $response->assertStatus(422);
        $this->assertArrayHasKey($field, $response->decodeResponseJson()['errors']);
    }

    /** @test  */
    public function customer_can_purchase_published_concert_tickets_when_provided_payment_token()
    {
        $this->disableExceptionHandling();
        $concert = Concert::factory()->published()->create(['ticket_price' => 3250])->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'jacek.placek@example.com',
            'quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidToken()
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'email' => 'jacek.placek@example.com',
            'quantity' => 3,
            'amount' => 3250 * 3,
        ]);
        $this->assertEquals(3250 * 3, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('jacek.placek@example.com'));
        $this->assertEquals(3, $concert->ordersFor('jacek.placek@example.com')->first()->ticketsQuantity());
    }

    /** @test  */
    public function customer_can_purchase_published_concert_tickets_when_provided_card_details()
    {
        $this->disableExceptionHandling();
        $concert = Concert::factory()->published()->create(['ticket_price' => 3250])->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'jacek.placek@example.com',
            'quantity' => 3,
            'card_no' => '111111111111',
            'card_cvc' => '111',
            'card_exp_month' => 1,
            'card_exp_year' => 2000,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'email' => 'jacek.placek@example.com',
            'quantity' => 3,
            'amount' => 3250 * 3,
        ]);
        $this->assertEquals(3250 * 3, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('jacek.placek@example.com'));
        $this->assertEquals(3, $concert->ordersFor('jacek.placek@example.com')->first()->ticketsQuantity());
    }

    /** @test */
    public function cannot_purchase_tickets_another_user_is_already_trying_to_purchase()
    {
        $this->disableExceptionHandling();
        $concert = Concert::factory()->published()->create(['ticket_price' => 1000])->addTickets(3);

        $this->paymentGateway->beforeFirstCharge(function($paymentGateway) use ($concert) {

            $res = $this->orderTickets($concert, [
                'email' => 'personB@example.com',
                'quantity' => 2,
                'payment_token' => $this->paymentGateway->getValidToken()
            ]);

            $res->assertStatus(422);
            $this->assertEquals(0, $this->paymentGateway->totalCharges());
            $this->assertFalse($concert->hasOrderFor('personB@example.com'));
        });

        $this->orderTickets($concert, [
            'email' => 'personA@example.com',
            'quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidToken()
        ]);

        $this->assertEquals(1000 * 3, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('personA@example.com'));
        $this->assertEquals(3, $concert->ordersFor('personA@example.com')->first()->ticketsQuantity());
    }

    /** @test */
    public function cannot_purchase_unpublished_concert_tickets()
    {
        $concert = Concert::factory()->unpublished()->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'jacek.placek@example.com',
            'quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidToken()
        ]);

        $response->assertStatus(404);
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertFalse($concert->hasOrderFor('jacek.placek@example.com'));
    }

    /** @test */
    public function email_is_required_to_purchase_tickets()
    {
        $concert = Concert::factory()->published()->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidToken()
        ]);

        $this->assertValidationError($response, 'email');
    }

    /** @test  */
    public function valid_email_is_required_to_purchase_ticket()
    {
        $concert = Concert::factory()->published()->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'this-is-no-a-valid-email-adress',
            'quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidToken()
        ]);

        $this->assertValidationError($response, 'email');
    }

    /** @test */
    public function ticket_quantity_is_required_to_purchase_ticket()
    {
        $concert = Concert::factory()->published()->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'test_email@example.com',
            'payment_token' => $this->paymentGateway->getValidToken()
        ]);

        $this->assertValidationError($response, 'quantity');
    }

    /** @test */
    public function ticket_quantity_must_be_greater_than_0_to_purchase_ticket()
    {
        $concert = Concert::factory()->published()->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'test_email@example.com',
            'payment_token' => $this->paymentGateway->getValidToken()
        ]);

        $this->assertValidationError($response, 'quantity');
    }

    /** @test */
    public function card_details_or_payment_token_is_required_to_purchase_ticket()
    {
        $concert = Concert::factory()->published()->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'test_email@example.com',
            'quantity' => 3,
        ]);

        $this->assertValidationError($response, 'payment_token');
        $this->assertValidationError($response, 'card_no');
        $this->assertValidationError($response, 'card_cvc');
        $this->assertValidationError($response, 'card_exp_month');
        $this->assertValidationError($response, 'card_exp_year');
    }

    /** @test */
    public function an_order_is_not_created_if_payment_fails()
    {
        $this->disableExceptionHandling();
        $concert = Concert::factory()->published()->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'jacek.placek@example.com',
            'quantity' => 3,
            'payment_token' => 'not-valid-payment-token'
        ]);

        $response->assertStatus(422);
        self::assertFalse($concert->hasOrderFor('jacek.placek@example.com'));
        self::assertEquals(3, $concert->ticketsRemaining());

    }

    /** @test */
    public function cannot_purchase_more_tickets_than_remain()
    {
        // arrange
        $concert = Concert::factory()->published()->create()->addTickets(50);

        // act
        $response = $this->orderTickets($concert, [
            'email' => 'jacek.placek@example.com',
            'quantity' => 51,
            'payment_token' => $this->paymentGateway->getValidToken()
        ]);

        $response->assertStatus(422);
        $this->assertEquals(50, $concert->ticketsRemaining());
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertFalse($concert->hasOrderFor('jacek.placek@example.com'));
    }
}
