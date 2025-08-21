<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\ExchangeRate;

use App\Service\ExchangeRate\FixedExchangeRateProvider;
use PHPUnit\Framework\TestCase;

class FixedExchangeRateProviderTest extends TestCase
{
    private FixedExchangeRateProvider $provider;
    private array $testRates = [
        'USD' => 75.5,
        'EUR' => 80.0,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new FixedExchangeRateProvider($this->testRates);
    }

    public function testGetExchangeRateWithValidCurrency(): void
    {
        $this->assertEquals(75.5, $this->provider->getExchangeRate('USD'));
        $this->assertEquals(80.0, $this->provider->getExchangeRate('EUR'));
    }

    public function testGetExchangeRateWithInvalidCurrency(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported currency: GBP');
        
        $this->provider->getExchangeRate('GBP');
    }

    public function testGetExchangeRateWithEmptyRates(): void
    {
        $emptyProvider = new FixedExchangeRateProvider([]);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported currency: USD');
        
        $emptyProvider->getExchangeRate('USD');
    }

    public function testConstructorWithEmptyRates(): void
    {
        $provider = new FixedExchangeRateProvider([]);
        $this->assertInstanceOf(FixedExchangeRateProvider::class, $provider);
    }
}
