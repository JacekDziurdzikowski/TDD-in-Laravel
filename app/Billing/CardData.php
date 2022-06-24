<?php

namespace App\Billing;

class CardData
{

    private string $cardNumber;
    private string $cvc;
    private int $expMonth;
    private int $expYear;

    public function __construct()
    {
    }


    public function setCardNumber(string $number)
    {
        $this->cardNumber = $number;
    }

    public function setCvc(string $cvc)
    {
        $this->cvc = $cvc;
    }

    public function setExpMonth(int $month)
    {
        $this->expMonth = $month;
    }

    public function setExpYear(int $year)
    {
        $this->expYear = $year;
    }

    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function getCvc(): string
    {
        return $this->cvc;
    }

    public function getExpMonth(): int
    {
        return $this->expMonth;
    }

    public function getExpYear(): int
    {
        return $this->expYear;
    }



}
