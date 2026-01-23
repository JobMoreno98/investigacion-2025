<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <flux:heading size="lg">
            Bienvenido, {{ auth()->user()->name }}
        </flux:heading>

        {{-- 🔔 NOTIFICACIÓN --}}
        @if ($mustFillDatosGenerales)
            <flux:callout type="warning" class="mt-4">
                <flux:callout.heading>
                    Atención requerida
                </flux:callout.heading>

                <flux:callout.text>
                    Debes completar tus <strong>Datos generales</strong> para continuar.
                </flux:callout.text>

                
                <flux:button href="{{ route('categorias.show', $datosGeneralesCategoryId ?? 1) }}" variant="primary">
                    Completar ahora
                </flux:button>

            </flux:callout>
        @endif

        {{-- CONTENIDO NORMAL 
        <flux:card class="mt-6">
            <flux:heading size="sm">Resumen</flux:heading>
            <flux:text>
                Aquí va el contenido normal del dashboard.
            </flux:text>
        </flux:card>
        --}}
    </div>
</x-layouts.app>
