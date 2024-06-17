<?php

use App\Http\Controllers\AnuncioController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\CarrerasController;
use App\Http\Controllers\CucsController;
use App\Http\Controllers\EstudiantesController;
use App\Http\Controllers\FaseUnoController;
use App\Http\Controllers\FaseDosController;
use App\Http\Controllers\FaseTresController;
use App\Http\Controllers\FaseCuatroController;
use App\Http\Controllers\FaseCincoController;
use App\Http\Controllers\FaseFinalController;
use App\Http\Controllers\ForaneoController;
use App\Http\Controllers\GruposController;
use App\Http\Controllers\ConsejerosController;
use App\Http\Controllers\EscolaresController;
use App\Http\Controllers\FacilitadoresController;
use App\Http\Controllers\AdministrativosController;
use App\Http\Controllers\CodigoPostalController;
use App\Http\Controllers\ColoniaController;
use App\Http\Controllers\CoordinadoresController;
use App\Http\Controllers\EstadosController;
use App\Http\Controllers\MunicipiosController;
use App\Http\Controllers\PeriodosController;
use App\Http\Controllers\MateriasController;
use App\Http\Controllers\TipoSangreController;
use App\Http\Controllers\PueblosIndigenasController;
use App\Http\Controllers\LenguasIndigenasController;
use App\Http\Controllers\DiasController;
use App\Http\Controllers\ClasesController;
use App\Http\Controllers\NacionalidadesController;
use App\Http\Controllers\AreasConsejeriaController;
use App\Http\Controllers\EstadoDocumentacionController;
use App\Http\Controllers\ServicioController;
use App\Models\Carreras;    
use App\Models\Estudiantes;
use App\Models\Facilitadores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::post('/login', [AuthController::class, 'login']);
Route::get('/descarga/{name}', [FaseUnoController::class, 'downloadFile'])->name('download');

Route::get('/archivos/{nombreArchivo}', [FaseUnoController::class, 'getArchivo']);

Route::post('/olvido-contra', [AuthController::class, 'olvidoContrasena']);

Route::get('/reset-password/{token}', function (string $token) {
    return view('reset-password', ['token' => $token]);
})->name('password.reset');

Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');




Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/logout', [AuthController::class, 'logout']);
});
// Route::middleware('auth:api')->group(function () {
//     //Aqui se meterian las rutas para que solo se puedan acceder cuando se envie el token
//     Route::apiResource('cucs',CucsController::class);
// });

Route::apiResource('estados', EstadosController::class);
Route::apiResource('municipios', MunicipiosController::class);
Route::apiResource('cp', CodigoPostalController::class);
Route::apiResource('colonias', ColoniaController::class);
Route::get('/estados/{estadoId}/municipios', [MunicipiosController::class, 'municipiosPorEstado']);
Route::get('/cp/{cpId}/colonias/municipio/estado', [CodigoPostalController::class, 'coloniasMunicipioEstadoDeCodigoP']);

Route::apiResource('administrativos', AdministrativosController::class);
Route::apiResource('coordinadores', CoordinadoresController::class);

