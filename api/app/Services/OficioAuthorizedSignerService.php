<?php

namespace App\Services;

use App\Models\OficioAuthorizedSigner;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OficioAuthorizedSignerService
{
    public function list(): Collection
    {
        return OficioAuthorizedSigner::with(['user.position', 'position'])
            ->get()
            ->map(fn (OficioAuthorizedSigner $signer) => $this->toArray($signer))
            ->values();
    }

    public function replaceAll(array $entries): Collection
    {
        return DB::transaction(function () use ($entries) {
            OficioAuthorizedSigner::query()->delete();

            foreach ($entries as $entry) {
                OficioAuthorizedSigner::create([
                    'user_id'     => $entry['type'] === 'user' ? $entry['id'] : null,
                    'position_id' => $entry['type'] === 'position' ? $entry['id'] : null,
                ]);
            }

            return $this->list();
        });
    }

    public function isAuthorized(User $user): bool
    {
        return OficioAuthorizedSigner::where('user_id', $user->id)
            ->when(
                $user->position_id,
                fn ($query, $positionId) => $query->orWhere('position_id', $positionId)
            )
            ->exists();
    }

    private function toArray(OficioAuthorizedSigner $signer): array
    {
        if ($signer->user_id) {
            return [
                'id'        => $signer->id,
                'type'      => 'user',
                'signer_id' => $signer->user->id,
                'name'      => $signer->user->name,
                'cargo'     => $signer->user->position?->name,
            ];
        }

        return [
            'id'        => $signer->id,
            'type'      => 'position',
            'signer_id' => $signer->position->id,
            'name'      => $signer->position->name,
        ];
    }
}
