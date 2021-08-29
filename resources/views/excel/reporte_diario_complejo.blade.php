<table>
    <thead>
    <tr>
        <td colspan="3">
           Fecha: {{ date('d/m/Y') }}
        </td>
    </tr>
    <tr>
        <th>DESCRIPCION</th>
        <th>PERSONAL</th>
        <th>PRE-EMBARQUE MEGACENTRO</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td rowspan="2">Trabajadores Valorados</td>
        <td>Cerro Verde</td>
        <td>{{ $reporte['cv']['fichas'] }}</td>
    </tr>
    <tr>
        <td>Contratistas</td>
        <td>{{ $reporte['con']['fichas'] }}</td>
    </tr>
    <tr>
        <td rowspan="2">Reactivos IGM</td>
        <td>Cerro Verde</td>
        <td>{{ $reporte['cv']['igm'] }}</td>
    </tr>
    <tr>
        <td>Contratistas</td>
        <td>{{ $reporte['con']['igm'] }}</td>
    </tr>
    <tr>
        <td rowspan="2">Reactivos IGM/IGG</td>
        <td>Cerro Verde</td>
        <td>{{ $reporte['cv']['igm_igg'] }}</td>
    </tr>
    <tr>
        <td>Contratistas</td>
        <td>{{ $reporte['con']['igm_igg'] }}</td>
    </tr>
    <tr>
        <td rowspan="2">Reactivos IGG</td>
        <td>Cerro Verde</td>
        <td>{{ $reporte['cv']['igg'] }}</td>
    </tr>
    <tr>
        <td>Contratistas</td>
        <td>{{ $reporte['con']['igg'] }}</td>
    </tr>
    <tr>
        <td rowspan="2">Sintomáticos</td>
        <td>Cerro Verde</td>
        <td>{{ $reporte['cv']['sintomaticos'] }}</td>
    </tr>
    <tr>
        <td>Contratistas</td>
        <td>{{ $reporte['con']['sintomaticos'] }}</td>
    </tr>
    <tr>
        <td rowspan="2">Epidemiológicos</td>
        <td>Cerro Verde</td>
        <td>{{ $reporte['cv']['epidemiologicos'] }}</td>
    </tr>
    <tr>
        <td>Contratistas</td>
        <td>{{ $reporte['con']['epidemiologicos'] }}</td>
    </tr>
    <tr>
        <td rowspan="2">NO REACTIVOS</td>
        <td>Cerro Verde</td>
        <td>{{ $reporte['cv']['no_reactivos'] }}</td>
    </tr>
    <tr>
        <td>Contratistas</td>
        <td>{{ $reporte['con']['no_reactivos'] }}</td>
    </tr>
    <tr>
        <td rowspan="2">Pruebas Repetidas</td>
        <td>Cerro Verde</td>
        <td>{{ $reporte['cv']['pruebas_repetidas'] }}</td>
    </tr>
    <tr>
        <td>Contratistas</td>
        <td>{{ $reporte['con']['pruebas_repetidas'] }}</td>
    </tr>
    <tr>
        <td rowspan="2">Reactivos IGM persistentes</td>
        <td>Cerro Verde</td>
        <td>{{ $reporte['cv']['persistentes'] }}</td>
    </tr>
    <tr>
        <td>Contratistas</td>
        <td>{{ $reporte['con']['persistentes'] }}</td>
    </tr>
    <tr>
        <td rowspan="2">Reactivos Recuperados</td>
        <td>Cerro Verde</td>
        <td>{{ $reporte['cv']['igg_rec'] }}</td>
    </tr>
    <tr>
        <td>Contratistas</td>
        <td>{{ $reporte['con']['igg_rec'] }}</td>
    </tr>
    <tr>
        <td rowspan="2">Reactivos Vacunados</td>
        <td>Cerro Verde</td>
        <td>{{ $reporte['cv']['igg_vac'] }}</td>
    </tr>
    <tr>
        <td>Contratistas</td>
        <td>{{ $reporte['con']['igg_vac'] }}</td>
    </tr>
    <tr>
        <td rowspan="2">Pruebas Moleculares</td>
        <td>Cerro Verde</td>
        <td>{{ $reporte['cv']['pruebas_moleculares'] }}</td>
    </tr>
    <tr>
        <td>Contratistas</td>
        <td>{{ $reporte['con']['pruebas_moleculares'] }}</td>
    </tr>
    <tr>
        <td>Personal de ISOS</td>
        <td>ISOS</td>
        <td>{{ $reporte['isos']['fichas'] }}</td>
    </tr>
    </tbody>
</table>
