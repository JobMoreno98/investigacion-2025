<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Perfil Académico</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10px;
            color: #333;
        }

        .section-header {
            background-color: #d1d5db;
            padding: 4px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
            border-bottom: 2px solid #9ca3af;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 5px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f3f4f6;
            font-weight: normal;
            color: #4b5563;
        }

        td {
            font-weight: bold;
        }

        .dynamic-table th {
            width: 30%;
            background-color: #e5e7eb;
            font-weight: bold;
        }

        .dynamic-table td {
            font-weight: normal;
        }
    </style>
</head>

<body>

@php
        function getAns($entry, $keyword) {
            if(!$entry) return '---';
            
            // 1. Primero buscamos en el formulario padre
            $ans = $entry->answers->first(fn($a) => stripos($a->question->label, $keyword) !== false);
            if ($ans) return $ans->formatted_value;

            // 2. Si no está en el padre, buscamos dentro de sus sub-formularios (hijos)
            if ($entry->children) {
                foreach ($entry->children as $child) {
                    $childAns = $child->answers->first(fn($a) => stripos($a->question->label, $keyword) !== false);
                    if ($childAns) return $childAns->formatted_value;
                }
            }

            return '---';
        }
    @endphp

    {{-- BLOQUE 1: DATOS GENERALES (Formato Fijo) --}}
    <table>
        <tr>
            <th>Apellido Paterno</th>
            <th>Apellido Materno</th>
            <th>Nombre(s)</th>
            <th>Código</th>
        </tr>
        <tr>
            <td>{{ getAns($datosGenerales, 'Paterno') }}</td>
            <td>{{ getAns($datosGenerales, 'Materno') }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ getAns($datosGenerales, 'Código') }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <th>Domicilio</th>
            <th>Teléfono</th>
        </tr>
        <tr>
            <td>{{ getAns($datosGenerales, 'Domicilio') }}</td>
            <td>{{ getAns($datosGenerales, 'Teléfono') }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <th>CURP</th>
            <th>RFC</th>
            <th>Grado</th>
            <th>Disciplina del grado</th>
        </tr>
        <tr>
            <td>{{ getAns($datosGenerales, 'CURP') }}</td>
            <td>{{ getAns($datosGenerales, 'RFC') }}</td>
            <td>{{ getAns($datosGenerales, 'Grado') }}</td>
            <td>{{ getAns($datosGenerales, 'Disciplina') }}</td>
        </tr>
    </table>

    {{-- BLOQUE 2: DATOS LABORALES (Formato Fijo) --}}
    <div class="section-header">Datos Laborales</div>
    <table>
        <tr>
            <th>Departamento</th>
            <th>Nombramiento</th>
            <th>Tipo Nombramiento</th>
            <th>Antigüedad</th>
        </tr>
        <tr>
            <td>{{ getAns($datosLaborales, 'Departamento') }}</td>
            <td>{{ getAns($datosLaborales, 'Nombramiento') }}</td>
            <td>{{ getAns($datosLaborales, 'Tipo Nombramiento') }}</td>
            <td>{{ getAns($datosLaborales, 'Antigüedad') }}</td>
        </tr>
    </table>

    {{-- BLOQUE 3: EL RESTO DE SECCIONES (Iteración Dinámica) --}}
    @foreach ($reportData as $catData)
        @foreach ($catData['seccionesData'] as $secData)
            <div class="section-header">{{ $secData['seccion']->title }}</div>

            @foreach ($secData['entries'] as $index => $entry)
                <table class="dynamic-table">
                    <tbody>
                        @foreach ($entry->answers->sortBy(fn($a) => $a->question->sort_order) as $answer)
                            @if ($answer->question->type !== 'sub_form' && $answer->question->type !== 'file')
                                <tr>
                                    <th>{{ $answer->question->label }}</th>
                                    <td>
                                        @php
                                            $val = json_decode($answer->value, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($val)) {
                                                if (isset($val[0]) && is_array($val[0])) {
                                                    foreach ($val as $item)
                                                        echo htmlspecialchars(implode(': ', $item)) . "<br>";
                                                } else {
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

                {{-- Sub-formularios --}}
                @foreach ($entry->children as $child)
                    <table class="dynamic-table" style="margin-left: 20px; width: 95%; border-left: 3px solid #1a4b8c;">
                        <tbody>
                            @foreach ($child->answers->sortBy(fn($a) => $a->question->sort_order) as $childAnswer)
                                @if ($childAnswer->question->type !== 'file')
                                    <tr>
                                        <th style="background-color: #f9fafb;">{{ $childAnswer->question->label }}</th>
                                        <td>
                                            @php
                                                $cVal = json_decode($childAnswer->value, true);
                                                echo (json_last_error() === JSON_ERROR_NONE && is_array($cVal))
                                                    ? htmlspecialchars(implode(', ', $cVal))
                                                    : nl2br(htmlspecialchars($childAnswer->value ?? '---'));
                                            @endphp
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @endforeach
        @endforeach
    @endforeach

</body>

</html>