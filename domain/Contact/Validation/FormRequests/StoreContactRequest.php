<?php

namespace Domain\Contact\Validation\FormRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'included' => 'sometimes',
            'included.emails.*.email' => 'required|email|distinct|unique:emails,email',
            'included.phones.*.phone' => [
                'required',
                'regex:/\+(61|62)\d{1,10}$/', // A very basic NZ/AU E164 regex validator
                'distinct',
                'unique:phones,phone',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'included.emails.*.email.unique' => 'A contact with the same email in position #:index already exists',
            'included.phones.*.phone.unique' => 'A contact with the same phone in position #:index already exists',
            'included.phones.*.phone.regex' => 'The phone in position #:index is not in the correct format. Only E164 Australian and New Zealand phone numbers are allowed',
            'included.emails.*.email.distinct' => 'The email in position #:index is a duplicate. The emails array must only contain distinct values',
            'included.phones.*.phone.distinct' => 'The phone in position #:index is a duplicate. The phones array must only contain distinct values',
        ];
    }
}
