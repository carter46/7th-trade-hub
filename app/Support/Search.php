<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Search
{
    /**
     * Apply FULLTEXT when available (MySQL/MariaDB), otherwise LIKE fallback.
     *
     * @param  list<string>  $columns
     */
    public static function apply(Builder $query, string $term, array $columns = ['name', 'email', 'username']): Builder
    {
        $term = trim($term);
        if ($term === '') {
            return $query;
        }

        $driver = DB::getDriverName();
        $supportsFullText = in_array($driver, ['mysql', 'mariadb'], true)
            && Schema::hasTable($query->getModel()->getTable());

        if ($supportsFullText) {
            try {
                return $query->whereFullText($columns, $term);
            } catch (\Throwable) {
                // Fall through to LIKE when index is missing.
            }
        }

        $like = '%'.$term.'%';

        return $query->where(function (Builder $inner) use ($columns, $like, $term) {
            foreach ($columns as $i => $column) {
                $method = $i === 0 ? 'where' : 'orWhere';
                if ($column === 'username') {
                    $inner->{$method}($column, 'like', $term.'%');
                } else {
                    $inner->{$method}($column, 'like', $like);
                }
            }
        });
    }
}
