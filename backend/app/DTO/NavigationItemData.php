<?php

namespace App\DTO;

use App\Models\NavigationItem;

class NavigationItemData
{
    public function __construct(
        public readonly int $id,
        public readonly string $label,
        public readonly string $url,
        public readonly string $target,
        public readonly ?string $icon,
        public readonly ?string $image,
        public readonly ?int $parentId,
        public readonly int $sortOrder,
        public readonly bool $showOnHeader,
        public readonly bool $showOnFooter,
    ) {}

    public static function fromModel(NavigationItem $item): self
    {
        return new self(
            id: $item->id,
            label: $item->label,
            url: $item->url,
            target: $item->target->value,
            icon: $item->icon,
            image: $item->image_url,
            parentId: $item->parent_id,
            sortOrder: $item->sort_order,
            showOnHeader: $item->show_on_header,
            showOnFooter: $item->show_on_footer,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'url' => $this->url,
            'target' => $this->target,
            'icon' => $this->icon,
            'image' => $this->image,
            'parent_id' => $this->parentId,
            'sort_order' => $this->sortOrder,
            'show_on_header' => $this->showOnHeader,
            'show_on_footer' => $this->showOnFooter,
        ];
    }
}
