<?php

namespace App\Billing;

interface PaymentGateway
{
    public function charge(int $amount, string $token): void;

    public function totalCharges(): int;

    public function getValidToken(): string;

    public function getTokenFromCard(CardData $cardData): string;
}
