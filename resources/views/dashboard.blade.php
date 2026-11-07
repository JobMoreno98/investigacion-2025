<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">

        {{-- ENCABEZADO DEL DASHBOARD --}}
        <div class="flex flex-col gap-1 border-b border-gray-200 pb-4">
            <flux:heading size="xl" class="text-gray-800">
                Bienvenido, {{ auth()->user()->name }}
            </flux:heading>
            <flux:subheading class="text-gray-500">
                Panel de control y resumen de tu información académica.
            </flux:subheading>
        </div>

        {{-- ALERTAS GLOBALES --}}
        @if (session('success'))
            <x-alert type="success" class="mb-2">
                {{ session('success') }}
            </x-alert>
        @endif

        @if (session('error'))
            <x-alert type="error" class="mb-2">
                {{ session('error') }}
            </x-alert>
        @endif

        @if (session('warning'))
            <x-alert type="warning" class="mb-2">
                {{ session('warning') }}
            </x-alert>
        @endif

        {{-- NOTIFICACIÓN CRÍTICA (Datos Generales) --}}
        @if ($mustFillDatosGenerales)
            <flux:callout type="warning" class="bg-amber-50 border border-amber-200 shadow-sm mt-2">
                <div class="flex items-start gap-4">
                    <div class="text-amber-500 mt-1">
                        {{-- Icono de alerta (Ajusta el nombre si usas Heroicons u otro set) --}}
                        <flux:icon name="exclamation-triangle" class="w-6 h-6" />
                    </div>
                    <div class="flex-1">
                        <flux:callout.heading class="text-amber-800 font-bold text-lg">
                            Atención requerida
                        </flux:callout.heading>
                        <flux:callout.text class="text-amber-700 mt-1">
                            Debes completar tus <strong>Datos Generales</strong> antes de poder registrar proyectos, llenar
                            otros formularios o generar reportes.
                        </flux:callout.text>
                        <div class="mt-4">
                            {{-- Corregí el route('categorias.show') para que use solo el slug --}}
                            <flux:button href="{{ route('categorias.show', 'datos-generales') }}" variant="primary"
                                size="sm" class="bg-amber-600 hover:bg-amber-700 text-white">
                                Completar ahora
                            </flux:button>
                        </div>
                    </div>
                </div>
            </flux:callout>
        @endif

        {{-- CUADRÍCULA DE ACCIONES PRINCIPALES --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">

            {{-- 1. TARJETA: REPORTE GENERAL (NUEVA) --}}
            <flux:card
                class="flex flex-col justify-between hover:shadow-lg transition-shadow border-t-4 border-t-blue-600 bg-white">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                            <flux:icon name="document-text" class="w-6 h-6" />
                        </div>
                        <flux:heading size="md" class="font-bold text-gray-800">Reporte General</flux:heading>
                    </div>
                    <flux:text class="text-sm text-gray-600 mb-6 line-clamp-3">
                        Genera y descarga un documento PDF con toda tu información académica, laboral y proyectos
                        registrados en el ciclo activo.
                    </flux:text>
                </div>

                {{-- Deshabilitar el botón si no ha llenado datos generales --}}
                {{-- Deshabilitar el botón si no ha llenado datos generales --}}
                <flux:button href="{{ $mustFillDatosGenerales ? '#' : route('reporte.general') }}"
                    target="{{ $mustFillDatosGenerales ? '_self' : '_blank' }}" variant="primary"
                    class="w-full flex justify-center items-center gap-2 {{ $mustFillDatosGenerales ? 'opacity-50 cursor-not-allowed' : '' }}"
                    :disabled="$mustFillDatosGenerales">

                    <flux:icon name="arrow-down-tray" class="w-4 h-4" />
                    Descargar PDF
                </flux:button>
            </flux:card>



            <flux:card
                class="flex flex-col justify-between hover:shadow-lg transition-shadow border-t-4 border-t-blue-600 bg-white">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                            <flux:icon name="document-text" class="w-6 h-6" />
                        </div>
                        <flux:heading size="md" class="font-bold text-gray-800">Informe de Impacto</flux:heading>
                    </div>
                    <flux:text class="text-sm text-gray-600 mb-6 line-clamp-3">
                        Genera y descarga un documento PDF con toda tu información académica, laboral y proyectos
                        registrados en el ciclo activo.
                    </flux:text>
                </div>

                {{-- Deshabilitar el botón si no ha llenado datos generales --}}
                {{-- Deshabilitar el botón si no ha llenado datos generales --}}
                <flux:button href="{{ $mustFillDatosGenerales ? '#' : route('reporte.impacto') }}"
                    target="{{ $mustFillDatosGenerales ? '_self' : '_blank' }}" variant="primary"
                    class="w-full flex justify-center items-center gap-2 {{ $mustFillDatosGenerales ? 'opacity-50 cursor-not-allowed' : '' }}"
                    :disabled="$mustFillDatosGenerales">

                    <flux:icon name="arrow-down-tray" class="w-4 h-4" />
                    Descargar PDF
                </flux:button>
            </flux:card>

            <flux:card
                class="flex flex-col justify-between hover:shadow-lg transition-shadow border-t-4 border-t-blue-600 bg-white">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                            <flux:icon name="document-text" class="w-6 h-6" />
                        </div>
                        <flux:heading size="md" class="font-bold text-gray-800">Informe PROESDE</flux:heading>
                    </div>
                    <flux:text class="text-sm text-gray-600 mb-6 line-clamp-3">
                        Genera y descarga un documento PDF con toda tu información académica, laboral y proyectos
                        registrados en el ciclo activo.
                    </flux:text>
                </div>

                {{-- Deshabilitar el botón si no ha llenado datos generales --}}
                {{-- Deshabilitar el botón si no ha llenado datos generales --}}
                <flux:button href="{{ $mustFillDatosGenerales ? '#' : route('reporte.perfil') }}"
                    target="{{ $mustFillDatosGenerales ? '_self' : '_blank' }}" variant="primary"
                    class="w-full flex justify-center items-center gap-2 {{ $mustFillDatosGenerales ? 'opacity-50 cursor-not-allowed' : '' }}"
                    :disabled="$mustFillDatosGenerales">

                    <flux:icon name="arrow-down-tray" class="w-4 h-4" />
                    Descargar PDF
                </flux:button>
            </flux:card>



            {{-- 2. TARJETA: DATOS LABORALES (Atajo visual) --}}
            <flux:card
                class="flex flex-col justify-between hover:shadow-lg transition-shadow border-t-4 border-t-emerald-500 bg-white">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
                            <flux:icon name="briefcase" class="w-6 h-6" />
                        </div>
                        <flux:heading size="md" class="font-bold text-gray-800">Datos Laborales</flux:heading>
                    </div>
                    <flux:text class="text-sm text-gray-600 mb-6">
                        Actualiza tu nombramiento, departamento y carga horaria para mantener tu expediente
                        institucional al día.
                    </flux:text>
                </div>
                <flux:button href="{{ route('categorias.show', 'datos-laborales') }}" variant="outline"
                    class="w-full flex justify-center items-center gap-2">
                    Actualizar expediente
                </flux:button>
            </flux:card>

            {{-- 3. TARJETA: PROYECTOS DE INVESTIGACIÓN (Atajo visual) --}}
            <flux:card
                class="flex flex-col justify-between hover:shadow-lg transition-shadow border-t-4 border-t-purple-500 bg-white">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-purple-100 text-purple-600 rounded-lg">
                            <flux:icon name="beaker" class="w-6 h-6" />
                        </div>
                        <flux:heading size="md" class="font-bold text-gray-800">Investigación</flux:heading>
                    </div>
                    <flux:text class="text-sm text-gray-600 mb-6">
                        Registra y administra tus proyectos de investigación, montos de apoyo y periodos de ejecución
                        actuales.
                    </flux:text>
                </div>
                <flux:button href="{{ route('categorias.show', 'proyectos-de-investigacion') }}" variant="outline"
                    class="w-full flex justify-center items-center gap-2">
                    Gestionar proyectos
                </flux:button>
            </flux:card>

        </div>
    </div>
</x-layouts.app>