Route::middleware(['auth:sanctum', 'checkDefaultRole'])->group(function () {
    Route::get('/cucs', [CucsController::class, 'index']);
    Route::get('/cucs/{id}', [CucsController::class, 'show']);
    Route::get('/cucs/{cucId}/consejeros', [CucsController::class, 'consejerosPorCuc']);//pa consultar consejeros por cuc rol admin y coor
    Route::get('/cucs/{cucId}/escolares', [CucsController::class, 'escolaresPorCuc']);
    Route::get('/cucs/{cucId}/carreras', [CucsController::class, 'carrerasPorCuc']);//pa consultar carreras en rol de coordi
    Route::get('/cucs/{cucId}/facilitadores', [CucsController::class, 'facilitadoresPorCuc']);//pa consultar facilitadores en rol de coordi
    Route::get('/cucs/{cucId}/grupos', [CucsController::class, 'gruposPorCuc']);
    

    Route::get('/carreras', [CarrerasController::class, 'index']);
    Route::get('/carreras/{id}', [CarrerasController::class, 'show']);
    Route::get('/carreras/{claveCarrera}/grupos', [CarrerasController::class, 'gruposPorCarrera']);
    Route::get('/carreras/{claveCarrera}/cucs/{claveCuc}/grupos', [CarrerasController::class, 'gruposPorCarreraPorCuc']);
    Route::get('/carreras/{carreraId}/materias', [CarrerasController::class, 'materiasPorCarrera']);

    Route::get('/grupos', [GruposController::class, 'index']); 
    Route::get('/grupos/{id}', [GruposController::class, 'show']);

    Route::get('/consejeros', [ConsejerosController::class, 'index']);
    Route::get('/consejeros/{id}', [ConsejerosController::class, 'show']);

    Route::get('/escolares', [EscolaresController::class, 'index']);
    Route::get('/escolares/{id}', [EscolaresController::class, 'show']);

    Route::get('/estudiantes', [EstudiantesController::class, 'index']);
    Route::get('/estudiante/{id}', [EstudiantesController::class, 'showEstudiante']);
    Route::get('/foraneo/{id}', [ForaneoController::class, 'show']);
    Route::get('/estudiantes/{id}', [EstudiantesController::class, 'show']);
    

    Route::get('/periodos', [PeriodosController::class, 'index']);
    Route::get('/periodos/{id}', [PeriodosController::class, 'show']);
    Route::get('/periodos/mes/actual', [PeriodosController::class, 'periodosAnioActual']);
    Route::get('/carrera/{claveCarrera}/periodos', [PeriodosController::class, 'periodosPorCarrera']);
    Route::get('/carrera/{claveCarrera}/periodos/mes/actual', [PeriodosController::class, 'periodosAnioActualporCarrera']);
    Route::get('/grupos/{grupoId}/estudiantes', [GruposController::class, 'estudiantesPorGrupo']);

    Route::get('/materias', [MateriasController::class, 'index']);
    Route::get('/materias/{id}', [MateriasController::class, 'show']);

    Route::get('/clases', [ClasesController::class, 'index']);
    Route::get('/clases/{id}', [ClasesController::class, 'show']);
    
    Route::get('/facilitadores', [FacilitadoresController::class, 'index']);
    Route::get('/facilitadores/{id}', [FacilitadoresController::class, 'show']);
    Route::get('/cucs/carreras/carreritas', [CucsController::class, 'pruebaCarrerasPorCuc']);//comparten escolares y consejeros
    Route::get('/obc/estudiantes/estudiantess', [CucsController::class, 'estudiantesDeCUC']);

    

    Route::get('/obc/estudiantes/estudiantess/prestadores', [CucsController::class, 'estudiantesDeCUCServicio']);

    Route::get('/obc/estudiantes/estudiantess/candidatos', [CucsController::class, 'candidatosDeCUCServicio']);

    //bajas
     Route::get('/obc/estudiantes/estudiantess/prestadores/bajas', [CucsController::class, 'bajasDeCUCServicio']);



    Route::patch('obc/estudiantes/matricula/activar/{id}', [CucsController::class, 'activarServicio']);
    Route::patch('obc/estudiantes/matricula/cancelar/{id}', [CucsController::class, 'cancelarServicio']);

   

    Route::patch('estudiante/estatus/revisado/{id}', [CucsController::class, 'revisadoEstatus']);
    Route::get('/estado/estudiante/envio/{id}',[EstudiantesController::class,'obtenerEnvioInfo']);
    Route::get('/estado/estudiante/servicio/{id}',[EstudiantesController::class,'obtenerServicioInfo']);


    Route::get('/obe/clases/clasess', [CucsController::class, 'clasesDeCuc']);
    // Route::get('/cucs/{cucId}/facilitadores', [CucsController::class, 'facilitadoresPorCuc']); 

    Route::get('/tiposangre', [TipoSangreController::class, 'index']);
    Route::get('/lenguasindigenas', [LenguasIndigenasController::class, 'index']);
    Route::get('/pueblosindigenas', [PueblosIndigenasController::class, 'index']);
    Route::get('/dias', [DiasController::class, 'index']);
    Route::get('/areasconsejerias', [AreasConsejeriaController::class,'index']);

    Route::get('/carreras/{claveCarrera}/materias/{claveMateria}/periodos/{idPeriodo}/clases/facilitador-nocalificado', [ClasesController::class,'clasesPorCarreraPorMateria']);
    Route::get('/carreras/{claveCarrera}/materias/{claveMateria}/periodos/{idPeriodo}/clases/facilitador-calificado', [ClasesController::class,'clasesPorCarreraPorMateriaDeEscolar']);
    Route::get('/carrera/{claveCarrera}/clases/{claveClase}/materia/{claveMateria}/estudiantes', [ClasesController::class,'estudiantesPorCarreraYClase']);
    Route::get('/carrera/clases/{claveClase}/estudiantes', [ClasesController::class,'matriculaEstudiantesPorClase']);
    Route::get('/carrera/{claveCarrera}/materia/{claveMateria}/periodo/{idPeriodo}/clases', [ClasesController::class,'clasesGenerales']);
    Route::get('/clase/{claveClase}/estudiantes', [ClasesController::class,'estudiantesDeClaseEnGeneral']);
    Route::get('/nacionalidades', [NacionalidadesController::class,'index']);
    Route::get('/documentaciones', [EstadoDocumentacionController::class,'index']);
    Route::get('/grupo/{claveGrupo}/periodo/{clavePeriodo}/materias/estudiantes', [ClasesController::class,'clasesDeEstudiantesPorGrupoPorPeriodo']);
    Route::get('/grupo/{claveGrupo}/periodo/{clavePeriodo}/aprobado/inicio', [GruposController::class,'actualizarestadoinicio']);
    Route::get('/grupo/{claveGrupo}/periodo/{clavePeriodo}/aprobado/final', [GruposController::class,'actualizarestadofinal']);
    Route::get('/grupo/{claveGrupo}/periodo/{clavePeriodo}/estados/inicio-final/aprobados', [GruposController::class,'aprobadoInicioYFinal']);
    Route::get('/cuc/{claveCuc}/carrera/{claveCarrera}/periodo/{clavePeriodo}/grupos/aprobados', [GruposController::class,'gruposPorCarreraPorCucPorPeriodo']);

    //escolar
    Route::get('/por-cuc/estudiantes/total/numero', [EstudiantesController::class,'numEstudiantesPorCuc']);
    Route::get('/por-cuc/programa/{claveCarrera}/estudiantes/total/numero', [EstudiantesController::class,'totalEstudiantesPorCarreraPorCuc']);
    Route::get('/por-cuc/programas/total/numero', [CarrerasController::class,'totalCarrerasPorCuc']);
    Route::get('/por-cuc/programa/{claveCarrera}/grupos/total/numero', [CarrerasController::class,'totalGruposPorCarreraPorCuc']);
    Route::get('/coordi-escolar/estudiantes/{matriculaEstudiante}/clases/generales/total', [ClasesController::class, 'TotalClasesDeEstudiantesGeneral']);
    Route::get('/por-cuc/programa/estudiantes/total/numero', [EstudiantesController::class,'totalEstudiantesCarrerasPorCuc']);

    //coordinador
    Route::get('/cucs/estudiantes/total/numero', [EstudiantesController::class,'totEstudiantes']);
    Route::get('/coordinador/cuc/{claveCuc}/estudiantes/total/numero', [EstudiantesController::class,'numEstudiantesPorCucCoordinador']);
    Route::get('/coordinador/cuc/{claveCuc}/programa/{claveCarrera}/estudiantes/total/numero', [EstudiantesController::class,'totalEstudiantesPorCarreraPorCucCoordinador']);
    Route::get('/cucs/programa/{claveCarrera}/estudiantes/total/numero', [EstudiantesController::class,'totalEstudiantesPorCarreraCucs']);
    Route::get('/coordinador/cucs/programas/total/numero', [CarrerasController::class,'totalCarrerasCoordinador']);
    Route::get('/coordinador/cuc/{claveCuc}/programas/total/numero', [CarrerasController::class,'totalCarrerasPorCucCoordinador']);
    Route::get('/coordinador/cuc/{claveCuc}/programa/{claveCarrera}/grupos/total/numero', [CarrerasController::class,'totalGruposPorCarreraPorCucCoordiandor']);
    Route::get('/coordinador/programa/estudiantes/total/numero', [EstudiantesController::class,'totalEstudiantesCarreras']);


    
    Route::get('/obc/estudiantes/estudiantess/prestadores/tramite', [CucsController::class, 'estudiantesTramite']);

    Route::get('/obc/estudiantes/estudiantess/servicio/bajas', [CucsController::class, 'bajasGeneralServicio']);

    //estadisticas escolares
      //escolar
      Route::get('/por-cuc/prestadores/total/numero', [EstudiantesController::class,'numPrestadoresPorCuc']);

      //coordinador
      Route::get('/cucs/prestadores/total/numero', [EstudiantesController::class,'totPrestadores']);
});

