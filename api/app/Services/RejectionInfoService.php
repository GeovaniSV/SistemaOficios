<?php

namespace App\Services;

use App\Enums\OficioStatusEnum;
use App\Models\Oficio;
use App\Models\RejectionInfo;
use Illuminate\Support\Facades\Auth;

class RejectionInfoService
{
    public function getById(Oficio $oficio): RejectionInfo
    {
        return $oficio->rejectionInfo;
    }

    public function create(array $data, OficioStatusEnum $type): RejectionInfo
    {
        return RejectionInfo::create([
            'reason'    => $data['reason'],
            'author_id' => Auth::id(),
            'type'      => $type,
        ]);
    }
}
