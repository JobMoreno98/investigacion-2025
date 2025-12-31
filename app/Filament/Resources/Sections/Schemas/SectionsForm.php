<?php

namespace App\Filament\Resources\Sections\Schemas;

use App\Models\CatalogItem;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SectionsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->label('Título'),
            TextInput::make('description')->label('Descripción'),
            Select::make('categoria_id')
                ->relationship(name: 'categorias', titleAttribute: 'titulo'),
            Toggle::make('is_repeatable')
                ->label('¿Permite múltiples registros?')
                ->default(true)
                ->helperText('Actívalo para secciones como "Estudios". Desactívalo para "Datos Generales" (solo llenan una vez).'),

            // Aquí gestionamos las preguntas de esta sección
            Repeater::make('questions')->addActionLabel('Añadir pregunta')->collapsed()->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                ->columnSpan(2)
                ->label('Preguntas')
                ->reorderableWithButtons()
                ->columns(2)
                ->relationship() // Usa la relación hasMany
                ->schema([

                    TextInput::make('label')->required()->label('Pregunta'),

                    Select::make('type')
                        ->options([
                            'text' => 'Texto Corto',
                            'textarea' => 'Texto Largo',
                            'number' => 'Número',
                            'select' => 'Lista Desplegable',
                            'file' => 'Archivo',
                            'date' => 'Fecha',
                            'catalog' => 'Lista de Catalogos',
                            'sub_form' => 'Insertar Otra Sección (Sub-Formulario)',
                        ])
                        ->reactive() // Para mostrar/ocultar opciones
                        ->required(),
                    // Agrega esto debajo del selector de tipo
                    TextInput::make('options.allowed_formats')
                        ->label('Formatos permitidos')
                        ->placeholder('ej: pdf, jpg, png, docx')
                        ->helperText('Escribe las extensiones separadas por coma.')
                        ->visible(fn ($get) => $get('type') === 'file'),

                    // Este campo solo aparece si el tipo es 'select'
                    Repeater::make('options.choices')
                        ->label('Opciones del Select')
                        ->schema([
                            TextInput::make('value')
                                ->label('Valor (ID Interno)')
                                ->required(),

                            TextInput::make('label')
                                ->label('Texto a mostrar')
                                ->required(),
                        ])
                        ->columns(2)
                        // ->grid(2) // Opcional: Para que se vean compactos
                        ->defaultItems(1)
                        ->reorderableWithButtons() // O ->reorderable() simple
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['label'] ?? null) // Pone el título en la barrita colapsada
                        ->visible(fn ($get) => $get('type') === 'select'),

                    Select::make('options.catalog_name')
                        ->label('Fuente de Datos')
                        ->helperText('Selecciona qué lista de la base de datos se cargará.')
                        ->visible(fn ($get) => $get('type') === 'catalog') // Solo visible si es tipo catálogo
                        ->required()
                        ->searchable()
                        ->options(function () {
                            // 1. Obtenemos los catálogos universales (Ciudades, Hospitales...)
                            $universal = CatalogItem::query()
                                ->distinct()
                                ->pluck('catalog_type')
                                ->mapWithKeys(fn ($type) => [$type => Str::ucfirst($type)])
                                ->toArray();

                            // 3. Fusionamos ambos arrays para que el admin elija cualquiera
                            return $universal;
                        }),
                    Select::make('options.target_section_id')
                        ->label('Sección a Incrustar')
                        ->helperText('Selecciona la sección genérica que quieres que aparezca aquí.')
                        // Listamos todas las secciones NO repetibles (para simplificar)
                        ->options(\App\Models\Sections::where('is_repeatable', false)->pluck('title', 'id'))
                        ->required()
                        ->visible(fn ($get) => $get('type') === 'sub_form'),

                    Section::make()->schema([
                        Toggle::make('is_required'),
                        Toggle::make('is_unique')
                            ->label('¿Respuesta Única Global?')
                            ->helperText('Si activas esto, dos usuarios no podrán registrar el mismo valor.')
                            ->onColor('danger'),
                    ]),

                ])
                ->orderable('sort_order')
                ->collapsible(),
        ]);
    }
}