Route::middleware(['auth:sanctum', 'checkCoordinadorRole'])->group(function () {
    Route::post('/cucs', [CucsController::class, 'store']);
    Route::delete('/cucs/{id}', [CucsController::class, 'destroy']);
    Route::put('/cucs/{id}', [CucsController::class, 'update']);

    Route::post('/periodos', [PeriodosController::class, 'store']);
    Route::delete('/periodos/{id}', [PeriodosController::class, 'destroy']);
    Route::put('/periodos/{id}', [PeriodosController::class, 'update']);

    Route::post('/carreras', [CarrerasController::class, 'store']);
    Route::delete('/carreras/{id}', [CarrerasController::class, 'destroy']);
    Route::put('/carreras/{id}', [CarrerasController::class, 'update']);

    Route::post('/materias', [MateriasController::class, 'store']);
    Route::delete('/materias/{id}', [MateriasController::class, 'destroy']);
    Route::put('/materias/{id}', [MateriasController::class, 'update']);



});

Route::middleware(['auth:sanctum', 'checkAdminRole'])->group(function () {

    Route::post('/facilitadores', [FacilitadoresController::class, 'store']);
    Route::delete('/facilitadores/{id}', [FacilitadoresController::class, 'destroy']);
    Route::put('/facilitadores/{id}', [FacilitadoresController::class, 'update']);

    Route::post('/consejeros', [ConsejerosController::class, 'store']);
    Route::delete('/consejeros/{id}', [ConsejerosController::class, 'destroy']);
    Route::put('/consejeros/{id}', [ConsejerosController::class, 'update']);

    Route::post('/escolares', [EscolaresController::class, 'store']);
    Route::delete('/escolares/{id}', [EscolaresController::class, 'destroy']);
    Route::put('/escolares/{id}', [EscolaresController::class, 'update']);
});


