<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-slate-50 antialiased dark:bg-slate-950">
    <div class="relative grid h-dvh flex-col items-center justify-center lg:max-w-none lg:grid-cols-2 lg:px-0">

        {{-- LADO IZQUIERDO (Imagen institucional con filtro azul y branding centrado) --}}
        <div
            class="relative hidden h-full flex-col items-center justify-center p-12 text-white lg:flex dark:border-e dark:border-blue-950/60 overflow-hidden">

            {{-- Fondo: Imagen cubriendo todo el alto/ancho --}}
            <div class="absolute inset-0 bg-[url('/img/fondo-udg.jpg')] bg-cover bg-center bg-no-repeat">
                {{-- Capa de oscurecimiento con degradado azul marino institucional --}}
                <div class="absolute inset-0 bg-gradient-to-br from-blue-950/90 via-blue-900/75 to-slate-950/90"></div>
            </div>

            <div
                class="relative z-20 flex max-w-md flex-col items-center rounded-2xl border border-white/15 bg-indigo-950/70 p-10 text-center shadow-2xl transition-transform duration-500 hover:scale-105">

                <x-app-logo class="h-28 w-auto drop-shadow-2xl" />

                <div class="my-4 h-1 w-16 rounded-full bg-indigo-500"></div>

                <p class="text-xl font-medium text-gray-200 drop-shadow-lg tracking-wide">
                    Sistema de Información Académica
                </p>

                <span class="mt-2 text-md uppercase tracking-widest text-gray-300">
                    centro universitario de ciencias sociales y humanidades
                </span>
                <span class="mt-2 text-xs uppercase tracking-widest text-gray-300">
                    Universidad de Guadalajara
                </span>
            </div>
        </div>

        {{-- LADO DERECHO (Área de Formulario) --}}
        <div class="flex h-full w-full flex-col justify-between bg-white px-8 py-10 lg:p-12 dark:bg-slate-950">

            {{-- Espaciador superior para centrar verticalmente en pantallas grandes --}}
            <div class="hidden lg:block"></div>

            <div class="mx-auto flex w-full flex-col justify-center space-y-8 sm:w-[400px]">

                <div class="flex flex-col items-center lg:hidden">
                    <a href="{{ route('home') }}"
                        class="z-20 flex items-center justify-center rounded-2xl bg-indigo-950 px-6 py-3 shadow-lg shadow-indigo-950/30"
                        wire:navigate>
                        <x-app-logo class="h-12 w-auto" />
                    </a>
                </div>

                {{-- Contenedor del Formulario (Slot de Livewire / Blade) --}}
                <div class="w-full">
                    {{ $slot }}
                </div>

            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>
