# Travel Insurance Calculation API

This is a Symfony-based API for calculating travel insurance costs for trips abroad.

## Requirements

- PHP 8.2 or higher
- Composer
- Symfony 7.3

## API Endpoint

### Calculate Insurance Cost

Calculate the cost of travel insurance based on the provided parameters.

**Endpoint:** `POST /api/insurance/calculate`

#### Request Body

```json
{
    "insuranceAmount": 30000,
    "startDate": "2025-10-01",
    "endDate": "2025-10-03",
    "currency": "EUR"
}
```

#### Parameters

| Parameter       | Type   | Required | Description                                      | Allowed Values         |
|-----------------|--------|----------|--------------------------------------------------|------------------------|
| insuranceAmount | int    | Yes      | Insurance amount in the specified currency       | 30000, 50000           |
| startDate      | string | Yes      | Start date of the trip (YYYY-MM-DD)              | Valid future date      |
| endDate        | string | Yes      | End date of the trip (YYYY-MM-DD)                | Must be after startDate|
| currency       | string | Yes      | Currency code                                    | EUR, USD               |

#### Response

```json
{
    "success": true,
    "data": {
        "totalInCurrency": 1.8,
        "totalInRubles": 144,
        "daysCount": 3,
        "dailyRate": 0.6,
        "exchangeRate": 80,
        "insuranceAmount": 30000
    }
}
```

#### Error Responses

**Invalid Input (400 Bad Request)**
```json
{
    "success": false,
    "errors": [
        "This value should be of type int.",
        "This value should not be blank."
    ]
}
```

**Unsupported Currency (400 Bad Request)**
```json
{
    "success": false,
    "error": "Unsupported currency: GBP"
}
```

**Unsupported Insurance Amount (400 Bad Request)**
```json
{
    "success": false,
    "error": "Unsupported insurance amount: 20000"
}
```

## Testing

Run the test suite with:

```bash
php bin/phpunit
```

## Implementation Details

- The calculation is based on the formula: `(Daily Rate) * (Number of Days)`
- Daily rates:
  - 30,000 - 0.6 per day
  - 50,000 - 0.8 per day
- Supported currencies and their exchange rates (fixed for now):
  - EUR: 80.0 RUB
  - USD: 70.0 RUB

## License

Proprietary
