<?php

namespace App\Http\Resources\Api;

use App\Models\Statistic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Statistic */
class StatisticResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'value' => $this->value,
            'icon' => $this->icon,
            'suffix' => $this->suffix,
            'sort_order' => $this->sort_order,
            'show_on_homepage' => $this->show_on_homepage,
        ];
    }
}
