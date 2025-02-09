# Rex Technical Challenge

## How to run the project:

I used my own Docker setup to run this project with Make scripts to make running applications easier.

If you are on Linux, the easiest way to run the project is using the commands in the Makefile. First, ensure you have `Docker` and `make` installed, then run the below commands.


```
cp .env.example .env
make composer-install
make db-migrate
make db-seed
make start 

# The application should start on http://localhost:8080 
```

Otherwise, you can use Laravel Sail with a Postgres database, you just need to update the .env file

## Assumptions

- Every Contact can have many phones
- Every Contact can have many emails
- Every email is unique (two Contacts cannot have the same email)
- Every phone is unique (two Contacts cannot have the same phone)
- Phone calls happen asynchronously (more on that below)
- I should not be using any external packages other than what Laravel comes bundled with.

## Architecture

I took inspiration from Domain Driven Design (DDD) while building this application. This approach helps keep the business/application logic decoupled from the things like the database, APIs or CLIs, making it easier to move to different technologies later.

I utilised the Repository Design Pattern as a standerdised way to access and manipulate data. This pattern make software easier to maintain, test and adapt to changes. 

I tried to stay true to the Laravel framework instead of fighting against it when implementing these patterns. 

## API
I implemented a simple RESTful API that uses some the features of the Contact domain. I loosely followed some of the conventions in the JSON API Specification to design the API.

The endpoints are:
```
GET    /contacts
GET    /contacts/{uuid}
POST   /contacts
PUT    /contacts/{uuid}
DELETE /contacts/{uuid}
```

I was not completely sure about the part related to calling contacts. I made the assumption that we will use an external service such as Twilio to schedule an automated call.

The endpoint that mocks that is:

```
POST /contacts/{uuid}/call
```

To utilize searching/filtering you can do something like this:
```
GET    /contacts?filter[first_name]=john&filter[email]=hello@world.com
```

This will return all the Contacts having firstName John OR having at least one email equal to "hello@world"

## Concessions Made:

### No Tests were written
I would have loved to write feature or unit tests for the application.

### No packages external to Laravel were used.
In a real world application there are some package that could have been useful and made life easier while architecting this application.

- For the API I would have used this package for sophisticated filtering capabilities: 
    - https://github.com/spatie/laravel-query-builder
- For DDD, I would have used these two packages:
    - https://github.com/lorisleiva/laravel-actions
    - https://github.com/spatie/laravel-data

### Duplicated Validation rules
Validation rules were duplicated in Form Requests. Ideally, I should have moved the input validation to the Repository so that its part of the domain. Someone using the ContactRepository to create a new Contact through the CLI will not have their input validated.

### More encapsulation of business logic in Services
I could have moved more of the Business logic to Services. For example, I could have created a ContactService to Upsert a Contact with all its relations. 

All the code in the `PUT /contacts/{uuid}` endpoint could have been summarised by this:
```
$contact = $service->upsertContactWithRelations($contactUuid, $attributes);

return new ConactResource($contact);
```
### Missing an OpenAPI specification

## Error Responses:
I used a simplified implementation of error responses that I've used in a real-world API at work. The error responses follow the [rfc9457](https://www.rfc-editor.org/rfc/rfc9457) standard. This structure allows us to add more details while keeping everything consistent as the application grows.