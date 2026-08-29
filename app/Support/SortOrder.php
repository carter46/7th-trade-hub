<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    /**
     * Move $model to 1-based $newPosition among siblings, shifting neighbors.
     * Positions must be between 1 and sibling count inclusive.
     *
     * @param  Builder  $siblingsQuery  Query that returns all siblings (including $model), unordered OK
     */
    public static function move(Model $model, int $newPosition, Builder $siblingsQuery): void
    {
        $ids = (clone $siblingsQuery)
            ->reorder()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $count = count($ids);
        if ($count === 0) {
            return;
        }

        if ($newPosition < 1 || $newPosition > $count) {
            throw ValidationException::withMessages([
                'sort_order' => "Sort position must be between 1 and {$count}.",
            ]);
        }

        $fromIndex = array_search((int) $model->getKey(), $ids, true);
        if ($fromIndex === false) {
            throw ValidationException::withMessages([
                'sort_order' => 'That item is not in the sortable group.',
            ]);
        }

        $toIndex = $newPosition - 1;
        if ($fromIndex === $toIndex) {
            // Still normalize gaps / 0-based leftovers.
            self::writeSequential($model->getTable(), $ids);

            return;
        }

        array_splice($ids, $fromIndex, 1);
        array_splice($ids, $toIndex, 0, [(int) $model->getKey()]);

        self::writeSequential($model->getTable(), $ids);
        $model->refresh();
    }

    /**
     * Renumber siblings to contiguous 1..N by current sort_order, id.
     */
    public static function normalize(Builder $siblingsQuery): int
    {
        $ids = (clone $siblingsQuery)
            ->reorder()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($ids === []) {
            return 0;
        }

        $table = (clone $siblingsQuery)->getModel()->getTable();
        self::writeSequential($table, $ids);

        return count($ids);
    }

    /**
     * @param  list<int>  $idsInOrder
     */
    private static function writeSequential(string $table, array $idsInOrder): void
    {
        DB::transaction(function () use ($table, $idsInOrder) {
            foreach ($idsInOrder as $index => $id) {
                DB::table($table)->where('id', $id)->update(['sort_order' => $index + 1]);
            }
        });
    }
}
