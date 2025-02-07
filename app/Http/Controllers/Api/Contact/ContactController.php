<?php

namespace App\Http\Controllers\Api\Contact;

use Domain\Contact\Exceptions\StoreContactException;
use Domain\Contact\Repositories\Contracts\ContactRepositoryInterface;
use Domain\Contact\Resources\ContactCollection;
use Domain\Contact\Resources\ContactResource;
use Domain\Contact\Validation\FormRequests\StoreContactRequest;
use Domain\Contact\Validation\FormRequests\UpsertContactRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ContactController
{
    public function __construct(protected ContactRepositoryInterface $contacts) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $repository = $this->contacts->with($request->includes()->all());

        $paginator = $request->filters()->isEmpty()
            ? $repository->paginate($request->query('size'))
            : $repository->searchContactsBy($request->filters()->all());

        return ContactCollection::make($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactRequest $request)
    {
        $includes = $request->collect('included');

        try {
            DB::beginTransaction();

            $contact = $this->contacts->create($request->validated());

            if ($includes->has('emails')) {
                $relatedEmails = $includes->get('emails');

                $this->contacts->storeContactEmails($contact, $relatedEmails);
            }

            if ($includes->has('phones')) {
                $relatedPhones = $includes->get('phones');

                $this->contacts->storeContactPhones($contact, $relatedPhones);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            // This exception will get rendered as a HTTP response.
            throw new StoreContactException('Failed to create new Contact', $e);
        }

        if ($includes->isNotEmpty()) {
            $contact->load($includes->keys()->all());
        }

        return ContactResource::make($contact);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $contactUuid)
    {
        $contact = $this->contacts->with(
            $request->includes()->all()
        )->find($contactUuid);

        return ContactResource::make($contact);
    }

    /**
     * Attempts to perform an update of the specified resource. Otherwise,
     * create the a new instance of the resource with any specified relations
     *
     * This endpoint will perform a COMPLETE replacement of the emails and phones
     * relations of the Contact resource.
     *
     * @see https://jsonapi.org/format/#crud-updating-resource-relationships
     */
    public function update(UpsertContactRequest $request, string $contactUuid): ContactResource
    {
        $includes = $request->collect('included');

        try {
            DB::beginTransaction();

            // Upsert a contact
            $contact = $this->contacts->upsert(
                $request->except('included'),
                $contactUuid
            );

            if ($includes->has('emails')) {
                $relatedEmails = $includes->get('emails');

                $this->contacts->deleteAllContactEmails($contact);
                $this->contacts->storeContactEmails($contact, $relatedEmails);
            }

            if ($includes->has('phones')) {
                $relatedPhones = $includes->get('phones');

                $this->contacts->deleteAllContactPhones($contact, $relatedPhones);
                $this->contacts->storeContactPhones($contact, $relatedPhones);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            // This will get rendered as a HTTP response.
            throw new StoreContactException('Failed to upsert new Contact', $e);
        }

        if ($includes->isNotEmpty()) {
            $contact->load($includes->keys()->all());
        }

        return ContactResource::make($contact);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $contactUuid)
    {
        $this->contacts->delete($contactUuid);

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
