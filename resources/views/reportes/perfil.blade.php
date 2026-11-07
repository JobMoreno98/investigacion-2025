@extends('reportes.layout')
@section('title', 'Perfil Académico - ' . $user->name)

@section('content')
    @php
        function getAns($entry, $keyword) {
            if(!$entry) return '---';
            $ans = $entry->answers->first(fn($a) => stripos($a->question->label, $keyword) !== false);
            if ($ans) return $ans->formatted_value;
            if ($entry->children) {
                foreach ($entry->children as $child) {
                    $childAns = $child->answers->first(fn($a) => stripos($a->question->label, $keyword) !== false);
                    if ($childAns) return $childAns->formatted_value;
                }
            }
            return '---';
        }
    @endphp

    <h1 class="report-title">Perfil Académico</h1>

    <div class="seccion-header">Datos Generales</div>
    <table class="pdf-table">
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

    <div class="seccion-header">Datos Laborales</div>
    <table class="pdf-table">
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

    @foreach ($reportData as $catData)
        <div class="categoria-header">{{ $catData['categoria']->titulo }}</div>
        
        @foreach ($catData['seccionesData'] as $secData)
            <div class="seccion-header">{{ $secData['seccion']->title }}</div>
            
            @foreach ($secData['entries'] as $entry)
                <table class="pdf-table">
                    <tbody>
                        @foreach ($entry->answers->sortBy(fn($a) => $a->question->sort_order) as $answer)
                            @if ($answer->question->type !== 'sub_form' && $answer->question->type !== 'file')
                                <tr>
                                    <th>{{ $answer->question->label }}</th>
                                    <td>{!! $answer->formatted_value !!}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                @foreach ($entry->children as $child)
                    <table class="pdf-table" style="width: 95%; margin-left: auto;">
                        <tbody>
                            @foreach ($child->answers->sortBy(fn($a) => $a->question->sort_order) as $childAnswer)
                                @if ($childAnswer->question->type !== 'file')
                                <tr>
                                    <th style="background-color: #f8fafc;">{{ $childAnswer->question->label }}</th>
                                    <td>{!! $childAnswer->formatted_value !!}</td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @endforeach
        @endforeach
    @endforeach
@endsection