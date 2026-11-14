<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-zinc-500 antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
    <div class="relative grid h-dvh flex-col items-center justify-center lg:max-w-none lg:grid-cols-2 lg:px-0">

        {{-- LADO IZQUIERDO (Imagen completa y logo centrado) --}}
        <div
            class="relative hidden h-full flex-col items-center justify-center p-12 text-white lg:flex dark:border-e dark:border-neutral-800 overflow-hidden">

            {{-- Fondo: Imagen cubriendo todo el alto/ancho --}}
            {{-- IMPORTANTE: Cambia '/img/fondo-udg.jpg' por la ruta real de tu imagen dentro de la carpeta "public"
            --}}
            <div class="absolute inset-0 bg-[url('/img/fondo-udg.jpg')] bg-cover bg-center bg-no-repeat">
                {{-- Capa de oscurecimiento (overlay) para asegurar que el texto blanco siempre se lea bien --}}
                <div class="absolute inset-0 bg-black/50"></div>
            </div>

            {{-- Nombre del sistema y Logotipo (Totalmente centrados) --}}
            <div
                class="relative z-20 flex flex-col items-center text-center transition-transform hover:scale-105 duration-500">
                {{-- Ícono con efecto cristal (Glassmorphism) --}}


                {{-- Nombre Principal --}}
                <h1 class="text-6xl font-extrabold tracking-tight text-white drop-shadow-2xl">
                    <x-app-logo-icon class="size-10 fill-current text-white dark:text-black" />
                   
                </h1>

                {{-- Subtítulo opcional --}}
                <p class="mt-5 text-xl font-medium text-gray-200 drop-shadow-lg tracking-wide">
                    Sistema de Infromación de Académica
                </p>
            </div>
        </div>

        {{-- LADO DERECHO (Área de Formulario) --}}
        <div class="w-full px-8 lg:p-8 flex flex-col justify-center h-full bg-white dark:bg-transparent">
            <div class="mx-auto flex w-full flex-col justify-center space-y-8 sm:w-[400px]">

                {{-- Logotipo para vista Móvil (solo se ve en celulares) --}}
                <div class="flex flex-col items-center lg:hidden">
                    <a href="{{ route('home') }}" class="z-20 flex items-center gap-3 font-bold text-2xl tracking-tight"
                        wire:navigate>
                        <span
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-600 shadow-lg shadow-indigo-500/30 text-white">
                            <x-app-logo-icon class="size-8 fill-current" />
                        </span>
                        <span class="text-gray-900 dark:text-white">{{ config('app.name', 'SIA') }}</span>
                    </a>
                </div>

                {{-- Contenedor del Slot (Formulario de Login/Registro) --}}
                <div class="w-full">
                    {{ $slot }}
                </div>

                {{-- Footer institucional centrado bajo el formulario --}}
                <div class="mt-8 text-center text-xs text-gray-500 dark:text-gray-400">
                    <p>&copy; {{ date('Y') }} Universidad de Guadalajara.</p>
                    <p class="mt-1">Todos los derechos reservados.</p>
                </div>

            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>