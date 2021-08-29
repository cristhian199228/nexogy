<br>
<table>
    <thead>
    <tr>
        <th>N°</th>
        <th>FECHA</th>
        <th>TIPO DOCUMENTO</th>
        <th>NUMERO DOCUMENTO</th>
        <th>NOMBRES Y APELLIDOS</th>
        <th>RESULTADO PRS</th>
        <th>HORA ENVIO</th>
        <th>HORA DE CONSULTA</th>
        <th>RESULTADO PCR</th>
        <th>HORA ENVIO</th>
        <th>HORA DE CONSULTA</th>
    </tr>
    </thead>
    <tbody>

    @foreach($fichas as $ficha)
        <tr>
            <td>{{ $ficha->contador }}</td>
            <td>{{ $ficha->created_at->format('d/m/Y') }}</td>
            <td>{{ $ficha->PacienteIsos->tipo_doc }}</td>
            <td>{{ $ficha->PacienteIsos->numero_documento }}</td>
            <td>{{ $ficha->PacienteIsos->full_name }}</td>
            @if(count($ficha->PruebaSerologica) > 0)
                <td>{{ $ficha->PruebaSerologica[0]->resultado }}</td>
                @if(count($ficha->PruebaSerologica[0]->EnvioWP) > 0)
                    <td>{{ $ficha->PruebaSerologica[0]->EnvioWP[0]->created_at->format('H:i') }}</td>
                    @if(count($ficha->PruebaSerologica[0]->EnvioWP[0]->consulta) > 0)
                        <td>{{ $ficha->PruebaSerologica[0]->EnvioWP[0]->consulta[0]->created_at->format('H:i') }}</td>
                    @else
                        <td></td>
                    @endif
                @else
                    <td></td>
                @endif
            @else
                <td></td>
            @endif
            @if($ficha->PcrPruebaMolecular)
                <td>{{ $ficha->PcrPruebaMolecular->resultado }}</td>
                @if($ficha->PcrPruebaMolecular->EnvioWpPcr)
                    <td>{{ $ficha->PcrPruebaMolecular->EnvioWpPcr->created_at->format('H:i') }}</td>
                    @if(count($ficha->PcrPruebaMolecular->EnvioWpPcr->consulta) > 0)
                        <td>{{ $ficha->PcrPruebaMolecular->EnvioWpPcr->consulta[0]->created_at->format('H:i') }}</td>
                    @else
                        <td></td>
                    @endif
                @else
                    <td></td>
                @endif
            @else
                <td></td>
            @endif
        </tr>
    @endforeach
    </tbody>
</table>
