<?php

declare(strict_types=1);

namespace App\Service\ExchangeRate;

class FixedExchangeRateProvider implements ExchangeRateProviderInterface
{
    /** @var array<string, float> */
    private array $rates;

    /**
     * @param array<string, float> $rates
     */
    public function __construct(array $rates)
    {
        $this->rates = $rates;
    }

    public function getExchangeRate(string $currency): float
    {
        if (!isset($this->rates[$currency])) {
            throw new \InvalidArgumentException(sprintf('Unsupported currency: %s', $currency));
        }

        return $this->rates[$currency];
    }
}
