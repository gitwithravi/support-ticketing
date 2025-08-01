<?php

namespace App\Services;

use App\Models\User;
use App\Settings\PrfApiSettings;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrfApiService
{
    private string $baseUrl;

    private string $accessKey;

    private string $accessSecret;

    public function __construct(User $user)
    {
        $settings = app(PrfApiSettings::class);

        $this->baseUrl = $settings->api_endpoint;

        $credentials = $user->getPrfApiCredentials();
        $this->accessKey = $credentials['access_key'] ?? '';
        $this->accessSecret = $credentials['access_secret'] ?? '';

        $this->validateConfiguration($user);
    }

    /**
     * Create a new PrfApiService instance for the given user
     */
    public static function forUser(User $user): self
    {
        return new self($user);
    }

    private function validateConfiguration(User $user): void
    {
        if (empty($this->baseUrl)) {
            throw new \InvalidArgumentException('PRF API endpoint is not configured in settings');
        }

        if (empty($this->accessKey)) {
            throw new \InvalidArgumentException("PRF API access key is not configured for user {$user->name} (ID: {$user->id})");
        }

        if (empty($this->accessSecret)) {
            throw new \InvalidArgumentException("PRF API access secret is not configured for user {$user->name} (ID: {$user->id})");
        }
    }

    public function createUserPurchaseRequest(array $data): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Access-Key' => $this->accessKey,
                'X-Access-Secret' => $this->accessSecret,
            ])->post($this->baseUrl.'/user-purchase-requests', $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('PRF API request failed', [
                'status' => $response->status(),
                'response' => $response->body(),
                'request_data' => $data,
            ]);

            return [
                'success' => false,
                'error' => $this->parseErrorResponse($response),
            ];
        } catch (\Exception $e) {
            Log::error('PRF API request exception', [
                'message' => $e->getMessage(),
                'request_data' => $data,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to connect to PRF API: '.$e->getMessage(),
            ];
        }
    }

    private function parseErrorResponse(Response $response): string
    {
        $statusCode = $response->status();
        $responseData = $response->json();

        return match ($statusCode) {
            401 => 'Authentication failed: '.($responseData['message'] ?? 'Invalid credentials'),
            404 => 'Employee record not found: '.($responseData['message'] ?? 'No employee record found'),
            422 => 'Validation failed: '.$this->formatValidationErrors($responseData),
            500 => 'Server error: '.($responseData['message'] ?? 'Internal server error'),
            default => 'API request failed with status '.$statusCode.': '.($responseData['message'] ?? 'Unknown error'),
        };
    }

    private function formatValidationErrors(array $responseData): string
    {
        if (! isset($responseData['errors']) || ! is_array($responseData['errors'])) {
            return $responseData['message'] ?? 'Validation failed';
        }

        $errors = [];
        foreach ($responseData['errors'] as $field => $messages) {
            if (is_array($messages)) {
                $errors[] = $field.': '.implode(', ', $messages);
            } else {
                $errors[] = $field.': '.$messages;
            }
        }

        return implode('; ', $errors);
    }

    public function transformMaterialRequestsToPrfPayload(
        Collection $materialRequests,
        string $purpose,
        string $contactNo,
        string $requestedDeliveryDate
    ): array {
        $items = [];
        $ticketNumbers = [];

        foreach ($materialRequests as $materialRequest) {
            if ($materialRequest->ticket) {
                $ticketNumbers[] = $materialRequest->ticket->ticket_id;
            }

            foreach ($materialRequest->items as $item) {
                $items[] = [
                    'product_details' => $item->item_name,
                    'quantity' => $item->quantity,
                    'uom' => $item->uom,
                ];
            }
        }

        $remarks = 'PRF Request for Ticket Numbers: '.implode(', ', array_unique($ticketNumbers));

        return [
            'purpose' => $purpose,
            'contact_no' => $contactNo,
            'requested_delivery_date' => $requestedDeliveryDate,
            'remarks' => $remarks,
            'items' => $items,
        ];
    }
}
