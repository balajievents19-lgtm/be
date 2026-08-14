<?php

namespace App\Support;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use App\Models\ContactInquiry;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LeadsCsvExporter
{
    /**
     * @return list<string>
     */
    public static function headers(): array
    {
        return [
            'Name',
            'Mobile',
            'Email',
            'Subject',
            'Service',
            'Source',
            'Event Date',
            'Event Location',
            'Budget',
            'Status',
            'Priority',
            'Assigned To',
            'Follow-up At',
            'Message',
            'Admin Notes',
            'Received',
        ];
    }

    /**
     * @return list<string>
     */
    public static function row(ContactInquiry $lead): array
    {
        $status = $lead->status instanceof ContactInquiryStatus
            ? $lead->status->label()
            : (ContactInquiryStatus::tryFrom((string) $lead->status)?->label() ?? (string) ($lead->status ?? ''));

        $priority = $lead->priority instanceof ContactInquiryPriority
            ? $lead->priority->label()
            : (ContactInquiryPriority::tryFrom((string) $lead->priority)?->label() ?? (string) ($lead->priority ?? ''));

        return [
            (string) ($lead->name ?? ''),
            (string) ($lead->mobile ?? ''),
            (string) ($lead->email ?? ''),
            (string) ($lead->subject ?? ''),
            (string) ($lead->service_interested ?? ''),
            (string) ($lead->source ?? ''),
            $lead->event_date?->format('Y-m-d') ?? '',
            (string) ($lead->event_location ?? ''),
            (string) ($lead->budget ?? ''),
            $status,
            $priority,
            (string) ($lead->assignee?->name ?? ''),
            $lead->follow_up_at?->format('Y-m-d H:i') ?? '',
            (string) ($lead->message ?? ''),
            (string) ($lead->admin_notes ?? ''),
            $lead->created_at?->format('Y-m-d H:i') ?? '',
        ];
    }

    /**
     * @param  iterable<int, ContactInquiry>  $leads
     */
    public static function toCsv(iterable $leads): string
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, self::headers());

        foreach ($leads as $lead) {
            fputcsv($handle, self::row($lead));
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $csv;
    }

    /**
     * @param  iterable<int, ContactInquiry>|Collection<int, ContactInquiry>  $leads
     */
    public static function download(iterable $leads, ?string $filename = null): StreamedResponse
    {
        $filename ??= 'leads-'.now()->format('Y-m-d-His').'.csv';
        $rows = Collection::make($leads)->values();

        return response()->streamDownload(function () use ($rows): void {
            echo self::toCsv($rows);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
