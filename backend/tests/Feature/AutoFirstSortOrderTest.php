<?php

namespace Tests\Feature;

use App\Models\EventOverview;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\NavigationItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoFirstSortOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_event_without_sort_order_is_placed_first_and_shifts_existing(): void
    {
        $older = EventOverview::query()->create([
            'title' => 'Older Event',
            'image' => 'events/older.jpg',
            'status' => true,
            'sort_order' => 1,
        ]);
        $newerDefault = EventOverview::query()->create([
            'title' => 'Newest Event',
            'image' => 'events/newest.jpg',
            'status' => true,
            'sort_order' => 0,
        ]);
        $alsoUnset = EventOverview::query()->create([
            'title' => 'Brand New',
            'image' => 'events/brand-new.jpg',
            'status' => true,
        ]);

        $this->assertSame(0, $alsoUnset->fresh()->sort_order);
        $this->assertSame(1, $newerDefault->fresh()->sort_order);
        $this->assertSame(3, $older->fresh()->sort_order);

        $ordered = EventOverview::query()->ordered()->pluck('title')->all();
        $this->assertSame(['Brand New', 'Newest Event', 'Older Event'], $ordered);
    }

    public function test_explicit_nonzero_sort_order_is_kept_and_does_not_shift_siblings(): void
    {
        $first = EventOverview::query()->create([
            'title' => 'First',
            'image' => 'events/first.jpg',
            'status' => true,
            'sort_order' => 1,
        ]);
        $second = EventOverview::query()->create([
            'title' => 'Second',
            'image' => 'events/second.jpg',
            'status' => true,
            'sort_order' => 2,
        ]);

        $this->assertSame(1, $first->fresh()->sort_order);
        $this->assertSame(2, $second->fresh()->sort_order);
    }

    public function test_updating_sort_order_does_not_reapply_auto_first(): void
    {
        $a = EventOverview::query()->create([
            'title' => 'A',
            'image' => 'events/a.jpg',
            'status' => true,
            'sort_order' => 1,
        ]);
        $b = EventOverview::query()->create([
            'title' => 'B',
            'image' => 'events/b.jpg',
            'status' => true,
            'sort_order' => 2,
        ]);

        $a->update(['sort_order' => 2]);
        $b->update(['sort_order' => 1]);

        $this->assertSame(2, $a->fresh()->sort_order);
        $this->assertSame(1, $b->fresh()->sort_order);
        $this->assertSame(['B', 'A'], EventOverview::query()->ordered()->pluck('title')->all());
    }

    public function test_gallery_item_auto_first_is_scoped_to_category(): void
    {
        $weddings = GalleryCategory::query()->create([
            'name' => 'Weddings',
            'slug' => 'weddings',
            'status' => true,
            'sort_order' => 1,
        ]);
        $birthdays = GalleryCategory::query()->create([
            'name' => 'Birthdays',
            'slug' => 'birthdays',
            'status' => true,
            'sort_order' => 2,
        ]);

        $weddingOld = GalleryItem::query()->create([
            'gallery_category_id' => $weddings->id,
            'title' => 'Old Wedding',
            'image' => 'gallery/old.jpg',
            'status' => true,
            'sort_order' => 1,
        ]);
        $birthdayOld = GalleryItem::query()->create([
            'gallery_category_id' => $birthdays->id,
            'title' => 'Old Birthday',
            'image' => 'gallery/bday.jpg',
            'status' => true,
            'sort_order' => 1,
        ]);
        $weddingNew = GalleryItem::query()->create([
            'gallery_category_id' => $weddings->id,
            'title' => 'New Wedding',
            'image' => 'gallery/new.jpg',
            'status' => true,
            'sort_order' => 0,
        ]);

        $this->assertSame(0, $weddingNew->fresh()->sort_order);
        $this->assertSame(2, $weddingOld->fresh()->sort_order);
        $this->assertSame(1, $birthdayOld->fresh()->sort_order);
    }

    public function test_navigation_item_auto_first_is_scoped_to_parent(): void
    {
        $parent = NavigationItem::query()->create([
            'label' => 'Parent',
            'url' => '/parent',
            'status' => true,
            'is_visible' => true,
            'sort_order' => 1,
        ]);
        $childA = NavigationItem::query()->create([
            'label' => 'Child A',
            'url' => '/a',
            'parent_id' => $parent->id,
            'status' => true,
            'is_visible' => true,
            'sort_order' => 1,
        ]);
        $rootNew = NavigationItem::query()->create([
            'label' => 'New Root',
            'url' => '/new',
            'status' => true,
            'is_visible' => true,
            'sort_order' => 0,
        ]);
        $childNew = NavigationItem::query()->create([
            'label' => 'Child New',
            'url' => '/new-child',
            'parent_id' => $parent->id,
            'status' => true,
            'is_visible' => true,
            'sort_order' => 0,
        ]);

        $this->assertSame(0, $rootNew->fresh()->sort_order);
        $this->assertSame(2, $parent->fresh()->sort_order);
        $this->assertSame(0, $childNew->fresh()->sort_order);
        $this->assertSame(2, $childA->fresh()->sort_order);
    }
}
