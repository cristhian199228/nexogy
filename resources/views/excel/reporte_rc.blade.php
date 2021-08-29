<br>
<table>
    <thead>
    <tr>
        <th></th>
        <th height="100px" colspan="3"></th>

        <th width="2000px" style="text-align:center;" colspan="6">
            HOJA DE REGISTRO SERVICIO RESPONCE CENTER - EVIDENCIAS COVID 19
        </th>
    </tr>
    <tr>
        <th></th>
        <th style="text-align:center;">FECHA</th>
        <th style="text-align:center;">ESTACION</th>
        <th style="text-align:center;" width="20px">USUARIO</th>
        <th style="text-align:center;" width="20px">NOMBRE PACIENTE</th>
        <th style="text-align:center;" width="20px">NUMERO DE DOCUMENTO</th>
        <th style="text-align:center;" width="20px">N° REGISTRO</th>
        <th style="text-align:center;" width="20px">EMPRESA</th>
        <th style="text-align:center;" width="20px">DOCUMENTOS</th>
        <th style="text-align:center;" width="20px">FICHA EP.</th>
        <th style="text-align:center;" width="20px">CONTACTOS DIRECTOS</th>
        <th style="text-align:center;" width="20px">FICHA CAM</th>
        <th style="text-align:center;" width="20px">ESTADO</th>
    </tr>
    </thead>
    <tbody>

    @foreach($evidencias as $ev)
        <tr>
            <td></td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ev['fecha'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ev->estacion['nom_estacion'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ev['user'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ev->paciente->nom_completo }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ev->paciente->numero_documento }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ev->paciente->nro_registro }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ev->paciente->Empresa->descripcion }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ev->puede_subir_fotos ? count($ev->fotos) > 0 ? 'SUBIO ' . count($ev->fotos) .' DOCUMENTOS' : 'NO SUBIO DOCUMENTOS' : '' }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ ($ev->fichaEp) ? ($ev->fichaEp->firma) ? 'FICHA FIRMADA' : 'FICHA NO FIRMADA' : ''  }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ ($ev->fichaEp) ? (count($ev->fichaEp->contactos) > 0) ? count($ev->fichaEp->contactos) . ' CONTACTOS DIRECTOS' : 'SIN CONTACTOS DIRECTOS' : '' }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ ($ev->fichaCam) ? ($ev->fichaCam->firma) ? 'FICHA FIRMADA' : 'FICHA NO FIRMADA' : '' }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ev->estado ? 'COMPLETADO' : 'EN PROCESO'  }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
