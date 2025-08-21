<?php

declare(strict_types=1);

namespace App\Service\Insurance;

use App\DTO\CalculateInsuranceRequest;
use App\DTO\CalculateInsuranceResponse;

interface InsuranceCalculatorInterface
{
    public function calculate(CalculateInsuranceRequest $request): CalculateInsuranceResponse;
}
