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


    public function create(
        Oficio $oficio,
        string $reason,
        OficioStatusEnum $type
    ): RejectionInfo {
        return RejectionInfo::create([
            'oficio_id' => $oficio->id,
            'author_id' => Auth::id(),
            'reason'    => $reason,
            'type'      => $type,
        ]);
    }
}
