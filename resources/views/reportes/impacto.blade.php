@extends('reportes.layout')
@section('title', 'Informe de Impacto')

@section('content')
    @foreach($proyectos as $index => $proyecto)
        <div class="{{ $index > 0 ? 'page-break' : '' }}">
            <h1 class="report-title">Informe General de Proyectos de Investigación {{ date('Y') }}</h1>
            
            <div class="header-box">
                <p><strong>Datos del Investigador</strong></p>
                <p>Responsable: {{ $user->name }} | Código: {{ $user->email }}</p>
                <p>Departamento: 
                    @php
                        $depto = 'No registrado';
                        if($datosLaborales) {
                            $ans = $datosLaborales->answers->first(fn($a) => stripos($a->question->label, 'Departamento') !== false);
                            if(!$ans && $datosLaborales->children) {
                                foreach($datosLaborales->children as $child) {
                                    $ans = $child->answers->first(fn($a) => stripos($a->question->label, 'Departamento') !== false);
                                    if($ans) break;
                                }
                            }
                            if($ans) $depto = $ans->formatted_value;
                        }
                        echo $depto;
                    @endphp
                </p>
            </div>

            <table class="pdf-table">
                <tbody>
                    @php $res = $proyecto->answers->keyBy(fn($a) => mb_strtolower(trim($a->question->label))); @endphp
                    <tr><td colspan="2"><strong>Titulo:</strong> {{ $res['título del proyecto']->formatted_value ?? '---' }}</td></tr>
                    <tr>
                        <td><strong>Fecha inicio:</strong> {{ $res['fecha inicio']->formatted_value ?? '---' }}</td>
                        <td><strong>Fecha fin:</strong> {{ $res['fecha de término']->formatted_value ?? '---' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tipo de proyecto:</strong> {{ $res['tipo de proyecto']->formatted_value ?? '---' }}</td>
                        <td><strong>Clasificación:</strong> {{ $res['clasificación del recurso']->formatted_value ?? '---' }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="categoria-header" style="text-align: center;">INFORME DE IMPACTO</div>

            @if($proyecto->children->count() > 0)
                @foreach($proyecto->children as $child)
                    <table class="pdf-table">
                        <tbody>
                            @foreach($child->answers->sortBy(fn($a) => $a->question->sort_order) as $ans)
                                @if($ans->question->type !== 'file')
                                    <tr>
                                        <th>{{ $ans->question->label }}</th>
                                        <td>{!! $ans->formatted_value !!}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @else
                <p class="empty-state">No hay información de impacto registrada para este proyecto.</p>
            @endif
        </div>
    @endforeach
@endsection