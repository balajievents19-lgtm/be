<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class MediaApprovalRequired extends Notification
{
    use Queueable;

    public function __construct(
        public Model $record,
        public User $uploader,
        public bool $brandReview = false,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = (string) ($this->record->getAttribute('title') ?: 'Untitled');
        $area = (string) ($this->record->getAttribute('gallery_category_id') ?: $this->record->getAttribute('service_id') ?: class_basename($this->record));

        return [
            'title' => $this->brandReview ? 'Brand Review Required' : 'Media Approval Required',
            'body' => sprintf(
                '%s uploaded new media%s.',
                $this->uploader->name ?: $this->uploader->email,
                $title !== '' ? ': '.$title : ''
            ),
            'record_type' => $this->record::class,
            'record_id' => $this->record->getKey(),
            'uploaded_by' => $this->uploader->id,
            'brand_review' => $this->brandReview,
            'area' => $area,
        ];
    }
}
