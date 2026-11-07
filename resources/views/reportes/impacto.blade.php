<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Impacto</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; }
        .page-break { page-break-after: always; }
        h1 { text-align: center; font-size: 16px; margin-bottom: 20px; color: #000; }
        .header-box { border: 1px solid #000; padding: 10px; margin-bottom: 15px; }
        .header-box p { margin: 3px 0; }
        .section-title { background-color: #e5e7eb; padding: 5px; font-weight: bold; text-align: center; border: 1px solid #000; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #f3f4f6; font-size: 10px; }
    </style>
</head>
<body>
    @foreach($proyectos as $index => $proyecto)
        {{-- Forzamos salto de página por cada proyecto excepto el primero --}}
        <div class="{{ $index > 0 ? 'page-break' : '' }}">
            <h1>Informe General de los Proyectos de Investigación {{ date('Y') }}</h1>
            
            <div class="header-box">
                <p><strong>Datos del Investigador</strong></p>
                <p>Responsable: {{ $user->name }} | Código: {{ $user->email }}</p>
                <p>Departamento: 
                    @php
                        $depto = 'No registrado';
                        if($datosLaborales) {
                            $ans = $datosLaborales->answers->first(fn($a) => stripos($a->question->label, 'Departamento') !== false);
                            if($ans) $depto = $ans->value;
                        }
                        echo $depto;
                    @endphp
                </p>
            </div>

            {{-- Datos del Proyecto --}}
            <table>
                <tbody>
                    @php
                        // Mapeo dinámico de respuestas del proyecto
                        $respuestasProyecto = $proyecto->answers->keyBy(fn($a) => mb_strtolower(trim($a->question->label)));
                    @endphp
                    <tr>
                        <td colspan="2"><strong>Titulo:</strong> {{ $respuestasProyecto['título del proyecto']->value ?? '---' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Fecha inicio:</strong> {{ $respuestasProyecto['fecha inicio']->value ?? '---' }}</td>
                        <td><strong>Fecha fin:</strong> {{ $respuestasProyecto['fecha de término']->value ?? '---' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tipo de proyecto:</strong> {{ $respuestasProyecto['tipo de proyecto']->value ?? '---' }}</td>
                        <td><strong>Clasificación del recurso:</strong> {{ $respuestasProyecto['clasificación del recurso']->value ?? '---' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Monto de apoyo UDG:</strong> {{ $respuestasProyecto['monto de apoyo udg']->value ?? '0' }}</td>
                        <td><strong>Monto de apoyos externos:</strong> {{ $respuestasProyecto['monto de apoyos externos']->value ?? '0' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Área de conocimiento:</strong> {{ $respuestasProyecto['área de conocimiento']->value ?? '---' }}</td>
                        <td><strong>Estudiantes de pregrado:</strong> {{ $respuestasProyecto['cuenta con estudiantes de pregrado en algún proyecto de investigación']->value ?? '---' }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="section-title">INFORME DE IMPACTO</div>

            {{-- Respuestas del Sub-formulario de impacto (Hijos del entry principal) --}}
            @if($proyecto->children->count() > 0)
                @foreach($proyecto->children as $child)
                    <table>
                        <tbody>
                            @foreach($child->answers->sortBy(fn($a) => $a->question->sort_order) as $ans)
                                @if($ans->question->type !== 'file')
                                    <tr>
                                        <th style="width:30%">{{ $ans->question->label }}</th>
                                        <td>
                                            @php
                                                $val = json_decode($ans->value, true);
                                                echo (json_last_error() === JSON_ERROR_NONE && is_array($val)) 
                                                    ? htmlspecialchars(implode(', ', $val)) 
                                                    : nl2br(htmlspecialchars($ans->value ?? 'N/A'));
                                            @endphp
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @else
                <p style="text-align:center; color:#666;">No hay información de impacto registrada para este proyecto.</p>
            @endif
        </div>
    @endforeach
</body>
</html>