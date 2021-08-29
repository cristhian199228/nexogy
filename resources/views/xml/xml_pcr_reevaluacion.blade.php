<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>
<Clinicas>
    <Clinica Nombre="SERVICIOS MEDICOS INTEGRADOS S.R.L">
        @if($ficha->PcrPruebaMolecular)
            <Resultado>
                <DocumentType>{{ $ficha->PacienteIsos['tipo_documento'] }}</DocumentType>
                <DocumentNumber>{{ $ficha->PacienteIsos['numero_documento'] }}</DocumentNumber>
                <Proceso>{{ $ficha->proceso }}</Proceso>
                <Test>PCR</Test>
                <Valor>{{ count($ficha->PcrPruebaMolecular->reevaluacion) > 0 ? 2 :
                            ($ficha->PcrPruebaMolecular['resultado'] ? 1 : 0) }}</Valor>
                <FechaHora>{{ $ficha->fecha_hora }}</FechaHora>
                <Dias_bloqueo>{{ count($ficha->PcrPruebaMolecular->reevaluacion) > 0 ?
                            $ficha->PcrPruebaMolecular->reevaluacion[0]->dias_bloqueo :
                            ($ficha->PcrPruebaMolecular['resultado']  ? 13 : 0) }}</Dias_bloqueo>
            </Resultado>
        @endif
        @if(count($ficha->PruebaSerologica) > 0)
            <Resultado>
                <DocumentType>{{ $ficha->PacienteIsos['tipo_documento'] }}</DocumentType>
                <DocumentNumber>{{ $ficha->PacienteIsos['numero_documento'] }}</DocumentNumber>
                <Proceso>{{ $ficha->proceso }}</Proceso>
                <Test>Estado</Test>
                <Valor>{{ count($ficha->PruebaSerologica[0]->reevaluacion) > 0 ? 'RNRE' :
                            $ficha->PruebaSerologica[0]->resultado }}</Valor>
                <FechaHora>{{ $ficha->fecha_hora }}</FechaHora>
                <Dias_bloqueo>{{ count($ficha->PruebaSerologica[0]->reevaluacion) > 0 ?
                            $ficha->PruebaSerologica[0]->reevaluacion[0]->dias_bloqueo :
                            $ficha->PruebaSerologica[0]->dias_bloqueo }}</Dias_bloqueo>
            </Resultado>
        @endif
        <Resultado>
            <DocumentType>{{ $ficha->PacienteIsos['tipo_documento'] }}</DocumentType>
            <DocumentNumber>{{ $ficha->PacienteIsos['numero_documento'] }}</DocumentNumber>
            <Proceso>{{ $ficha->proceso }}</Proceso>
            <Test>Sintomas</Test>
            <Valor>{{  count($ficha->DatosClinicos) > 0 ? 1 : 0 }}</Valor>
            <FechaHora>{{ $ficha->fecha_hora }}</FechaHora>
            <Dias_bloqueo>{{ count($ficha->DatosClinicos) > 0 ? 6 : 0 }}</Dias_bloqueo>
        </Resultado>
        <Resultado>
            <DocumentType>{{ $ficha->PacienteIsos['tipo_documento'] }}</DocumentType>
            <DocumentNumber>{{ $ficha->PacienteIsos['numero_documento'] }}</DocumentNumber>
            <Proceso>{{ $ficha->proceso }}</Proceso>
            <Test>Antecedentes</Test>
            <Valor>{{  count($ficha->AntecedentesEp) > 0 ? 1 : 0 }}</Valor>
            <FechaHora>{{ $ficha->fecha_hora }}</FechaHora>
            <Dias_bloqueo>{{ count($ficha->AntecedentesEp) > 0 ? 13 : 0 }}</Dias_bloqueo>
        </Resultado>
        <Resultado>
            <DocumentType>{{ $ficha->PacienteIsos['tipo_documento'] }}</DocumentType>
            <DocumentNumber>{{ $ficha->PacienteIsos['numero_documento'] }}</DocumentNumber>
            <Proceso>{{ $ficha->proceso }}</Proceso>
            <Test>Opatologias</Test>
            <Valor>0</Valor>
            <FechaHora>{{ $ficha->fecha_hora }}</FechaHora>
            <Dias_bloqueo>0</Dias_bloqueo>
        </Resultado>
    </Clinica>
</Clinicas>
