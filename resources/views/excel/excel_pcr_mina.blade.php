<br>
<table>
    <thead>
    <tr>
        <th></th>
        <th height="100px" colspan="3"></th>

        <th width="500px" style="text-align:center;" colspan="3">
            HOJA DE REGISTRO DE RESULTADOS PRUEBAS MOLECULARES - SARS COV 2 (COVID-19)
        </th>
    </tr>
    <tr>
        <th></th>

        <th style="text-align:center;">FECHA</th>
        <th style="text-align:center;" width="20px">NOMBRES</th>
        <th style="text-align:center;" width="20px">NRO DOCUMENTO</th>
        <th style="text-align:center;" width="20px">SEDE</th>
        <th style="text-align:center;" width="20px">ESTACION</th>
        <th style="text-align:center;" width="20px">HORARIO ATENCION POSTA</th>
        <th style="text-align:center;" width="20px">EMPRESA</th>
        <th style="text-align:center;" width="20px">ROL</th>
        <th style="text-align:center;" width="20px">RESULTADO</th>
        <th style="text-align:center;" width="20px">FECHA FIN AISLAMIENTO</th>
    </tr>
    </thead>
    <tbody>
    @foreach($fichas as $ficha)
        <tr>
            <td></td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->PcrPruebaMolecular['fecha'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->PacienteIsos['full_name'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->PacienteIsos['numero_documento'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->Estacion->Sede['descripcion'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->Estacion['nombre_estacion'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha['horario_atencion'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->PacienteIsos->Empresa['descripcion'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha['rol'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->PcrPruebaMolecular['resultado'] }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha['fecha_fin_aislamiento'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
