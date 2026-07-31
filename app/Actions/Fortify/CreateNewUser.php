<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'bdate' => ['required', 'date'],
            'gender' => ['required','string'],
            'city' => ['required','string'],
            'street' => ['required','string'],
            'province' => ['required','string'],
            'prefix' => ['required','max:2'],
            'phone' => ['required','max:11'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'username' => $input['username'],
            'bdate' => $input['bdate'],
            'gender' => $input['gender'],
            'prefix' => $input['prefix'],
            'phone' => $input['phone'],
            'img_url' => null,
            'city' => $input['city'],
            'province' => $input['province'],
            'street' => $input['street'],
            'email' => $input['email'],
            'user_type' => "admin",
            'password' => Hash::make($input['password']),
        ]);

        return $user;
    }
}
