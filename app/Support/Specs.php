<?php

namespace App\Support;

use App\Models\Category;

class Specs
{
    /** @return array<int,array{id:string,label:string,fields:array<int,string>}> */
    public static function groups(): array
    {
        return config('specs.groups');
    }

    /** @return array<int,string> */
    public static function months(): array
    {
        return config('specs.months');
    }

    public static function monthLabel(?string $month): string
    {
        if (! $month) {
            return '';
        }
        $idx = ((int) $month) - 1;

        return config('specs.months')[$idx] ?? '';
    }

    /** @return array<int,string> */
    public static function years(): array
    {
        return config('specs.years');
    }

    /** @return array<int,string> */
    public static function historySources(): array
    {
        return config('specs.history_sources');
    }

    /** @return array<int,string> */
    public static function palette(): array
    {
        return config('specs.palette');
    }

    /**
     * รวม spec field-key จากสเปคอ้างอิง + vendors (เรียงตามลำดับที่พบ, ไม่ซ้ำ).
     * ใช้สร้างแถวสเปคในฟอร์ม/detail/export ของการเปรียบเทียบ โดยใช้ key เป็น label.
     *
     * @param  array<string,mixed>|null  $referenceSpecs
     * @param  iterable<array<string,mixed>|null>  $vendorSpecsList
     * @return array<int,string>
     */
    public static function comparisonFieldKeys(?array $referenceSpecs, iterable $vendorSpecsList): array
    {
        $keys = [];
        foreach (array_keys($referenceSpecs ?? []) as $k) {
            $keys[(string) $k] = true;
        }
        foreach ($vendorSpecsList as $specs) {
            foreach (array_keys((array) ($specs ?? [])) as $k) {
                $keys[(string) $k] = true;
            }
        }

        return array_keys($keys);
    }

    /**
     * Categories from DB (excluding the synthetic "all"), as plain arrays keyed by order.
     *
     * @return \Illuminate\Support\Collection<int,Category>
     */
    public static function categories()
    {
        return Category::orderBy('position')->get();
    }

    /** Map slug => color, used widely for badges. */
    public static function colorMap(): array
    {
        return Category::pluck('color', 'slug')->toArray();
    }

    public static function color(?string $slug): string
    {
        if (! $slug) {
            return '#64748B';
        }

        return static::colorMap()[$slug] ?? '#64748B';
    }

    public static function label(?string $slug): string
    {
        if (! $slug) {
            return '';
        }

        return Category::where('slug', $slug)->first()?->label ?? $slug;
    }

    /**
     * Render a spec value safely for output (PDF/HTML).
     * Guards against array values reaching htmlspecialchars(), which would
     * throw "Argument #1 must be of type string, array given".
     */
    public static function display($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return is_array($value)
            ? json_encode($value, JSON_UNESCAPED_UNICODE)
            : (string) $value;
    }
}
