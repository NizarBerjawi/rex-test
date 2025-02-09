<?php

namespace Domain\Contact\Repositories\Contracts;

use Domain\Contact\Models\Contact;
use Domain\Shared\Repositories\Contracts\RepositoryInterface;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

interface ContactRepositoryInterface extends RepositoryInterface
{
    /**
     * Bulk create emails for a specified Contact resource
     */
    public function storeContactEmails(Contact $contact, array $emails): bool;

    /**
     * Bulk create phones for a specified Contact resource
     */
    public function storeContactPhones(Contact $contact, array $phones): bool;

    /**
     * Bulk delete emails for a specified Contact resource
     */
    public function deleteAllContactEmails(Contact $contact): bool;

    /**
     * Bulk delete phones for a specified Contact resource
     */
    public function deleteAllContactPhones(Contact $contact): bool;

    /**
     * Search Contacts by a specified set of filters
     */
    public function searchContactsBy(array $filters, bool $paginate = true): AbstractPaginator|Collection;
}
