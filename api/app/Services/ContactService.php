<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContactService
{
    public function list(?bool $isActive = null)
    {
        $query = Contact::with('address', 'responsibles');

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        return $query->paginate(20);
    }

    public function getById(int $id): Contact{
        return Contact::with(['address', 'responsibles'])->findOrFail($id);
    }

    public function getResponsibles(int $contactId)
    {
        $contact = Contact::findOrFail($contactId);

        return $contact->responsibles;
    }

    public function create(array $data): Contact
    {
        return DB::transaction(function () use ($data) {

            $address = Address::create(
                $data['address']
            );

            $contact = Contact::create([
                'type' => $data['type'],
                'doc' => $data['doc'],
                'name' => $data['name'],
                'address_id' => $address->id,
            ]);

            $contact->responsibles()->createMany(
                $data['responsibles']
            );

            return $this->getById($contact->id);
        });
    }

    public function update(
        int $id,
        array $data
    ): Contact {

        if (!empty($data['doc'])) {
            $doc = preg_replace('/\D/', '', $data['doc']);

            $docTaken = Contact::where('doc', $doc)
                ->where('id', '!=', $id)
                ->exists();

            if ($docTaken) {
                throw ValidationException::withMessages([
                    'doc' => ['O documento já está em uso por outro contato.'],
                ]);
            }
        }

        return DB::transaction(function () use (
            $id,
            $data
        ) {

            $contact = Contact::with([
                'address',
                'responsibles'
            ])->findOrFail($id);

            $fields = [
                'type' => $data['type'],
                'name' => $data['name'],
            ];

            if (!empty($data['doc'])) {
                $fields['doc'] = $data['doc'];
            }

            $contact->update($fields);

            $contact->address->update(
                $data['address']
            );

            $contact->responsibles()->delete();

            $contact->responsibles()->createMany(
                $data['responsibles']
            );

            return $this->getById($contact->id);
        });
    }

    public function toggleActive(int $id, bool $activate): Contact
    {
        $contact = Contact::findOrFail($id);
        $contact->update(['is_active' => $activate]);
        return $this->getById($id);
    }
}

