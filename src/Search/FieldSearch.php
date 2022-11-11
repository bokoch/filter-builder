<?php

namespace Mykolab\FilterBuilder\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;

class FieldSearch implements Search
{
    /**
     * @param  Collection<Searchable>  $searchables
     */
    public function __construct(private readonly Collection $searchables)
    {
    }

    public function __invoke(Builder $query, string $value): void
    {
        $query->where(function (Builder $query) use ($value) {
            $this->searchables->map(function (Searchable $searchable) use ($query, $value) {
                $likeOperator = $searchable->caseInsensitive ? 'ilike' : 'like';

                $preparedValue = str($value)
                    ->when($searchable->wildCardAtStart, fn (Stringable $str) => $str->prepend('%'))
                    ->when($searchable->wildCardAtEnd, fn (Stringable $str) => $str->append('%'));

                $query->orWhere($searchable->property, $likeOperator, $preparedValue);
            });
        });
    }
}
