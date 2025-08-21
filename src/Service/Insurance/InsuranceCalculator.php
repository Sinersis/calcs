<?php

declare(strict_types=1);

namespace App\Service\Insurance;

use App\DTO\CalculateInsuranceRequest;
use App\DTO\CalculateInsuranceResponse;
use App\Service\ExchangeRate\ExchangeRateProviderInterface;

class InsuranceCalculator implements InsuranceCalculatorInterface
{
    /** @var array<int, float> */
    private array $dailyRates;

    /**
     * @param array<int, float> $dailyRates
     */
    public function __construct(
        private readonly ExchangeRateProviderInterface $exchangeRateProvider,
        array $dailyRates
    ) {
        $this->dailyRates = $dailyRates;
    }

    public function calculate(CalculateInsuranceRequest $request): CalculateInsuranceResponse
    {
        $startDate = new \DateTimeImmutable($request->startDate);
        $endDate = new \DateTimeImmutable($request->endDate);
        
        if ($endDate < $startDate) {
            throw new \InvalidArgumentException('End date must be after start date');
        }
        
        $daysCount = $endDate->diff($startDate)->days + 1;

        $dailyRate = $this->getDailyRate($request->insuranceAmount);
        $totalInCurrency = $this->calculateTotal($dailyRate, $daysCount);
        
        $exchangeRate = $this->exchangeRateProvider->getExchangeRate($request->currency);
        $totalInRubles = $totalInCurrency * $exchangeRate;

        return new CalculateInsuranceResponse(
            totalInCurrency: round($totalInCurrency, 2),
            totalInRubles: round($totalInRubles, 2),
            daysCount: $daysCount,
            dailyRate: $dailyRate,
            exchangeRate: $exchangeRate,
            insuranceAmount: $request->insuranceAmount
        );
    }

    private function getDailyRate(int $insuranceAmount): float
    {
        if (!isset($this->dailyRates[$insuranceAmount])) {
            throw new \InvalidArgumentException(sprintf('Unsupported insurance amount: %d', $insuranceAmount));
        }

        return $this->dailyRates[$insuranceAmount];
    }

    private function calculateTotal(float $dailyRate, int $days): float
    {
        return $dailyRate * $days;
    }
}
