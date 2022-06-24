<?php

namespace Tests\Unit\Billing;

use App\Billing\CardData;
use App\Billing\PaymentFailedException;
use App\Billing\StripePaymentGateway;
use Stripe\Charge;
use Stripe\StripeClient;
use Tests\TestCase;

/**
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{

    private StripeClient $client;

    private StripePaymentGateway $gateway;

    private Charge $lastCharge;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new StripeClient(env('STRIPE_SECRET'));
        $this->gateway = new StripePaymentGateway($this->client);
        $this->lastCharge = $this->lastCharge();
    }

    private function lastCharge(): Charge
    {
        return $this->client->charges->all([
            'limit' => 1,
        ])['data'][0];
    }

    private function newCharges(): array
    {
        return $this->client->charges->all([
            'limit' => 1,
            'ending_before' => $this->lastCharge->id
        ])['data'];
    }

    private function validToken(): string
    {
        return $this->client->tokens
            ->create([
                'card' => [
                    'number' => '4242424242424242',
                    'exp_month' => 1,
                    'exp_year' => date('Y') + 1,
                    'cvc' => '123'
                ]
            ])->id;
    }



    /** @test */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        // act - make a charge
        $this->gateway->charge(2500, $this->validToken());

        // assert - verify the charge was correct
        $this->assertCount(1, $this->newCharges());
        $this->assertEquals(2500, $this->lastCharge()->amount);
    }

    /** @test */
    public function charges_with_an_invalid_token_fails()
    {
        try{
            $this->gateway->charge(2500, 'not-valid-payment-token');
        } catch (PaymentFailedException $e) {
            $this->assertCount(0, $this->newCharges());
            return;
        }
        $this->fail('Charging with invalid payment token did not throw a PaymentFailedException.');
    }

    /** @test */
    public function can_create_token_from_card_data()
    {
        // arrange
        $cardData = new CardData();
        $cardData->setCardNumber('4242424242424242');
        $cardData->setCvc('123');
        $cardData->setExpMonth(1);
        $cardData->setExpYear(date('Y') + 1);

        // act
        $tokenId = $this->gateway->getTokenFromCard($cardData);

        // assert
        $this->assertNotEmpty($this->client->tokens->retrieve($tokenId, []));
    }
}
