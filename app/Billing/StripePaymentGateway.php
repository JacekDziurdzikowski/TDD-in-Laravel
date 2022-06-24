<?php declare(strict_types=1);

namespace App\Billing;


use Stripe\Exception\InvalidRequestException;
use Stripe\StripeClient;
use Stripe\Token;

class StripePaymentGateway implements PaymentGateway
{
    private StripeClient $client;


    public function __construct(StripeClient $client)
    {
        $this->client = $client;
    }

    public function totalCharges(): int
    {

    }

    public function getValidToken(): string
    {

    }

    public function charge(int $amount, string $token): void
    {
        try {
            $this->client->charges->create([
                'amount' => $amount,
                'currency' => 'pln',
                'source' => $token
            ]);
        } catch (InvalidRequestException $e) {
            throw new PaymentFailedException();
        }

    }

    public function getTokenFromCard(CardData $cardData): string
    {
        return $this->client->tokens
            ->create([
                'card' => [
                    'number' => $cardData->getCardNumber(),
                    'exp_month' => $cardData->getExpMonth(),
                    'exp_year' => $cardData->getExpYear(),
                    'cvc' => $cardData->getCvc()
                ]
            ])->id;
    }
}
