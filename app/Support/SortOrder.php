<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SortOrder
{
    /**
     * Next sort_order = current max + 1 (starts at 1 when empty).
     *
     * @param  class-string<Model>|Builder  $modelOrQuery
     */
    public static function next(string|Builder $modelOrQuery): int
    {
        $query = $modelOrQuery instanceof Builder
            ? $modelOrQuery
            : $modelOrQuery::query();

        return ((int) $query->max('sort_order')) + 1;
    }
}