Route::middleware(['auth:sanctum', 'checkConsejeroRole'])->group(function () {
    // Route::post('/grupos', [GruposController::class, 'store']);
    // Route::delete('/grupos/{id}', [GruposController::class, 'destroy']);
    // Route::put('/grupos/{id}', [GruposController::class, 'update']);

    //Route::post('/estudiantes', [EstudiantesController::class, 'store']);
    //Route::delete('/estudiantes/{id}', [EstudiantesController::class, 'destroy']);
    //Route::put('/estudiantes/{id}', [EstudiantesController::class, 'update']);

    Route::post('/carreras/asociar-cuc/', [CarrerasController::class, 'asociarCuc']);
    Route::post('/facilitadores/asociar-cuc/', [FacilitadoresController::class, 'asociarCuc']);
    Route::post('/carreras/desasociar-cuc/', [CarrerasController::class, 'eliminarAsociacionCuc']);
    Route::post('/facilitadores/desasociar-cuc/', [FacilitadoresController::class, 'eliminarAsociacionCuc']);
    // Route::get('/carreras/{claveCarrera}/cucs/grupos', [CarrerasController::class, 'pruebaGruposPorCarreraPorCuc']);
    // Route::get('/cucs/grupos/grupitos', [CucsController::class, 'pruebaGruposPorCuc']);
    Route::get('/cucs/carreras/carreritass', [CucsController::class, 'pruebaCarrerasPorCuc']);
    Route::get('/consejeros/cuc/especifico', [ConsejerosController::class, 'obtenerCucDeConsejero']);//para obtener la info de la cuc del consejero
    Route::get('/cuc/consejero/facilitadores', [CucsController::class, 'facilitadoresPorCucDeConsejero']); //para obtener facilitadores de la cuc del consejero
    
    
});

