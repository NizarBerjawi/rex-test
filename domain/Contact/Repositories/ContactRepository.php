<?php

namespace Domain\Contact\Repositories;

use Carbon\Carbon;
use Domain\Contact\Models\Contact;
use Domain\Contact\Models\Email;
use Domain\Contact\Models\Phone;
use Domain\Contact\Repositories\Contracts\ContactRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ContactRepository implements ContactRepositoryInterface
{
    public array $relations = [];

    /**
     * The relations to loaded
     */
    public function with(array $relations): self
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * Return all instances of the model
     */
    public function all(): Collection
    {
        return Contact::with($this->relations)->all();
    }

    /**
     * Return a paginated collection of the model
     */
    public function paginate(?int $limit = null): AbstractPaginator
    {
        /** @var \Illuminate\Pagination\AbstractPaginator */
        $paginator = Contact::with($this->relations)
            ->paginate($limit);

        return $paginator->withQueryString();
    }

    /**
     * Attempt to find a model by uuid
     */
    public function find(string $uuid): Contact
    {
        return Contact::with($this->relations)->findOrFail($uuid);
    }

    /**
     * Create a new model with the provided attributes
     */
    public function create(array $attributes): Contact
    {
        return Contact::with($this->relations)
            ->create($attributes);
    }

    /**
     * Attempt to update a model with the provided attributes.
     */
    public function update(array $attributes, string $uuid): Contact
    {
        $contact = Contact::findOrFail($uuid);

        $contact->fill($attributes);

        $contact->save();

        return $contact->load($this->relations);
    }

    /**
     * Attempt to update a model if it is found. Otherwise, create it
     * with the provided attributes
     */
    public function upsert(array $attributes, string $uuid): Contact
    {
        $contact = new Contact;

        // We want to create the Contact with a user provided uuid without
        // having to add uuid to the fillable array of the model
        $contact->unguard();

        $contact = $contact->updateOrCreate(
            [$contact->getKeyName() => $uuid],
            $attributes
        );

        $contact->reguard();

        $contact->load($this->relations);

        return $contact;
    }

    /**
     * Delete a specified model by uuid
     */
    public function delete(string $uuid): bool
    {
        return Contact::where((new Contact)->getKeyName(), $uuid)->delete();
    }

    /**
     * Bulk create emails for a specified Contact resource
     */
    public function storeContactEmails(Contact $contact, array $emails): bool
    {
        if (empty($emails)) {
            return false;
        }

        $now = Carbon::now();

        // If the client provides a uuid, we will use that. Otherwise,
        // we generate a new one for the email.
        return Email::insert(Arr::map($emails, fn ($email) => [
            'uuid' => (string) Str::orderedUuid(),
            ...$email,
            'contact_uuid' => $contact->getKey(),
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }

    /**
     * Bulk create phones for a specified Contact resource
     */
    public function storeContactPhones(Contact $contact, array $phones): bool
    {
        if (empty($phones)) {
            return false;
        }

        $now = Carbon::now();

        // If the client provides a uuid, we will use that. Otherwise,
        // we generate a new one for the phone.
        return Phone::insert(Arr::map($phones, fn ($phone) => [
            'uuid' => (string) Str::orderedUuid(),
            ...$phone,
            'contact_uuid' => $contact->getKey(),
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }

    /**
     * Bulk delete emails for a specified Contact resource
     */
    public function deleteAllContactEmails(Contact $contact): bool
    {
        return $contact->emails()->delete();
    }

    /**
     * Bulk delete phones for a specified Contact resource
     */
    public function deleteAllContactPhones(Contact $contact): bool
    {
        return $contact->phones()->delete();
    }

    /**
     * Search Contacts by a specified set of filters.
     *
     * Very basic search mechanism here... Not very scalable
     */
    public function searchContactsBy(array $filters, bool $paginate = true): AbstractPaginator|Collection
    {
        $builder = Contact::with($this->relations);

        if (isset($filters['first_name'])) {
            $builder->where('first_name', data_get($filters, 'first_name'));
        }

        if (isset($filters['last_name'])) {
            $builder->orWhere('last_name', data_get($filters, 'last_name'));
        }

        if (isset($filters['email'])) {
            $builder->orwhereHas('emails', fn ($query) => $query->where('email', data_get($filters, 'email')));
        }

        if (isset($filters['phone'])) {
            $builder->orWhereHas('phones', fn ($query) => $query->where('phone', data_get($filters, 'phone')));
        }

        if ($paginate) {
            /** @var \Illuminate\Pagination\AbstractPaginator */
            $paginator = $builder->paginate();

            return $paginator->withQueryString();
        }

        return $builder->get();
    }
}
