<?php declare(strict_types=1);

namespace App\Billing;

use Closure;
use Illuminate\Support\Collection;

class FakePaymentGateway implements PaymentGateway
{
    private Collection $charges;

    private ?Closure $callbackBeforeFirstCharge;

    public function __construct()
    {
        $this->charges = collect();
    }

    public function getValidToken(): string
    {
        return 'valid-token';
    }

    public function charge(int $amount, string $token): void
    {
        if (!empty($this->callbackBeforeFirstCharge)) {
            $callback = $this->callbackBeforeFirstCharge;
            $this->callbackBeforeFirstCharge = null;
            $callback->__invoke($this);
        }

        if ($token !== $this->getValidToken()) {
            throw new PaymentFailedException();
        }
        $this->charges[] = $amount;
    }

    public function totalCharges(): int
    {
        return $this->charges->sum();
    }

    public function beforeFirstCharge(callable $callback)
    {
        $this->callbackBeforeFirstCharge = $callback;
    }

    public function getTokenFromCard(CardData $cardData): string
    {
        return $this->getValidToken();
    }
}
