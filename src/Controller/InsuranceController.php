<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CalculateInsuranceRequest;
use App\Service\Insurance\InsuranceCalculatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
#[Route('/api')]
class InsuranceController extends AbstractController
{
    public function __construct(
        private readonly InsuranceCalculatorInterface $insuranceCalculator,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[Route('/insurance/calculate', name: 'api_insurance_calculate', methods: ['POST'])]
    public function calculate(Request $request): JsonResponse
    {
        try {
            // Log the raw request
            $requestContent = $request->getContent();
            
            if (empty($requestContent)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Request body is empty',
                ], 400);
            }
            
            try {
                $data = json_decode($requestContent, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                return $this->json([
                    'success' => false,
                    'error' => 'Invalid JSON format: ' . $e->getMessage(),
                    'request' => $requestContent,
                ], 400);
            }
            
            // Log the parsed data
            
            // Validate required fields
            $requiredFields = ['insuranceAmount', 'startDate', 'endDate', 'currency'];
            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Missing required fields: ' . implode(', ', $missingFields),
                ], 400);
            }
            
            try {
                $calculateRequest = new CalculateInsuranceRequest(
                    insuranceAmount: (int)$data['insuranceAmount'],
                    startDate: (string)$data['startDate'],
                    endDate: (string)$data['endDate'],
                    currency: (string)$data['currency']
                );
            } catch (\Throwable $e) {
                return $this->json([
                    'success' => false,
                    'error' => 'Invalid request data: ' . $e->getMessage(),
                ], 400);
            }

            $errors = $this->validator->validate($calculateRequest);
            
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                
                return $this->json([
                    'success' => false,
                    'errors' => $errorMessages,
                ], 400);
            }

            try {
                $result = $this->insuranceCalculator->calculate($calculateRequest);

                return $this->json([
                    'success' => true,
                    'data' => $result->toArray(),
                ]);
            } catch (\Exception $e) {
                return $this->json([
                    'success' => false,
                    'error' => 'Calculation error: ' . $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ], 500);
            }
            
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => 'Unexpected error: ' . $e->getMessage(),
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}
