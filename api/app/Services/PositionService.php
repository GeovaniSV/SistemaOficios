<?php

namespace App\Services;

use App\Filters\BooleanFilter;
use App\Models\Position;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PositionService
{
    public function list(): LengthAwarePaginator
    {
        return QueryBuilder::for(Position::class)
            ->allowedFilters(...[
                AllowedFilter::partial('name'),
                AllowedFilter::custom('is_active', new BooleanFilter()),
            ])
            ->allowedSorts(...['name', 'created_at'])
            ->paginate(20);
    }

    public function getById(Position $position): Position
    {
        return $position;
    }

    public function create(array $data): Position
    {
        return Position::create($data);
    }

    public function update(Position $position, array $data): Position
    {
        $position->update($data);

        return $position;
    }

    public function toggleActive(Position $position, bool $activate): Position
    {
        $position->update(['is_active' => $activate]);

        return $position->fresh();
    }
}
