<?php

namespace App\Services;

use App\Filters\BooleanFilter;
use App\Models\OficioTemplate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OficioTemplateService
{
    public function list()
    {
        return QueryBuilder::for(OficioTemplate::class)
            ->allowedFilters(...[
                AllowedFilter::partial('name'),
                AllowedFilter::custom('is_active', new BooleanFilter()),
            ])
            ->allowedSorts(...['name', 'created_at'])
            ->paginate(20);
    }

    public function getById(
        OficioTemplate $template
    ): OficioTemplate {

        return $template;
    }

    public function create(
        array $data
    ): OficioTemplate {

        return OficioTemplate::create($data);
    }

    public function update(
        OficioTemplate $template,
        array $data
    ): OficioTemplate {

        $template->update($data);

        return $template;
    }

    public function toggleActive(OficioTemplate $template, bool $activate): OficioTemplate
    {
        $template->update(['is_active' => $activate]);

        return $template->fresh();
    }
}
