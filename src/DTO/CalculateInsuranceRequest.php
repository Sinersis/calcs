<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CalculateInsuranceRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('int')]
        #[Assert\Choice(choices: [30000, 50000], message: 'Insurance amount must be either 30000 or 50000')]
        public readonly int $insuranceAmount,

        #[Assert\NotBlank]
        #[Assert\DateTime(format: 'Y-m-d')]
        public readonly string $startDate,

        #[Assert\NotBlank]
        #[Assert\DateTime(format: 'Y-m-d')]
        #[Assert\GreaterThan(propertyPath: 'startDate')]
        public readonly string $endDate,

        #[Assert\NotBlank]
        #[Assert\Length(exactly: 3)]
        #[Assert\Choice(choices: ['EUR', 'USD'], message: 'Currency must be either EUR or USD')]
        public readonly string $currency
    ) {
    }
}
