<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte General - {{ $user->name }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        h1 { text-align: center; font-size: 18px; text-transform: uppercase; margin-bottom: 5px; color: #1a4b8c; }
        .subtitle { text-align: center; font-size: 12px; margin-bottom: 20px; color: #555; }
        
        .categoria-header { background-color: #1a4b8c; color: #ffffff; padding: 6px 10px; font-weight: bold; font-size: 13px; text-transform: uppercase; margin-top: 20px; margin-bottom: 10px; }
        .seccion-header { background-color: #e5e7eb; color: #374151; padding: 5px 10px; font-weight: bold; font-size: 12px; margin-top: 10px; margin-bottom: 5px; border-left: 4px solid #1a4b8c; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; page-break-inside: avoid; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #f9fafb; font-weight: bold; width: 35%; color: #4b5563; font-size: 10px; text-transform: uppercase; }
        td { font-size: 11px; color: #111827; }
        
        .sub-entry-title { font-weight: bold; font-size: 11px; color: #1a4b8c; margin-top: 8px; margin-bottom: 4px; padding-left: 10px; }
        .repeater-badge { display: inline-block; background: #e5e7eb; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-bottom: 2px;}
    </style>
</head>
<body>

    <h1>Reporte Académico General</h1>
    <div class="subtitle">
        Usuario: <strong>{{ $user->name }}</strong> | Ciclo: <strong>{{ $ciclo->nombre }}</strong>
    </div>

    @foreach ($reportData as $catData)
        <div class="categoria-header">{{ $catData['categoria']->titulo }}</div>

        @foreach ($catData['seccionesData'] as $secData)
            <div class="seccion-header">{{ $secData['seccion']->title }}</div>

            @foreach ($secData['entries'] as $index => $entry)
                {{-- Si hay múltiples registros del mismo formulario (ej. Varios proyectos de investigación) --}}
                @if(count($secData['entries']) > 1)
                    <div style="background: #f3f4f6; padding: 3px 10px; font-weight: bold; font-size:10px; margin-top:5px;">
                        Registro #{{ $index + 1 }}
                    </div>
                @endif

                <table>
                    <tbody>
                        @foreach ($entry->answers->sortBy(fn($a) => $a->question->sort_order) as $answer)
                            {{-- Ignoramos las preguntas tipo sub_form, ya que los datos de los hijos se iteran abajo --}}
                            @if ($answer->question->type !== 'sub_form' && $answer->question->type !== 'file')
                                <tr>
                                    <th>{{ $answer->question->label }}</th>
                                    <td>
                                        @php
                                            // Manejo seguro de JSON para campos como checkboxes o catalogos multiples
                                            $val = json_decode($answer->value, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($val)) {
                                                if (isset($val[0]) && is_array($val[0])) {
                                                    // Si es un repeater de premios/roles
                                                    foreach($val as $item) {
                                                        echo "<div class='repeater-badge'>" . htmlspecialchars(implode(': ', $item)) . "</div><br>";
                                                    }
                                                } else {
                                                    // Si es un array simple (checkboxes)
                                                    echo htmlspecialchars(implode(', ', $val));
                                                }
                                            } else {
                                                echo nl2br(htmlspecialchars($answer->value ?? '---'));
                                            }
                                        @endphp
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>

                {{-- ITERACIÓN DE SUB-FORMULARIOS (Ej. Especializaciones, Licenciaturas, etc.) --}}
                @if ($entry->children->count() > 0)
                    @foreach ($entry->children as $childIndex => $child)
                        <div class="sub-entry-title">
                            Detalles Adicionales 
                            {{ $child->section ? ' - ' . $child->section->title : '' }}
                        </div>
                        <table>
                            <tbody>
                                @foreach ($child->answers->sortBy(fn($a) => $a->question->sort_order) as $childAnswer)
                                    @if ($childAnswer->question->type !== 'file')
                                    <tr>
                                        <th>{{ $childAnswer->question->label }}</th>
                                        <td>
                                            @php
                                                $childVal = json_decode($childAnswer->value, true);
                                                echo (json_last_error() === JSON_ERROR_NONE && is_array($childVal)) 
                                                    ? htmlspecialchars(implode(', ', $childVal)) 
                                                    : nl2br(htmlspecialchars($childAnswer->value ?? '---'));
                                            @endphp
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @endforeach
                @endif
            @endforeach
        @endforeach
    @endforeach

</body>
</html>