<?php

namespace App\Support\Estimates;

use App\Enums\EstimateRateBasis;
use App\Models\Estimate;
use App\Models\EstimateLine;
use App\Support\Brand;
use RuntimeException;

final class CustomerEstimateDocument
{
    public const ITEM_COLUMNS = ['SN', 'Item / Description', 'Qty', 'Rate', 'Days', 'Amount'];

    /**
     * @return array<string, mixed>
     */
    public function viewData(Estimate $estimate): array
    {
        $estimate->loadMissing('lines');

        $logoPath = EstimateCompanySnapshot::logoAbsolutePath($estimate->issued_logo_path);
        $logoDataUri = $this->dataUri($logoPath);

        return [
            'estimate' => $estimate,
            'company_name' => Brand::name($estimate->issued_company_name),
            'tagline' => Brand::rewrite($estimate->issued_company_tagline),
            'phone' => $estimate->issued_phone,
            'email' => $estimate->issued_email,
            'address' => $estimate->issued_address,
            'website' => $estimate->issued_website,
            'logo_data_uri' => $logoDataUri,
            'logo_resolved' => $logoDataUri !== null,
            'columns' => self::ITEM_COLUMNS,
            'lines' => $estimate->lines->values()->map(function (EstimateLine $line, int $index): array {
                $oneTime = $line->rate_basis === EstimateRateBasis::OneTime;

                return [
                    'sn' => $index + 1,
                    'name' => $line->name,
                    'description' => $line->description,
                    'qty' => $this->formatNumber((string) $line->qty),
                    'rate' => $this->formatNumber((string) $line->rate),
                    'days' => $oneTime ? '—' : $this->formatNumber((string) $line->days),
                    'amount' => $this->formatNumber((string) $line->amount),
                ];
            })->all(),
        ];
    }

    public function html(Estimate $estimate): string
    {
        return view('estimates.customer-pdf', $this->viewData($estimate))->render();
    }

    public function assertCustomerPrivacy(string $html): void
    {
        $needles = [
            'actual cost',
            'actual_cost',
            'profit',
            'profit margin',
            'basic cost',
            'basic rate',
            'internal',
            'Estimate Status',
            'status',
        ];
        $lower = mb_strtolower($html);
        foreach (['actual cost', 'actual_cost', 'profit margin', 'basic cost', 'basic rate', 'estimate status'] as $forbidden) {
            if (str_contains($lower, $forbidden)) {
                throw new RuntimeException("Customer estimate HTML leaked internal field: {$forbidden}");
            }
        }
        unset($needles);
    }

    private function dataUri(?string $path): ?string
    {
        if ($path === null || ! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';
        $contents = file_get_contents($path);
        if ($contents === false) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    private function formatNumber(string $value): string
    {
        return number_format((float) EstimateMoney::of($value), 2, '.', ',');
    }
}
