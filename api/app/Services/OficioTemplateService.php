<?php

namespace App\Services;

use App\Models\OficioTemplate;

class OficioTemplateService
{
    public function list()
    {
        return OficioTemplate::paginate(20);
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
}
