<?php

declare(strict_types=1);

namespace App\DTO;

class CalculateInsuranceResponse
{
    public function __construct(
        public readonly float $totalInCurrency,
        public readonly float $totalInRubles,
        public readonly int $daysCount,
        public readonly float $dailyRate,
        public readonly float $exchangeRate,
        public readonly int $insuranceAmount
    ) {
    }

    public function toArray(): array
    {
        return [
            'totalInCurrency' => $this->totalInCurrency,
            'totalInRubles' => $this->totalInRubles,
            'daysCount' => $this->daysCount,
            'dailyRate' => $this->dailyRate,
            'exchangeRate' => $this->exchangeRate,
            'insuranceAmount' => $this->insuranceAmount,
        ];
    }
}
