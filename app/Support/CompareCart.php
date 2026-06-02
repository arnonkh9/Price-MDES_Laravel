<?php

namespace App\Support;

use Illuminate\Support\Facades\Session;

class CompareCart
{
    const KEY = 'compare_ids';
    const SPEC_KEY = 'compare_base_spec';
    const MAX = 3;

    /** @return array<int,string> */
    public static function ids(): array
    {
        return Session::get(self::KEY, []);
    }

    public static function count(): int
    {
        return count(self::ids());
    }

    public static function has(string $id): bool
    {
        return in_array($id, self::ids(), true);
    }

    /** @return bool true if toggled on, false if removed or rejected */
    public static function toggle(string $id): string
    {
        $ids = self::ids();
        if (in_array($id, $ids, true)) {
            $ids = array_values(array_filter($ids, fn ($x) => $x !== $id));
            Session::put(self::KEY, $ids);

            return 'removed';
        }
        if (count($ids) >= self::MAX) {
            return 'full';
        }
        $ids[] = $id;
        Session::put(self::KEY, $ids);

        return 'added';
    }

    public static function remove(string $id): void
    {
        Session::put(self::KEY, array_values(array_filter(self::ids(), fn ($x) => $x !== $id)));
    }

    public static function clear(): void
    {
        Session::forget(self::KEY);
    }

    public static function baseSpecId(): ?string
    {
        return Session::get(self::SPEC_KEY);
    }

    public static function setBaseSpec(?string $id): void
    {
        if ($id) {
            Session::put(self::SPEC_KEY, $id);
        } else {
            Session::forget(self::SPEC_KEY);
        }
    }

    public static function active(): bool
    {
        return self::count() > 0 || self::baseSpecId() !== null;
    }
}
