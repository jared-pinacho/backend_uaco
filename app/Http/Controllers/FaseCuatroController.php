<?php

namespace App\Http\Controllers;

use App\Models\FaseCuatro;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Servicio;
use App\Models\Estudiantes;
use App\Http\Responses\ApiResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Dotenv\Exception\ValidationException;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class FaseCuatroController extends Controller
{
    

    public function storeFileInformeTres(Request $request)
    {
        // Obtener el usuario autenticado
        $user = auth()->user();
        $id = $user->id;
    
        try {
            // Obtener el estudiante basado en el ID del usuario
            $estudiante = Estudiantes::where('id', $id)->firstOrFail();
            $matricula = $estudiante->matricula;
    
            // Obtener el servicio basado en la matrícula del estudiante
            $servicio = Servicio::where('matricula', $matricula)->firstOrFail();
            $id_servicio = $servicio->id_servicio;
    
            // Verificar si se recibió un archivo en la solicitud
            if ($request->hasFile('archivo')) {
                $archivo = $request->file('archivo');
    
                // Verificar si el archivo es válido y es un archivo PDF
                if ($archivo->isValid() && $archivo->getClientOriginalExtension() === 'pdf') {
                    // Generar un nombre único para el archivo utilizando UUID
                    $nombreArchivo = 'informeTres'.$matricula . '.pdf';
    
                    // Almacenar el archivo en el sistema de archivos (en storage/app/public)
                    $path = $archivo->storeAs('public', $nombreArchivo);
    
                    try {
                        // Verificar si existe un registro previo
                        $existeRegistro = FaseCuatro::where('id_servicio', $id_servicio)->where('reporte_tres', '!=', null)->first();
            
                        if (!$existeRegistro) {
                            // Crear una nueva instancia de FaseUno y guardarla en la base de datos
                            $fasecuatro = new FaseCuatro();
                            $fasecuatro->reporte_tres = $nombreArchivo;
                            $fasecuatro->id_servicio = $id_servicio;
                            $fasecuatro->comentario="";
                            $fasecuatro->estatus_envio=1;
                            $fasecuatro->save();
                        }
            
                        // Confirmar la transacción
                        DB::commit();
                        // Enviar una respuesta de éxito al cliente
                        return response()->json(['message' => 'Archivo almacenado correctamente'], 200);
                    } catch (ValidationException $e) {
                        // Revertir la transacción en caso de error de validación
                        DB::rollBack();
                        return response()->json(['error' => 'Error de validación: ' . $e->getMessage()], 422);
                    } catch (Exception $e) {
                        // Revertir la transacción en caso de error general
                        DB::rollBack();
                        return response()->json(['error' => 'Error interno del servidor: ' . $e->getMessage()], 500);
                    }
                } else {
                    // El archivo no es válido o no es un archivo PDF
                    return response()->json(['error' => 'El archivo seleccionado no es un PDF válido'], 400);
                }
            } else {
                // No se envió ningún archivo en la solicitud
                return response()->json(['error' => 'No se encontró ningún archivo en la solicitud'], 400);
            }
        } catch (Exception $e) {
            // Capturar cualquier excepción no controlada
            return response()->json(['error' => 'Error al procesar la solicitud: ' . $e->getMessage()], 500);
        }
    }



    public function cambiarEstadoInforme3($estado)
    {
        try {
            // Asegurarse de que el usuario esté autenticado
            $user = Auth::user();
            if (!$user) {
                return ApiResponses::error('Usuario no autenticado', 401);
            }
            
            // Obtener el ID del usuario autenticado
            $id = $user->id;
    
            // Buscar el estudiante basado en el ID del usuario
            $estudiante = Estudiantes::where('id', $id)->firstOrFail();
            $matricula = $estudiante->matricula;
    
            // Buscar el servicio relacionado con la matrícula del estudiante
            $servicio = Servicio::where('matricula', $matricula)->firstOrFail();
            $id_servicio = $servicio->id_servicio;
    
            // Buscar la faseUno relacionada con el ID del servicio
            $faseCuatro = FaseCuatro::where('id_servicio', $id_servicio)->firstOrFail();
    

            if($estado == 1){
              
                $estudiante->estatus_envio = 1;
                $estudiante->save();
            }
    
            if($estado == 2 || $estado ==3){
                
                $estudiante->estatus_envio = 2;
                $estudiante->save();
            }


            // Actualizar el estado de la presentación
            $faseCuatro->estatus_envio = $estado;
            $faseCuatro->save();
    
            return ApiResponses::success('Estado cambiado correctamente', 200, $faseCuatro->estatus_envio);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No se encontró el estudiante o servicio', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }
    

    public function getInformeTres($nombreArchivo)
    {
        $directorio = 'public';
        $rutaArchivo = $directorio . '/' . $nombreArchivo;
    
        if (Storage::exists($rutaArchivo)) {
            $contenidoArchivo = Storage::get($rutaArchivo);
    
            // Crear una respuesta con el contenido del archivo
            return response($contenidoArchivo, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$nombreArchivo.'"'
            ]);
        } else {
            return response()->json(['mensaje' => 'Archivo no encontrado'], Response::HTTP_NOT_FOUND);
        }
    }
    

  
    public function cambiarEstatusInforme3($matricula, $estado)
    {
        try {

            // $estudiante = Estudiantes::findOrFail($id);
           $servicio = Servicio::where('matricula', $matricula)->firstOrFail();
           $id_ser=$servicio->id_servicio;

           $faseCuatro = FaseCuatro::where('id_servicio', $id_ser)->firstOrFail();

            $faseCuatro->estatus_envio = $estado;
          

            $estudiante = Estudiantes::where('matricula', $matricula)->firstOrFail();

            if ($estado == 2 ) {         
                 $estudiante->estado_tramite = "Informe bimestral 3";
                 $estudiante->estado_tramite_updated_at = Carbon::now();
                 $estudiante->estatus_envio=2;
                  $estudiante->save();
              }


             if ($estado ==3) {      
                 $estudiante->estatus_envio=2;
                 $estudiante->estado_tramite_updated_at = Carbon::now();
                  $estudiante->save();
              }
 
             if($estado == 1){
                 $estudiante->estatus_envio = 1;
                 $estudiante->save();
             }

    
            $faseCuatro->save();
    
            return ApiResponses::success('Estatus se cambio', 200, $faseCuatro->estatus_envio);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }



    public function enviarComentarioInforme3($matricula, $comentario)
    {
        try {

            // $estudiante = Estudiantes::findOrFail($id);
           $servicio = Servicio::where('matricula', $matricula)->firstOrFail();
    $id_ser=$servicio->id_servicio;

    $faseCuatro = FaseCuatro::where('id_servicio', $id_ser)->firstOrFail();

            $faseCuatro->comentario = $comentario;
            $faseCuatro->save();
    
            return ApiResponses::success('Comentario Enviado', 200, $faseCuatro->comentario);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }


}