Route::middleware(['auth:sanctum', 'checkEscolarRole'])->group(function () {
    Route::post('/grupos', [GruposController::class, 'store']);
    Route::delete('/grupos/{id}', [GruposController::class, 'destroy']);
    Route::put('/grupos/{id}', [GruposController::class, 'update']);
    Route::post('/estudiantes', [EstudiantesController::class, 'store']);
    Route::delete('/estudiantes/{id}', [EstudiantesController::class, 'destroy']);
    Route::put('/estudiantes/{id}', [EstudiantesController::class, 'update']);
    Route::get('/cucs/grupos/{claveGrupo}/estudiantes', [CucsController::class, 'estudiantesPorGrupoPorCUC']);
    Route::get('/cuc/escolar/facilitadores', [CucsController::class, 'facilitadoresPorCucDeEscolar']);
    Route::get('/carreras/{claveCarrera}/cucs/grupos', [CarrerasController::class, 'pruebaGruposPorCarreraPorCuc']);
    Route::get('/cucs/grupos/grupitos', [CucsController::class, 'pruebaGruposPorCuc']);
    Route::get('/cucs/carreras/carreritas', [CucsController::class, 'pruebaCarrerasPorCuc']);
    Route::get('/escolares/cuc/especifico', [EscolaresController::class, 'obtenerCucDeEscolar']);//para obtener la info de la cuc del consejero
    Route::post('/clases', [ClasesController::class, 'store']);
    Route::delete('/clases/{id}', [ClasesController::class, 'destroy']);
    Route::put('/clases/{id}', [ClasesController::class, 'update']);
    Route::post('/clases/asociar-estudiantes/', [ClasesController::class, 'asociarEstudiantes']);
    Route::post('/clases/desasociar-estudiantes/', [ClasesController::class, 'eliminarAsociacionEstudiante']);
    Route::post('/escolar/clases/estudiantes/calificacion', [ClasesController::class, 'calificarEstudiantesPorEscolar']);
    Route::get('/escolares/clases/{claveClase}/estudiantes', [ClasesController::class, 'estudiantesPorClase']);
    Route::put('/documentacion/{claveEstudiante}', [EstadoDocumentacionController::class, 'update']);
    Route::get('/lengua/regresa', [LenguasIndigenasController::class, 'regresaLengua']);
    Route::get('/pueblos/regresa', [PueblosIndigenasController::class, 'regresaPueblo']);
    Route::get('/regresa/envio/{id}',[EstudiantesController::class,'obtenerEstado']);
    Route::patch('/estado/cambio/{matricula}/{estado}', [EstudiantesController::class, 'cambiarEstatus']);  
    Route::patch('/envia/comentario/{matricula}/{comentario}', [EstudiantesController::class, 'enviarComentario']);  
    Route::get('/obten/servicio/info/{matricula}', [ServicioController::class, 'infoServicioEscolar']);

    Route::patch('/estado/social/{matricula}/{estado}', [ServicioController::class, 'cambiarEst']);  
    Route::patch('/cambio/estado/presentacion/{matricula}/{estado}', [FaseUnoController::class, 'cambiarEstado']); 
    Route::patch('/envia/comentario/social/{matricula}/{comentario}', [ServicioController::class, 'enviarComentarioSocial']);  

    Route::get('/obten/info/general/{matricula}', [ServicioController::class, 'infoGeneral']);


    //faseUno
    Route::patch('/envia/comentario/presentacion/{matricula}/{comentario}', [FaseUnoController::class, 'enviarComentarioPresentacion']);  
    Route::patch('/envia/comentario/aceptacion/{matricula}/{comentario}', [FaseUnoController::class, 'enviarComentarioAceptacion']);  
    Route::patch('/cambio/estado/aceptacion/{matricula}/{estado}', [FaseUnoController::class, 'cambiarEstadoAceptacionEscolar']); 

//faseDos
Route::patch('/cambio/estado/informe1/{matricula}/{estado}', [FaseDosController::class, 'cambiarEstatusInforme1']); 
Route::patch('/envia/comentario/informe1/{matricula}/{comentario}', [FaseDosController::class, 'enviarComentarioInforme1']);  


//faseTres
Route::patch('/cambio/estado/informe2/{matricula}/{estado}', [FaseTresController::class, 'cambiarEstatusInforme2']); 
Route::patch('/envia/comentario/informe2/{matricula}/{comentario}', [FaseTresController::class, 'enviarComentarioInforme2']);  


//faseCuatro
Route::patch('/cambio/estado/informe3/{matricula}/{estado}', [FaseCuatroController::class, 'cambiarEstatusInforme3']); 
Route::patch('/envia/comentario/informe3/{matricula}/{comentario}', [FaseCuatroController::class, 'enviarComentarioInforme3']);  



//faseCinco
Route::patch('/cambio/estado/terminacion/{matricula}/{estado}', [FaseCincoController::class, 'cambiarEstatusTerminacion']); 
Route::patch('/envia/comentario/terminacion/{matricula}/{comentario}', [FaseCincoController::class, 'enviarComentarioTerminacion']); 

//faseFinal
Route::patch('/cambio/estado/recibo/{matricula}/{estado}', [FaseFinalController::class, 'cambiarEstatusRecibo']); 
Route::patch('/envia/comentario/recibo/{matricula}/{comentario}', [FaseFinalController::class, 'enviarComentarioRecibo']); 


    //foraneo
    Route::post('/foraneos', [ForaneoController::class, 'store']);
    Route::get('/obc/foraneos/cuc', [ForaneoController::class, 'foraneosDeCUC']);
    Route::get('/obc/anuncios/cuc', [AnuncioController::class, 'anunciosDeCUC']);
    Route::put('/foraneos/{id}', [ForaneoController::class, 'update']);
    Route::get('/obten/info/foraneo/{matricula}', [ForaneoController::class, 'infoForaneo']);

//Anuncio

Route::post('/anuncios', [AnuncioController::class, 'store']);
   
Route::put('/anuncios/{id}', [AnuncioController::class, 'update']);
Route::delete('/anuncios/{id}', [AnuncioController::class, 'destroy']);

Route::get('/anuncio/{id}', [AnuncioController::class, 'show']);

Route::delete('obc/estudiantes/matricula/eliminar/{id}', [CucsController::class, 'eliminarServicio']);

});

