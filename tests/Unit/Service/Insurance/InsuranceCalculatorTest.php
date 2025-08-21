<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Insurance;

use App\DTO\CalculateInsuranceRequest;
use App\Service\ExchangeRate\ExchangeRateProviderInterface;
use App\Service\Insurance\InsuranceCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InsuranceCalculatorTest extends TestCase
{
    private InsuranceCalculator $calculator;
    private ExchangeRateProviderInterface|MockObject $exchangeRateProvider;
    private array $dailyRates = [
        30000 => 0.6,
        50000 => 0.9,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a mock for the ExchangeRateProviderInterface
        $this->exchangeRateProvider = $this->createMock(ExchangeRateProviderInterface::class);
        
        // Create the calculator with test data
        $this->calculator = new InsuranceCalculator(
            $this->exchangeRateProvider,
            $this->dailyRates
        );
    }

    public function testCalculateWithValidData(): void
    {
        // Configure the mock to return a specific exchange rate
        $this->exchangeRateProvider->method('getExchangeRate')
            ->with('EUR')
            ->willReturn(80.0);
        
        $request = new CalculateInsuranceRequest(
            insuranceAmount: 30000,
            startDate: '2025-01-01',
            endDate: '2025-01-10',
            currency: 'EUR'
        );
        
        $response = $this->calculator->calculate($request);
        
        // Assert the response contains the expected values
        $this->assertEquals(6.0, $response->totalInCurrency); // 0.6 * 10 days = 6.0 EUR
        $this->assertEquals(480.0, $response->totalInRubles); // 6.0 * 80 = 480.0 RUB
        $this->assertEquals(10, $response->daysCount);
        $this->assertEquals(0.6, $response->dailyRate);
        $this->assertEquals(80.0, $response->exchangeRate);
        $this->assertEquals(30000, $response->insuranceAmount);
    }

    public function testCalculateWithSingleDay(): void
    {
        $this->exchangeRateProvider->method('getExchangeRate')
            ->with('USD')
            ->willReturn(75.5);
        
        $request = new CalculateInsuranceRequest(
            insuranceAmount: 50000,
            startDate: '2025-01-01',
            endDate: '2025-01-01',
            currency: 'USD'
        );
        
        $response = $this->calculator->calculate($request);

        $this->assertEquals(0.9, $response->totalInCurrency); // 0.9 * 1 day = 0.9 USD
        $this->assertEquals(67.95, $response->totalInRubles); // 0.9 * 75.5 = 67.95 RUB
        $this->assertEquals(1, $response->daysCount);
        $this->assertEquals(0.9, $response->dailyRate);
        $this->assertEquals(75.5, $response->exchangeRate);
        $this->assertEquals(50000, $response->insuranceAmount);
    }

    public function testCalculateWithUnsupportedInsuranceAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported insurance amount: 100000');
        
        $request = new CalculateInsuranceRequest(
            insuranceAmount: 100000,
            startDate: '2025-01-01',
            endDate: '2025-01-10',
            currency: 'EUR'
        );
        
        $this->calculator->calculate($request);
    }

    public function testCalculateWithInvalidDateRange(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('End date must be after start date');
        
        $request = new CalculateInsuranceRequest(
            insuranceAmount: 30000,
            startDate: '2025-01-10',
            endDate: '2025-01-01',
            currency: 'EUR'
        );
        
        $this->calculator->calculate($request);
    }

    public function testGetDailyRateWithValidAmount(): void
    {
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('getDailyRate');
        $method->setAccessible(true);
        
        $result = $method->invokeArgs($this->calculator, [30000]);
        $this->assertEquals(0.6, $result);
        
        $result = $method->invokeArgs($this->calculator, [50000]);
        $this->assertEquals(0.9, $result);
    }

    public function testGetDailyRateWithInvalidAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported insurance amount: 100000');
        
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('getDailyRate');
        $method->setAccessible(true);
        
        $method->invokeArgs($this->calculator, [100000]);
    }

    public function testCalculateTotal(): void
    {
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('calculateTotal');
        $method->setAccessible(true);
        
        $result = $method->invokeArgs($this->calculator, [0.6, 10]);
        $this->assertEquals(6.0, $result);
        
        $result = $method->invokeArgs($this->calculator, [0.9, 5]);
        $this->assertEquals(4.5, $result);
    }
}
