<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Bitrix24CrmLeadService
{
    protected bool $verifySsl;

    public function __construct()
    {
        $this->verifySsl = (bool) config('services.bitrix24.verify_ssl', true);
    }

    public function isEnabled(): bool
    {
        if (! filter_var(env('BITRIX24_CRM_LEAD_ENABLED', true), FILTER_VALIDATE_BOOL)) {
            return false;
        }

        return rtrim($this->restUrl(), '/') !== '';
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function createOrderLead(
        string $name,
        string $phone,
        string $comment,
        array $items,
        float $totalAmount,
        string $currency = 'USD',
    ): ?int {
        if (! $this->isEnabled()) {
            return null;
        }

        $title = 'Заказ с сайта '.config('app.url', 'realbrick.kz').' — '.trim($name);
        $comments = $this->buildOrderComments($comment, $items, $totalAmount, $currency);

        return $this->createLead(
            title: $title,
            name: trim($name),
            phone: trim($phone),
            comments: $comments,
            opportunity: $totalAmount > 0 ? $totalAmount : null,
            currency: $currency,
            sourceDescription: 'Корзина на сайте',
        );
    }

    public function createFormLead(string $name, string $phone, ?string $comment = null): ?int
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $title = 'Заявка с сайта '.config('app.url', 'realbrick.kz').' — '.trim($name);

        return $this->createLead(
            title: $title,
            name: trim($name),
            phone: trim($phone),
            comments: trim((string) $comment),
            opportunity: null,
            currency: 'USD',
            sourceDescription: 'Форма на сайте',
        );
    }

    protected function createLead(
        string $title,
        string $name,
        string $phone,
        string $comments,
        ?float $opportunity,
        string $currency,
        string $sourceDescription,
    ): ?int {
        $baseUrl = rtrim($this->restUrl(), '/');
        if ($baseUrl === '' || $name === '' || $phone === '') {
            return null;
        }

        $fields = [
            'TITLE' => mb_substr($title, 0, 255),
            'NAME' => mb_substr($name, 0, 50),
            'STATUS_ID' => env('BITRIX24_CRM_LEAD_STATUS_ID', 'NEW'),
            'OPENED' => 'Y',
            'SOURCE_ID' => env('BITRIX24_CRM_LEAD_SOURCE_ID', 'WEB'),
            'SOURCE_DESCRIPTION' => mb_substr($sourceDescription, 0, 255),
            'COMMENTS' => mb_substr($comments, 0, 65000),
            'PHONE' => [
                ['VALUE' => $phone, 'VALUE_TYPE' => 'WORK'],
            ],
        ];

        $assignedById = (int) env('BITRIX24_CRM_LEAD_ASSIGNED_BY_ID', 0);
        if ($assignedById > 0) {
            $fields['ASSIGNED_BY_ID'] = $assignedById;
        }

        if ($opportunity !== null && $opportunity > 0) {
            $fields['OPPORTUNITY'] = round($opportunity, 2);
            $fields['CURRENCY_ID'] = mb_strtoupper($currency) ?: 'USD';
            $fields['IS_MANUAL_OPPORTUNITY'] = 'Y';
        }

        try {
            $response = Http::timeout(20)
                ->withOptions(['verify' => $this->verifySsl])
                ->asJson()
                ->post($baseUrl.'/crm.lead.add', [
                    'fields' => $fields,
                    'params' => [
                        'REGISTER_SONET_EVENT' => 'Y',
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('Bitrix24CrmLead: crm.lead.add HTTP failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $data = $response->json();
            if (isset($data['error'])) {
                Log::warning('Bitrix24CrmLead: crm.lead.add API error', [
                    'error' => $data['error'],
                    'error_description' => $data['error_description'] ?? null,
                ]);

                return null;
            }

            $leadId = (int) ($data['result'] ?? 0);

            return $leadId > 0 ? $leadId : null;
        } catch (\Throwable $e) {
            Log::error('Bitrix24CrmLead: crm.lead.add exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    protected function buildOrderComments(string $comment, array $items, float $totalAmount, string $currency): string
    {
        $lines = [];
        if (trim($comment) !== '') {
            $lines[] = trim($comment);
            $lines[] = '';
        }

        $lines[] = 'Товары из корзины:';
        foreach ($items as $item) {
            $name = trim((string) ($item['name'] ?? 'Товар'));
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $price = (float) ($item['price_value'] ?? 0);
            $bitrixId = trim((string) ($item['id'] ?? ''));
            $line = "- {$name} x {$qty}";
            if ($bitrixId !== '') {
                $line .= " (Bitrix ID: {$bitrixId})";
            }
            if ($price > 0) {
                $amount = $price * $qty;
                $line .= " — {$price} {$currency}, сумма {$amount} {$currency}";
            }
            $lines[] = $line;
        }

        if ($totalAmount > 0) {
            $lines[] = '';
            $lines[] = 'Итого: '.number_format($totalAmount, 2, '.', ' ')." {$currency}";
        }

        return implode("\n", $lines);
    }

    protected function restUrl(): string
    {
        $url = trim((string) config('services.bitrix24.rest_url', ''));
        if ($url !== '') {
            return $url;
        }

        return trim((string) env('DILLER_BITRIX24_REST_URL', ''));
    }
}
