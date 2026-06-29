<?php

namespace App\Services;

use App\Models\CustomerComplaint;
use App\Models\User;
use App\Repositories\ComplaintRepository;

class LegalService
{
    public function __construct(
        private readonly ComplaintRepository $complaints,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function clientConfig(): array
    {
        /** @var array<string, mixed> $business */
        $business = config('legal.business', []);

        return [
            'business' => [
                'name' => (string) ($business['name'] ?? config('app.name')),
                'ruc' => (string) ($business['ruc'] ?? ''),
                'address' => (string) ($business['address'] ?? ''),
                'email' => (string) ($business['email'] ?? ''),
                'phone' => $business['phone'] !== null && $business['phone'] !== ''
                    ? (string) $business['phone']
                    : null,
            ],
            'complaint_response_days' => (int) config('legal.complaint_response_days', 15),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitComplaint(array $data, ?User $user = null): CustomerComplaint
    {
        return $this->complaints->create([
            'complaint_number' => $this->complaints->nextComplaintNumber(),
            'user_id' => $user?->id,
            'consumer_name' => (string) $data['consumer_name'],
            'document_type' => (string) $data['document_type'],
            'document_number' => (string) $data['document_number'],
            'address' => (string) $data['address'],
            'email' => (string) $data['email'],
            'phone' => isset($data['phone']) && $data['phone'] !== ''
                ? (string) $data['phone']
                : null,
            'item_type' => (string) $data['item_type'],
            'amount' => isset($data['amount']) && $data['amount'] !== null && $data['amount'] !== ''
                ? $data['amount']
                : null,
            'complaint_type' => (string) $data['complaint_type'],
            'description' => (string) $data['description'],
            'order_reference' => isset($data['order_reference']) && $data['order_reference'] !== ''
                ? (string) $data['order_reference']
                : null,
            'status' => 'pending',
        ]);
    }
}
