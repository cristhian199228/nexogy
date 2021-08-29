<br>
<table>
    <thead>
    <tr>
        <th></th>
        <th height="100px" colspan="3"></th>
        <th width="2000px" style="text-align:center;" colspan="15"></th>
    </tr>
    <tr>
        <th></th>
        <th style="text-align:center;">FECHA</th>
        <th style="text-align:center;" width="20px">SEDE</th>
        <th style="text-align:center;" width="20px">ESTACION</th>
        <th style="text-align:center;" width="60px">NOMBRES</th>
        <th style="text-align:center;" width="20px">TIPO DOCUMENTO</th>
        <th style="text-align:center;" width="20px">NUMERO DOCUMENTO</th>
        <th style="text-align:center;" width="20px">PUESTO</th>
        <th style="text-align:center;" width="20px">TURNO</th>
        <th style="text-align:center;" width="20px">ROL</th>
        <th style="text-align:center;" width="10px">EDAD</th>
        <th style="text-align:center;" width="60px">DIRECCION</th>
        <th style="text-align:center;" width="20px">CELULAR</th>
        <th style="text-align:center;" width="20px">CORREO</th>
        <th style="text-align:center;" width="40px">EMPRESA</th>
        <th style="text-align:center;" width="20px">TEMPERATURA</th>
        <th style="text-align:center;" width="80px">DATOS CLÍNICOS</th>
        <th style="text-align:center;" width="80px">ANTECEDENTES EPIDEMIOLÓGICOS</th>
        <th style="text-align:center;" width="20px">PRS</th>
        <th style="text-align:center;" width="20px">PCR</th>
        <th style="text-align:center;" width="20px">AG</th>
    </tr>
    </thead>
    <tbody>

    @foreach($fichas as $ficha)
        <tr>
            <td></td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->fecha }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->Estacion->Sede->descripcion }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->Estacion->nom_estacion }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->PacienteIsos->nom_completo }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->PacienteIsos->tipo_documento }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->PacienteIsos->numero_documento }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->PacienteIsos->puesto }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->turno }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->rol }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->PacienteIsos->edad }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->PacienteIsos->direccion }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->PacienteIsos->celular }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ strtolower($ficha->PacienteIsos->correo) }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->PacienteIsos->Empresa ? $ficha->PacienteIsos->Empresa->descripcion : '' }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ count($ficha->Temperatura) > 0 ? $ficha->Temperatura[0]->valortemp : '' }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ count($ficha->DatosClinicos) > 0 ? $ficha->DatosClinicos[0]->sintomas_str : '' }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ count($ficha->AntecedentesEp) > 0 ? $ficha->AntecedentesEp[0]->ant_str : '' }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ count($ficha->PruebaSerologica) > 0 ? $ficha->PruebaSerologica[0]->resultado : '' }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ $ficha->PcrPruebaMolecular ? $ficha->PcrPruebaMolecular->resultado : '' }}</td>
            <td style=" border: 2px solid black;text-align:center;">{{ count($ficha->pruebaAntigena) > 0 ? $ficha->pruebaAntigena[0]->resultado : '' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
