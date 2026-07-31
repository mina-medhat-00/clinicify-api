<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="username" value="{{ __('username') }}" />
                <x-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required autocomplete="username" />
            </div>
            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="bdate" value="{{ __('bdate') }}" />
                <x-input id="bdate" class="block mt-1 w-full" type="date" name="bdate" :value="old('bdate')" required autocomplete="bdate" />
            </div>

            <?php
            $countries = [
            'be' => 'Belgium',
            'nl' => 'The Netherlands',
            ];
            ?>
            <div class="mt-4">
                <x-label for="gender" value="{{ __('gender') }}" />
                <select for="gender" id="gender" class="block mt-1 w-full"  name="gender" :value="old('gender')">
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <!-- other options... -->
                </select>
            </div>
            <div class="mt-4">
                <x-label for="prefix" value="{{ __('prefix') }}" />
                <select for="prefix" id="prefix" class="block mt-1 w-full"  name="prefix" :value="old('prefix')">
                    <option value="20">+20 EG</option>
                    <option value="1">+1 US</option>
                    <option value="45">+45 DN</option>
                    <!-- other options... -->
                </select>
            </div>
            <div class="mt-4">
                <x-label for="phone" value="{{ __('phone') }}" />
                <x-input id="phone" class="block mt-1 w-full" type="phone" name="phone" :value="old('phone')" required autocomplete="phone" />
            </div>

            <div class="mt-4">
                <x-label for="province" value="{{ __('province') }}" />
                <x-input id="province" class="block mt-1 w-full" type="text" name="province" :value="old('province')" required autocomplete="province" />
            </div>
            <div class="mt-4">
                <x-label for="city" value="{{ __('city') }}" />
                <x-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city')" required autocomplete="city" />
            </div>
            <div class="mt-4">
                <x-label for="street" value="{{ __('street') }}" />
                <x-input id="street" class="block mt-1 w-full" type="text" name="street" :value="old('street')" required autocomplete="street" />
            </div>
            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-center">
                            <x-checkbox name="terms" id="terms" required />

                            <div class="ml-2">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Terms of Service').'</a>',
                                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Privacy Policy').'</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-button class="ml-4">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
