<x-layouts.auth.split>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Iniciar sesión')" :description="__('Ingresa tu correo institucional para acceder al sistema')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf
            @method('post')
            <!-- Correo o Código -->
            <flux:input wire:model="email" :label="__('Correo Institucional')" type="text" required autofocus
                autocomplete="username" placeholder="ej. usuario@academicos.udg.mx" />

            <!-- Contraseña -->
            <div class="relative">
                <flux:input wire:model="password" :label="__('Contraseña')" type="password" required
                    autocomplete="current-password" :placeholder="__('Contraseña')" viewable />

                @if (Route::has('password.request'))
                    <flux:link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                        {{ __('¿Olvidaste tu contraseña?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Recordar sesión -->
            <flux:checkbox wire:model="remember" :label="__('Mantener sesión iniciada')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full">
                    {{ __('Ingresar al SIA') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts.auth.split>
