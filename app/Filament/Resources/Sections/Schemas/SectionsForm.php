<?php

namespace App\Filament\Resources\Sections\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SectionsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required(),
            TextInput::make('description'),
            Select::make('categoria_id')
                ->relationship(name: 'categorias', titleAttribute: 'titulo'),

            // Aquí gestionamos las preguntas de esta sección
            Repeater::make('questions')
                ->columnSpan(2)
                ->columns(2)
                ->relationship() // Usa la relación hasMany
                ->schema([
                    TextInput::make('label')->required()->label('Pregunta'),

                    Select::make('type')
                        ->options([
                            'text' => 'Texto Corto',
                            'textarea' => 'Texto Largo',
                            'select' => 'Lista Desplegable',
                            'file' => 'Archivo',
                            'date' => 'Fecha',
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
                    KeyValue::make('options.choices')->label('Opciones del Select')->keyLabel('Valor (ID)')->valueLabel('Texto a mostrar')->hidden(fn ($get) => $get('type') !== 'select'),

                    Toggle::make('is_required'),
                ])
                ->orderable('sort_order') // Permite reordenar preguntas arrastrando
                ->collapsible(),
        ]);
    }
}
