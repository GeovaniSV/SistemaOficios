<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;

class ContactService
{
    public function list()
    {
        return Contact::with('address', 'responsibles')->paginate(20);
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

        return DB::transaction(function () use (
            $id,
            $data
        ) {

            $contact = Contact::with([
                'address',
                'responsibles'
            ])->findOrFail($id);

            $contact->update([
                'type' => $data['type'],
                'doc' => $data['doc'],
                'name' => $data['name'],
            ]);

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
}

