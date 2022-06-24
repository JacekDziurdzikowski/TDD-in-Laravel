<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    /** @test */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = new FakePaymentGateway;

        $paymentGateway->charge(2500, $paymentGateway->getValidToken());
        $paymentGateway->charge(2500, $paymentGateway->getValidToken());

        $this->assertEquals(5000, $paymentGateway->totalCharges());
    }

    /** @test */
    public function charges_with_an_invalid_token_fails()
    {
        try{
            $paymentGateway = new FakePaymentGateway;
            $paymentGateway->charge(2500, 'not-valid-payment-token');
        } catch (PaymentFailedException $e) {
            $this->assertTrue(true);
            return;
        }
        $this->fail();
    }

    /** @test */
    public function running_a_hook_before_the_first_charge()
    {
        $fakeGateway = new FakePaymentGateway();
        $timesCallbackRan = 0;

        $fakeGateway->beforeFirstCharge(function (PaymentGateway $fakeGateway) use (&$timesCallbackRan) {
            $timesCallbackRan++;
            $fakeGateway->charge(1000, $fakeGateway->getValidToken());
            self::assertEquals(1000, $fakeGateway->totalCharges());
        });

        $fakeGateway->charge(1000, $fakeGateway->getValidToken());
        self::assertEquals(2000, $fakeGateway->totalCharges());
        self::assertEquals(1, $timesCallbackRan);
    }

}