Route::middleware(['auth:sanctum', 'checkFacilitadorRole'])->group(function () {
    Route::get('/facilitadores/cucs/cucsuaco', [FacilitadoresController::class, 'cucsDeFacilitadores']);
    Route::get('/cuc/{claveCuc}/periodo/{idPeriodo}/facilitadores/clases', [FacilitadoresController::class, 'clasesDeFacilitadores']);
    Route::get('/facilitadores/clases/{claveClase}/estudiantes', [ClasesController::class, 'estudiantesPorClase']);
    Route::post('/facilitadores/clases/estudiantes/calificacion', [ClasesController::class, 'calificarEstudiantes']);
   
});

Route::middleware(['auth:sanctum', 'checkEstudiantesRole'])->group(function () {
    Route::get('/estudiantes/informacion/general', [EstudiantesController::class, 'infoPersonal']);
    Route::get('/estudiantes/clases/general', [ClasesController::class, 'clasesDeEstudiantes']);
    Route::get('/estudiantes/clases/general/totales', [ClasesController::class, 'TotalClasesDeEstudiantes']);
    Route::get('/estudiantes/clases/periodo/{idPeriodo}', [ClasesController::class, 'clasesDeEstudiantesPorPeriodo']);
    Route::get('/estudiantes/pertenece/periodos', [ClasesController::class, 'periodoDeEstudiantesPorClases']);
    Route::get('/estudiantes/servicio/{id}', [EstudiantesController::class, 'servicioEstatus']);
    Route::get('/estudiantes/matricula/propia', [EstudiantesController::class, 'matriculaActual']);
    
    Route::get('/tipos', [TipoSangreController::class, 'regresaSangre']);
    Route::get('/lengua', [LenguasIndigenasController::class, 'regresaLengua']);
    Route::get('/pueblos', [PueblosIndigenasController::class, 'regresaPueblo']);
    Route::get('/cucscarreras', [CucsController::class, 'regresaCarrerasPorCuc']);//comparten escolares y consejeros
    Route::get('/nacionalidad', [NacionalidadesController::class,'regresaNacionalidades']);
    Route::put('/estudia/{id}', [EstudiantesController::class, 'actualizaInfo']);
    Route::get('/estado/envio',[EstudiantesController::class,'obtenerEnvio']);
    Route::patch('/estado/enviado', [EstudiantesController::class, 'enviadoEstatus']);   

    Route::post('/servicio', [ServicioController::class, 'store']);

    Route::get('/servicio/info', [ServicioController::class, 'infoServicio']);

    
    Route::get('/estado/servicio',[EstudiantesController::class,'obtenerEstatusServicio']);


    Route::get('/regresa/estado/social',[ServicioController::class,'obtenerEnvio']);
    Route::patch('/enviado/estado/social', [ServicioController::class, 'enviadoEstatus']); 
    Route::get('/infoPage', [EstudiantesController::class, 'infoPage']);
    Route::get('/comentario', [EstudiantesController::class,'obtenerComentario']);
    Route::get('/comentario/social', [ServicioController::class,'obtenerComentarioSocial']);
    Route::put('/actualiza/info/social/{matricula}', [ServicioController::class, 'actualizaInfoSocial']);
    Route::get('/info/estado', [EstudiantesController::class, 'obtenerEstadoTramite']);

    Route::get('/obten/info/general/propia/est', [ServicioController::class, 'infoGeneralPropia']);

//Fase 1
    Route::post('/subir', [FaseUnoController::class, 'storeFileSolicitud']);
    Route::post('/subir/aceptacion', [FaseUnoController::class, 'storeFileAceptacion']);
    
    Route::patch('/estado/cambio/presentacion/carta/{valor}', [FaseUnoController::class, 'cambiarEstadoPresentacion']);
    Route::patch('/estado/cambio/aceptacion/carta/{valor}', [FaseUnoController::class, 'cambiarEstadoAceptacion']);
  
   // Route::get('/obten/info/general/{matricula}', [ServicioController::class, 'infoGeneral']);

 //Fase 2
 Route::post('/subir/doc/informe1', [FaseDosController::class, 'storeFileInformeUno']);
 Route::patch('/estado/cambio/presentacion/informe1/{valor}', [FaseDosController::class, 'cambiarEstadoInforme1']);
 Route::get('/archivo/informe/descarga/pdf/{nombreArchivo}', [FaseDosController::class, 'getInforme']);
    //Route::get('/estudiantes/pertenece/prueba', [ClasesController::class, 'periodoDeEstudiantesPorClasesPrueba']);


     //Fase 3
 Route::post('/subir/doc/informe2', [FaseTresController::class, 'storeFileInformeDos']);
 Route::patch('/estado/cambio/presentacion/informe2/{valor}', [FaseTresController::class, 'cambiarEstadoInforme2']);
 Route::get('/archivo/informe2/descarga/pdf/{nombreArchivo}', [FaseTresController::class, 'getInformeDos']);



 //Fase 4
 Route::post('/subir/doc/informe3', [FaseCuatroController::class, 'storeFileInformeTres']);
 Route::patch('/estado/cambio/presentacion/informe3/{valor}', [FaseCuatroController::class, 'cambiarEstadoInforme3']);

 Route::get('/archivo/informe3/descarga/pdf/{nombreArchivo}', [FaseCuatroController::class, 'getInformeTres']);



//Fase 5
Route::post('/subir/doc/terminacion', [FaseCincoController::class, 'storeFileTerminacion']);
Route::patch('/estado/cambio/carta/terminacion/{valor}', [FaseCincoController::class, 'cambiarEstadoTerminacion']);

Route::get('/archivo/informe3/descarga/pdf/{nombreArchivo}', [FaseCincoController::class, 'getCartaTerminacion']);


//Fase final
Route::post('/subir/doc/recibo', [FaseFinalController::class, 'storeFileRecibo']);

Route::patch('/estado/cambio/carta/recibo/{valor}', [FaseFinalController::class, 'cambiarEstadoRecibo']);

Route::get('/archivo/recibo/descarga/pdf/{nombreArchivo}', [FaseFinalController::class, 'getRecibo']);


Route::get('/anuncios/Cuc', [AnuncioController::class, 'anuncios']);

});


