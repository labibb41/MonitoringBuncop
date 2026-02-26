<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-900">Login VitaRoot</h1>
        <p class="mt-1 text-sm text-gray-600">Masuk terlebih dahulu untuk mengakses dashboard VitaRoot, Incubator, dan Nutrimix.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-4">
        <a href="{{ route('sso.redirect') }}"
            class="inline-flex w-full items-center justify-center rounded-md border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
            Login dengan SSO Keycloak
        </a>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-indigo-600 hover:text-indigo-800" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            @endif
        </div>

        <x-primary-button class="w-full justify-center">
            Log in
        </x-primary-button>
    </form>

    <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-4 text-xs text-gray-700">
        <p class="font-semibold text-gray-900">Akun default (hasil seeder):</p>
        <p class="mt-2"><span class="font-semibold">Super Admin</span> - superadmin@vitaroot.local / password123</p>
        <p><span class="font-semibold">Admin</span> - admin@vitaroot.local / password123</p>
        <p><span class="font-semibold">User</span> - user@vitaroot.local / password123</p>
    </div>
</x-guest-layout>
