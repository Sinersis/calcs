<?php

declare(strict_types=1);

namespace App\Service\ExchangeRate;

interface ExchangeRateProviderInterface
{
    public function getExchangeRate(string $currency): float;
}
