<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Billing\CardData;
use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Reservation;

class ConcertOrdersController extends Controller
{
    private PaymentGateway $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concertId)
    {
        $concert = Concert::published()->findOrFail($concertId);

        $this->validate(request(), [
            'email' => ['required', 'email'],
            'quantity' => ['required', 'integer', 'min:1'],
            'payment_token' => ['required_without:card_no,card_cvc,card_exp_month,card_exp_year'],
            'card_no' => ['required_without:payment_token'],
            'card_cvc' => ['required_without:payment_token'],
            'card_exp_month' => ['required_without:payment_token'],
            'card_exp_year' => ['required_without:payment_token'],
        ]);

        $cardData = $this->getCardDataIfAvailable();
        $paymentToken = $cardData ? $this->paymentGateway->getTokenFromCard($cardData) : request('payment_token');

        try {
            $reservation = $concert->reserveTickets((int) request('quantity'), request('email'));
            $order = $reservation->complete($this->paymentGateway, $paymentToken);
            return response()->json($order->toArray(), 201);
        }
        catch (PaymentFailedException $e) {
            $reservation->cancel();
            return response()->json([], 422);

        } catch (NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }

    }

    private function getCardDataIfAvailable(): ?CardData
    {
        if (request('card_no') &&
            request('card_cvc') &&
            request('card_exp_month') &&
            request('card_exp_year'))
        {
            $cardData = new CardData();
            $cardData->setCardNumber(request('card_no'));
            $cardData->setCvc(request('card_cvc'));
            $cardData->setExpMonth((int) request('card_exp_month'));
            $cardData->setExpYear((int) request('card_exp_year'));

            return $cardData;
        }

        return null;
    }
}
