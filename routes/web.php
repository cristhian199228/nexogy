<?php

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('salud');
});

Auth::routes();

Route::post('/api/v1/login', 'UserController@login');
Route::post('/api/v1/logout', 'UserController@logout');

//Route::middleware('auth')->prefix('/api/v1')->group(function () {
    Route::prefix('/api/v1')->group(function () {
    //MODULOS
    Route::get('/admision', 'api\v1\ModulosController@admision');
    Route::get('/controlador', 'api\v1\ModulosController@controlador');
    Route::get('/pcr', 'api\v1\ModulosController@pcr');
    Route::get('/super_pcr', 'api\v1\ModulosController@supervisorPcr');
    Route::get('/super_prs', 'api\v1\ModulosController@supervisorPrs');
   
    Route::get('/admin_pcr', 'api\v1\ModulosController@administradorPcr');
    Route::get('/rc', 'api\v1\ModulosController@responceCenter');
    Route::get('/controladorPa', 'api\v1\ModulosController@controladorPa');
    //EXTRAS PRS
    Route::put('/ps/{id}/start', 'api\v1\PruebaSerologicaController@start');
    Route::put('/pm/{id}/finish', 'api\v1\PruebaMolecularController@finish');
    Route::put('/pa/{id}/start', 'api\v1\PruebaAntigenaController@start');
    //EXTRAS FI
    Route::get('/fi2/{path}','api\v1\FotoFichaInvController@show2');
    Route::post('/fi2','api\v1\FotoFichaInvController@update2');
    Route::delete('/fi2/{id}','api\v1\FotoFichaInvController@destroy2');
    //FINALIZAR ATENCION
    Route::put('/ficha/{id}/finish', 'api\v1\FichaPacienteController@finish');
    Route::get('/pm/tipos', 'api\v1\PruebaMolecularController@showTipos');
    //REPORTES
    Route::get('/super_pcr/{inicio}/{final}', 'api\v1\ReportesController@supervisorPcr');
    Route::get('/salud/{inicio}/{final}', 'api\v1\ReportesController@salud');
    Route::get('/complejo','api\v1\ReportesController@complejo');
    Route::get('/rc/{inicio}/{final}', 'api\v1\ReportesController@responceCenter');
    //CITA MW
    Route::post('/cita_mw','api\v1\MediwebController@crearCita');
    //BUSCAR PACIENTES ADMISION
    Route::get('/search','api\v1\PacienteController@search');
    Route::post('/searchByDni','api\v1\PacienteController@buscarPorDni');
    Route::get('/searchMinsa','api\v1\PacienteController@searchMinsa');
    Route::post('/storeMinsa','api\v1\PacienteController@storeMinsa');
    Route::get('/fp/{path}','api\v1\PacienteController@showPhoto');
    Route::get('/searchEmpresa', 'api\v1\EmpresaController@search');
    Route::get('/searchDepartamento', 'api\v1\UbigeoController@searchDepartamento');
    Route::get('/searchProvincia', 'api\v1\UbigeoController@searchProvincia');
    Route::get('/searchDistrito', 'api\v1\UbigeoController@searchDistrito');
    Route::put('/ficha/{id}/turno', 'api\v1\FichaPacienteController@updateTurno');
    //Responce Center
    Route::put('/evidencia/habilitarFotos/{id}', 'api\v1\EvidenciaRcController@habilitarFotos');
    Route::post('/fc/upload', 'api\v1\FichaCamRcController@upload');
    Route::get('/fichaEp/{id_evidencia}', 'api\v1\ResponceCenterController@generarFichaEpidemiologica');
    Route::get('/fichaCam/{id_evidencia}', 'api\v1\ResponceCenterController@generarFichaCam');
    Route::get('/fichaIm/{id_evidencia}', 'api\v1\ResponceCenterController@generarIndicacionesMedicas');
    Route::get('/fev/download/{id_evidencia}', 'api\v1\ResponceCenterController@downloadImages');
    Route::post('/pm/enviar','api\v1\PruebaMolecularController@enviar');
    Route::put('/ps/updateDiasBloqueo/{id}', 'api\v1\PruebaSerologicaController@updateDiasBloqueo');

    Route::post('/paciente/celular', 'api\v1\PacienteController@updateCelular');

    //Route::post('/importar', 'api\v1\ProgramacionDiariaCvController@import');
    //Route::get('/procesar', 'api\v1\ProgramacionDiariaCvController@procesar');

    //inteligencia sanitaria
    Route::prefix('/is')->group(function () {
        Route::get('/getData', 'api\v1\InteligenciaSanitariaController@getData');
        Route::post('/pcr/guardar', 'api\v1\InteligenciaSanitariaController@guardarReevaluacionPcr');
        Route::post('/prs/guardar', 'api\v1\InteligenciaSanitariaController@guardarReevaluacionPrs');
        Route::get('/xml/{idficha_paciente}', 'api\v1\InteligenciaSanitariaController@mostrarXml');
        Route::post('/sendMail', 'api\v1\InteligenciaSanitariaController@enviarCorreo');
    });

    Route::post('/wp/ag', 'api\v1\WhatsAppController@enviarWpAntigena');
    Route::post('/wp/prueba', 'api\v1\WhatsAppController@enviarMensajePrueba');
    Route::get('/evidencia/pdf/{path}', 'api\v1\ResponceCenterController@showPdfEvidencia');

    Route::get('/fichasTemp', 'api\v1\FichaTemporalController@fichas');

    //RUTAS API
    Route::apiResources([
        'dj' => 'api\v1\DeclaracionJuradaController',
        'ci' => 'api\v1\ConsentimientoInformadoController',
        'dc' => 'api\v1\DatosClinicosController',
        'ae' => 'api\v1\AntecedentesEpidemiologicosController',
        'paciente' => 'api\v1\PacienteController',
        'estacion' => 'api\v1\EstacionController', 
        'ficha' => 'api\v1\FichaPacienteController',
        'temp' => 'api\v1\TemperaturaController',
        'a3' => 'api\v1\AnexoTresController',
        'ps' => 'api\v1\PruebaSerologicaController',
        'wp' => 'api\v1\WhatsAppController',
        'pm' => 'api\v1\PruebaMolecularController',
        'fi' => 'api\v1\FotoFichaInvController',
        'munoz' => 'api\v1\OrdernMunozController',
        'sede' => 'api\v1\SedeController',
        'empresa' => 'api\v1\EmpresaController',
        //Responce Center
        'fichaTemp' => 'api\v1\FichaTemporalController',
        'fotoPcr' => 'api\v1\PcrFotoMuestraController',
        'pa' => 'api\v1\PruebaAntigenaController',
        'firma' => 'api\v1\FirmaController'
    ]);
});

