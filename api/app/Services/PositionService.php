<?php

namespace App\Services;

use App\Models\Position;
use Illuminate\Pagination\LengthAwarePaginator;

class PositionService
{
    public function list(): LengthAwarePaginator
    {
        return Position::paginate(20);
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
