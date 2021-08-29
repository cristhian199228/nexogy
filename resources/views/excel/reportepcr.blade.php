<br>
<table>
    <thead>
    <tr>
        <th></th>
        <th height="100px" colspan="3"></th>

        <th width="2000px" style="text-align:center;" colspan="10">
            HOJA DE REGISTRO DE ASISTENCIA PRUEBAS MOLECULARES - SARS COV 2 (COVID-19)
        </th>
    </tr>
    <tr>
        <th></th>

        <th style="text-align:center;">FECHA</th>
        <th style="text-align:center;" width="20px">NOMBRES</th>
        <th style="text-align:center;" width="20px">NUMERO DE DOCUMENTO</th>
        <th style="text-align:center;" width="20px">TRANSACTION ID</th>
        <th style="text-align:center;" width="20px">INICIO</th>
        <th style="text-align:center;" width="20px">FIN</th>
        <th style="text-align:center;" width="20px">ESTACION</th>
        <th style="text-align:center;" width="20px">SEDE</th>
        <th style="text-align:center;" width="20px">EMPRESA</th>
        <th style="text-align:center;" width="20px">ROL</th>
        <th style="text-align:center;" width="20px">TURNO</th>
        <th style="text-align:center;" width="20px">FOTO FICHA INV.</th>
        <th style="text-align:center;" width="20px">RESULTADO</th>
    </tr>
    </thead>
    <tbody>

    @foreach($fichas as $f)
        <tr>
            <td></td>
            <td style=" border: 2px solid black;text-align:center;">{{ $f['fecha'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $f->PacienteIsos['nom_completo'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $f->PacienteIsos['numero_documento'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $f->PcrPruebaMolecular->PcrEnvioMunoz ? $f->PcrPruebaMolecular->PcrEnvioMunoz['transaction_id'] : ''}}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $f->PcrPruebaMolecular['hora_inicio'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $f->PcrPruebaMolecular['hora_fin']}}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $f->Estacion['nom_estacion'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $f->Estacion->Sede['descripcion'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $f->PacienteIsos->Empresa ? $f->PacienteIsos->Empresa['descripcion'] : ''}}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $f['rol'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $f['turno'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $f['estado_fotos'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $f->PcrPruebaMolecular['resultado'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
