<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DashboardNavigation
{
    /**
     * Return the visible, sorted navigation tree for a dashboard role.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function for(string $role, ?User $user = null): array
    {
        $entries = (array) config("menus.{$role}", []);

        $entries = array_values(array_filter($entries, fn (array $entry): bool => self::isVisible($entry, $user)));

        foreach ($entries as &$entry) {
            if (($entry['type'] ?? 'link') !== 'group') {
                continue;
            }

            $entry['children'] = array_values(array_filter(
                (array) ($entry['children'] ?? []),
                fn (array $child): bool => self::isVisible($child, $user),
            ));

            usort($entry['children'], fn (array $a, array $b): int => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));
        }
        unset($entry);

        $entries = array_values(array_filter(
            $entries,
            fn (array $entry): bool => ($entry['type'] ?? 'link') !== 'group' || count($entry['children'] ?? []) > 0,
        ));

        usort($entries, fn (array $a, array $b): int => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));

        return $entries;
    }

    /**
     * Match only explicitly declared route patterns. Root routes remain exact.
     */
    public static function isActive(array $item, ?string $routeName = null): bool
    {
        $routeName ??= request()->route()?->getName();

        if (! is_string($routeName) || $routeName === '') {
            return false;
        }

        $patterns = Arr::wrap($item['match'] ?? $item['route'] ?? []);

        foreach ($patterns as $pattern) {
            if (is_string($pattern) && $pattern !== '' && Str::is($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }

    public static function groupIsActive(array $group, ?string $routeName = null): bool
    {
        foreach ((array) ($group['children'] ?? []) as $child) {
            if (self::isActive($child, $routeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     * @return list<string>
     */
    public static function initiallyOpenGroups(array $entries, ?string $routeName = null): array
    {
        $open = [];

        foreach ($entries as $entry) {
            if (($entry['type'] ?? 'link') !== 'group') {
                continue;
            }

            if (($entry['default_open'] ?? false) || self::groupIsActive($entry, $routeName)) {
                $open[] = (string) $entry['id'];
            }
        }

        return array_values(array_unique($open));
    }

    private static function isVisible(array $item, ?User $user): bool
    {
        $permission = $item['permission'] ?? null;

        return ! is_string($permission) || $permission === '' || ($user?->can($permission) ?? false);
    }
}
