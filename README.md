# laravel-simple-dto
Composer Package for creating simple DTOs (Data Transfer Objects) in the Laravel framework  
  
Github: [https://github.com/oilytortoise/laravel-simple-dto]  
Packagist: [https://packagist.org/packages/oilytortoise/laravel-simple-dto]  
  
## Changes
### v1.0.2
- Removed `implements Wireable` from `AbstractDto` to allow usage in back-end services without installing Livewire.
    - Simply add `implements Wireable` to your DTOs in order to use them in Livewire components.

## Installation
`composer require oilytortoise/laravel-simple-dto`
  
  

## Assets
The package consists of two key classes:
 - `AbstractDto`
 - `AbstractDtoCollection`
  
  
 ## Usage
 DTOs are a handy data structure to help send data around your application, generate API payloads/responses, hydrate incoming request data etc.

 While all of this can be done with simple arrays, DTOs have the added benefit of being able to easily typecast values being stored, customize hydration logic, and add any functions for getting and setting values where necessary (however it is generally not advised to include business logic within a DTO).
  
  

### Creating a DTO
 A DTO class is very simple to create. Here is an example:

 ```
<?php

use Oilytortoise\LaravelSimpleDto\AbstractDto;

class UserDto extends AbstractDto
{
    public string $name;
    public ?string $email;
}
 ```

 And that's it!

 You can create a new instance of the DTO as follows:  
 `$user = new UserDto(['name' => 'Oily Tortoise', 'email' => 'oily@tortoise.com']);`

 **NOTE:** This package does not support union type properties *yet* e.g. `public string|int $unionProperty;` would not work and I recommend simply not type-hinting those properties for now. I am intending to introduce support for union types soon...

 You can also store other DTOs as properties:
  ```
<?php

use Oilytortoise\LaravelSimpleDto\AbstractDto;

class UserConfigDto extends AbstractDto
{
    public string $locale = 'en';
    public bool $2faEnabled = false;
    public bool $subscribedToNewsletter = true;
}

class UserDto extends AbstractDto
{
    public string $name;
    public ?string $email;
    public UserConfigDto $config; // Here, the config property is an instance of the UserConfigDto
}
 ```

 These can be constructed in two ways:
 - By nesting arrays:
 ```
 $user = new UserDto([
    'name' => 'Oily Tortoise',
    'email' => 'oily@tortoise.com',
    'config' => [
        'locale' => 'en',
        'subscribedToNesletter' => false,
    ]
 ]);
 ```

 - By passing in a constructed DTO:
 ```
 $userConfig = new UserConfigDto();
 $user = new UserDto(['name' => 'Oily Tortoise', 'email' => 'oily@tortoise.com', 'config' => $userConfig]);
 ```

 The `AbstractDto` constructor will recursively construct any nested DTOs upon construction - provided the value passed in is an array.
 This can be very helpful, for instance when you are generating an API response, you can validate the data being returned by creating a DTO instance of the response. As long as the properties of the DTO are set correctly - it will ensure that required values are present. It will also set default values if they are missing.
  

 ### DTO Collections
 Basically it's a collection of DTOs which can be accessed using any of Laravel's collection functions: [https://laravel.com/docs/11.x/collections#available-methods].

Instead of having an array of DTOs, you can create a DTO collection in which you can add functions to retrieve/query for deeply nested values.

To create a DTO collection, simply create a class which extends `Oilytortoise\LaravelSimpleDto\DtoCollection` and add the class of the DTO the collection should include:
  
```
use Oilytortoise\LaravelSimpleDto\DtoCollection;
use App\Dtos\Users\UserConfigDto;

class UserConfigDtoCollection extends DtoCollection
{
    protected string $dtoClass = UserConfigDto::class;
}
```
  
  

## Database JSON Casting
Many relational database engines such as MySQL now support JSON column types. These can be very useful especially when combined with Laravel's casting functionality.

You can easily cast data stored in your tables' JSON columns following the casting guide [https://laravel.com/docs/11.x/eloquent-mutators#custom-casts] and create casts which construct your DTO when the model is retrieved.

The Cast's `get()` and `set()` functions typically look something like this (however you can customize them however you want):
```
public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $dataArray = json_decode($value, true);

        return new UserConfigDto($dataArray);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $dataArray = $value->toArray();

        return json_encode($dataArray, JSON_UNESCAPED_SLASHES);
    }
```
  
  

 ## Livewire
 Any class which extends `AbstractDto` is automatically available for use as Livewire component properties.  
 **NOTE: as of v1.0.2 you must add `implements Wireable` manually to DTOs you wish to use in Livewire components. This was changed to allow usage in back-end services without installing Livewire unnecessarily.**

 This means that you can do things like create an `EditUserProfileDto` to store values from a form. As long as there is a public property on your component (e.g. `public EditUserProfileDto $editUserProfileDto;`) You can use `wire:model="editUserProfileDto.name"` and `wire:model="editUserProfileDto.email"` etc. on your input elements to fill the DTO values directly. You can then validate the DTO and persist data to your database using whatever pattern you like.
  
  

 # Coming soon...
 - Support for getter and setter functions