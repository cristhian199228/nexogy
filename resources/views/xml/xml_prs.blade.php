<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>
<Clinicas>
    <Clinica Nombre="SERVICIOS MEDICOS INTEGRADOS S.R.L">
    @foreach ($fichas as $ficha)
        <Resultado>
            <DocumentType>{{ $ficha->PacienteIsos['tipo_documento'] }}</DocumentType>
            <DocumentNumber>{{ $ficha->PacienteIsos['numero_documento'] }}</DocumentNumber>
            <Proceso>{{ $ficha->proceso }}</Proceso>
            <Test>Estado</Test>
            <Valor>{{ $ficha->PruebaSerologica[0]['resultado'] }}</Valor>
            <FechaHora>{{ $ficha->fecha }} {{ $ficha->hora }}</FechaHora>
            <Dias_bloqueo>{{ $ficha->PruebaSerologica[0]['dias_bloqueo'] }}</Dias_bloqueo>
        </Resultado>
        <Resultado>
            <DocumentType>{{ $ficha->PacienteIsos['tipo_documento'] }}</DocumentType>
            <DocumentNumber>{{ $ficha->PacienteIsos['numero_documento'] }}</DocumentNumber>
            <Proceso>{{ $ficha->proceso }}</Proceso>
            <Test>Sintomas</Test>
            <Valor>{{  count($ficha->DatosClinicos) > 0 ? 1 : 0 }}</Valor>
            <FechaHora>{{ $ficha->fecha }} {{ $ficha->hora }}</FechaHora>
            <Dias_bloqueo>{{ count($ficha->DatosClinicos) > 0 ? 6 : 0 }}</Dias_bloqueo>
        </Resultado>
        <Resultado>
            <DocumentType>{{ $ficha->PacienteIsos['tipo_documento'] }}</DocumentType>
            <DocumentNumber>{{ $ficha->PacienteIsos['numero_documento'] }}</DocumentNumber>
            <Proceso>{{ $ficha->proceso }}</Proceso>
            <Test>Antecedentes</Test>
            <Valor>{{  count($ficha->AntecedentesEp) > 0 ? 1 : 0 }}</Valor>
            <FechaHora>{{ $ficha->fecha }} {{ $ficha->hora }}</FechaHora>
            <Dias_bloqueo>{{ count($ficha->AntecedentesEp) > 0 ? 13 : 0 }}</Dias_bloqueo>
        </Resultado>
        <Resultado>
            <DocumentType>{{ $ficha->PacienteIsos['tipo_documento'] }}</DocumentType>
            <DocumentNumber>{{ $ficha->PacienteIsos['numero_documento'] }}</DocumentNumber>
            <Proceso>{{ $ficha->proceso }}</Proceso>
            <Test>Opatologias</Test>
            <Valor>0</Valor>
            <FechaHora>{{ $ficha->fecha }} {{ $ficha->hora }}</FechaHora>
            <Dias_bloqueo>0</Dias_bloqueo>
        </Resultado>
    @endforeach
    </Clinica>
</Clinicas>
