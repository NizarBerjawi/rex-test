<?php

namespace Domain\Contact\Repositories;

use Carbon\Carbon;
use Domain\Contact\Models\Contact;
use Domain\Contact\Models\Email;
use Domain\Contact\Models\Phone;
use Domain\Contact\Repositories\Contracts\ContactRepositoryInterface;
use Domain\Shared\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ContactRepository extends BaseRepository implements ContactRepositoryInterface
{
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
        $builder = $this->model->with($this->relations);

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