// Route::apiResource('cucs',CucsController::class);
// Route::apiResource('carreras',CarrerasController::class);
//Route::apiResource('grupos',GruposController::class);
//Route::apiResource('estudiantes',EstudiantesController::class);
// Route::apiResource('consejeros',ConsejerosController::class);
// Route::apiResource('facilitadores',FacilitadoresController::class);

// Route::controller(AuthController::class)->group(function(){
//     Route::delete('/logout','logout');
//     Route::get('/user','userDetail');
// })->middleware('auth:api');

// Route::post('/registrar', [AuthController::class, 'registrar']);

// Route::delete('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

// Route::get('/estados/{estadoId}/municipios', [MunicipiosController::class, 'municipiosPorEstado']);
//Route::apiResource('cucs',CucsController::class);








//Route::get('/cucs/{cucId}/grupos', [CucsController:: class, 'gruposPorCuc']);

// Route::post('/carreras/asociar-cuc/', [CarrerasController::class, 'asociarCuc']);
// Route::post('/facilitadores/asociar-cuc/', [FacilitadoresController::class, 'asociarCuc']);


// Route::post('/carreras/desasociar-cuc/', [CarrerasController::class, 'eliminarAsociacionCuc']);
// Route::post('/facilitadores/desasociar-cuc/', [FacilitadoresController::class, 'eliminarAsociacionCuc']);

//Route::get('/carreras/{claveCarrera}/grupos', [CarrerasController::class, 'gruposPorCarrera']);
//Route::get('/carreras/{claveCarrera}/cucs/{claveCuc}/grupos', [CarrerasController::class,'gruposPorCarreraPorCuc']);






// $user = $request->user();
//         if ($user) {
//             $user->token()->revoke();
//             return ApiResponses::success('Sesion Cerrada', 200);
//         }else {
//             return ApiResponses::error('No se pudo cerrar la sesión', 400);
//         }