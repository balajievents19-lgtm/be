<?php

namespace App\Http\Resources\Api;

use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TeamMember */
class TeamMemberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->role,
            'bio' => $this->bio,
            'photo' => $this->imageUrl($this->photo),
            'email' => $this->email,
            'phone' => $this->phone,
            'social_linkedin' => $this->social_linkedin,
            'social_instagram' => $this->social_instagram,
            'sort_order' => $this->sort_order,
        ];
    }
}
