<?php

namespace App\Http\Resources\Api;

use App\Models\Customer;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Customer */
class CustomerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'phone' => PhoneNumber::localTen($this->phone),
            'phone_masked' => PhoneNumber::mask($this->phone),
            'avatar' => $this->avatar,
            'email_verified_at' => $this->email_verified_at,
            'email_verified' => $this->email_verified_at !== null,
            'last_login_at' => $this->last_login_at,
            'pending_email' => $this->pending_email,
            'needs_email_verification' => $this->email_verified_at === null,
        ];
    }
}
