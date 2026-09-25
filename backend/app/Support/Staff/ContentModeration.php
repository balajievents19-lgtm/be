<?php

namespace App\Support\Staff;

use App\Enums\ContentModerationStatus;
use App\Models\User;
use App\Notifications\MediaApprovalRequired;
use App\Support\Rbac\AdminModules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

final class ContentModeration
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function prepareStaffCreate(User $user, array $data): array
    {
        $data['created_by'] = $user->id;
        $data['updated_by'] = $user->id;
        $data['status'] = false;
        $needsBrandReview = BrandReviewScanner::mightContainExternalBranding($data);
        $data['brand_review_required'] = $needsBrandReview;
        $data['moderation_status'] = $needsBrandReview
            ? ContentModerationStatus::BrandReview->value
            : ContentModerationStatus::PendingReview->value;
        $data['reviewed_by'] = null;
        $data['reviewed_at'] = null;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function prepareStaffUpdate(User $user, Model $record, array $data): array
    {
        $data['updated_by'] = $user->id;
        unset($data['created_by'], $data['status'], $data['moderation_status'], $data['reviewed_by'], $data['reviewed_at']);
        $data['status'] = false;
        $merged = array_merge($record->only(['title', 'description', 'caption', 'alt_text', 'image', 'thumbnail', 'url', 'video_url']), $data);
        $needsBrandReview = BrandReviewScanner::mightContainExternalBranding($merged);
        $data['brand_review_required'] = $needsBrandReview;
        $data['moderation_status'] = $needsBrandReview
            ? ContentModerationStatus::BrandReview->value
            : ContentModerationStatus::PendingReview->value;

        return $data;
    }

    public static function approve(User $reviewer, Model $record): Model
    {
        if (! StaffContentAccess::canApprove($reviewer)) {
            abort(403, 'Only Super Admin can approve content.');
        }

        $record->fill([
            'moderation_status' => ContentModerationStatus::Published->value,
            'status' => true,
            'brand_review_required' => false,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'updated_by' => $reviewer->id,
        ]);
        $record->save();

        return $record;
    }

    public static function reject(User $reviewer, Model $record, ?string $notes = null): Model
    {
        if (! StaffContentAccess::canApprove($reviewer)) {
            abort(403, 'Only Super Admin can reject content.');
        }

        $record->fill([
            'moderation_status' => ContentModerationStatus::Rejected->value,
            'status' => false,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'updated_by' => $reviewer->id,
            'moderation_notes' => $notes,
        ]);
        $record->save();

        return $record;
    }

    public static function notifySuperAdmins(Model $record, User $uploader, bool $brandReview): void
    {
        $admins = User::role(AdminModules::ROLE_SUPER_ADMIN)->get();
        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new MediaApprovalRequired($record, $uploader, $brandReview));
    }
}
