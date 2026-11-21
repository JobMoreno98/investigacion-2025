<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
</head>

<body class="min-h-screen bg-white" x-data="{ showSidebar: true }">
    <div id="page-loader"
        style="position:fixed;inset:0;z-index:9999;background:white;display:none;align-items:center;justify-content:center;">
        <div
            style="width:50px;height:50px;border:6px solid #c7d2fe;border-top:6px solid #4f46e5;border-radius:50%;animation:spin 1s linear infinite;">
        </div>
    </div>

    <style>
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <flux:sidebar sticky stashable
        x-bind:class="showSidebar ? 'lg:!w-72 lg:opacity-100' :
            'lg:!w-0 lg:!min-w-0 lg:!border-transparent lg:opacity-0 lg:-translate-x-full'"
        class="dark transition-all duration-300 ease-in-out lg:overflow-hidden border-e border-indigo-900 bg-indigo-950 text-white">

        <flux:sidebar.toggle class="lg:hidden text-white" icon="x-mark" />

        {{-- Contenedor del logo blanco con espacio y línea sutil inferior --}}
        <a href="{{ route('dashboard') }}" class="flex items-center justify-center py-2" wire:navigate>
            <x-app-logo class="h-14 w-auto" />
        </a>

        <flux:navlist class="text-indigo-100">
            <livewire:navigation-bar />
        </flux:navlist>

        <flux:spacer />


        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                icon:trailing="chevrons-up-down" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-indigo-600 text-white font-semibold">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Desktop Toggle Header -->
    <flux:header class="hidden lg:flex bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
        {{-- Usamos un botón normal conectado a nuestra variable de Alpine --}}
        <flux:button variant="subtle" icon="bars-3" class="-ml-2" x-on:click="showSidebar = !showSidebar" />
        <flux:spacer />
    </flux:header>

    <!-- Mobile User Menu (Este código ya lo tienes) -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    <script src="{{ asset('js/validateForm.js') }}"></script>

    <script>
        function initPageLoader() {

            const loader = document.getElementById('page-loader');

            if (!loader) return;

            function show() {
                loader.style.display = 'flex';
            }

            function hide() {
                loader.style.display = 'none';
            }

            window.testLoader = show;
            window.hideLoader = hide;

            document.addEventListener('livewire:navigate', show);
            document.addEventListener('livewire:navigated', hide);
            document.addEventListener('livewire:init', () => {
                Livewire.hook('request', ({
                    respond
                }) => {
                    show();
                    respond(() => hide());
                });
            });
            window.addEventListener('beforeunload', show);
        }
        document.addEventListener('livewire:navigated', initPageLoader);
        document.addEventListener('DOMContentLoaded', initPageLoader);
    </script>
    @livewireScripts
    @fluxScripts

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    @stack('js')

</body>

</html>