Route::prefix('/api/v1')->group(function () {
    Route::post('/constancia', 'ConstanciaController@buscarRegistros');
    Route::post('/solicitarConstancia', 'ConstanciaController@solicitarConstancia');
    Route::post('/validarDatos', 'api\v1\ResponceCenterController@validarPaciente');
    Route::get('/salud', 'api\v1\ModulosController@salud');
    Route::apiResources([
        'paciente' => 'api\v1\PacienteController',
        'evidencia' => 'api\v1\EvidenciaRcController',
        'fe' => 'api\v1\FichaEpRcController',
        'fc' => 'api\v1\FichaCamRcController',
        'fev' => 'api\v1\FotoEvidenciaRcController',
        'cd' => 'api\v1\FichaContactoRcController',
        'im' => 'api\v1\IndicacionesMedicasController',
    ]);
});

//estadisticas
Route::get('/tiemposComplejo','DashboardController@tiemposComplejo');
Route::get('/totalBarrasComplejo/{fecha}','DashboardController@totalBarrasComplejo');
Route::get('/totalBarrasComplejoInicial/{fecha}','DashboardController@totalBarrasComplejoInicial');
Route::get('/tiemposChilina','DashboardController@tiemposChilina');

//Bot Whatsapp
Route::post('/bot','BotController@InicioBot');

Route::get('/descargarConstanciaPDF/{fichapaciente}/{edad}/', 'ConstanciaController@descargarConstancia');
Route::get('/descargarConstanciaPCR/{id}', 'ConstanciaController@descargarConstanciaPcr');
Route::get('/descargarConstanciaPRS/{id}', 'ConstanciaController@descargarConstanciaPrs');
Route::get('/descargarConstanciaAG/{id}', 'ConstanciaController@descargarConstanciaAg');
Route::get('/sticker/{id}', 'ConstanciaController@descargarStickerPrs');
Route::get('/prueba', 'ConstanciaController@pruebasBot');
Route::get('/prs', 'DashboardController@reportePrs');
Route::get('/pruebawp', 'api\v1\WhatsappController@prueba');


