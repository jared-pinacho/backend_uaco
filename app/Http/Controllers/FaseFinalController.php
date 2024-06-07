<?php

namespace App\Http\Controllers;

use App\Models\faseFinal;
use Illuminate\Http\Request;


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

class FaseFinalController extends Controller
{
    public function storeFileRecibo(Request $request)
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
                    $nombreArchivo = 'recibo'.$matricula . '.pdf';
    
                    // Almacenar el archivo en el sistema de archivos (en storage/app/public)
                    $path = $archivo->storeAs('public', $nombreArchivo);
    
                    try {
                        // Verificar si existe un registro previo
                        $existeRegistro = FaseFinal::where('id_servicio', $id_servicio)->where('recibo', '!=', null)->first();
            
                        if (!$existeRegistro) {
                            // Crear una nueva instancia de FaseUno y guardarla en la base de datos
                            $fasefinal = new FaseFinal();
                            $fasefinal->recibo = $nombreArchivo;
                            $fasefinal->id_servicio = $id_servicio;
                            $fasefinal->comentario="";
                            $fasefinal->estatus_envio=1;
                            $fasefinal->save();
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




    public function cambiarEstadoRecibo($estado)
    {
        try {
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
            $faseFinal = FaseFinal::where('id_servicio', $id_servicio)->firstOrFail();
    
            // Actualizar el estado de la presentación
            $faseFinal->estatus_envio = $estado;
            $faseFinal->save();
    
            return ApiResponses::success('Estado cambiado correctamente', 200, $faseFinal->estatus_envio);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No se encontró el estudiante o servicio', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }
    


    
    public function getRecibo($nombreArchivo)
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


    public function cambiarEstatusRecibo($matricula, $estado)
    {
        try {

            // $estudiante = Estudiantes::findOrFail($id);
           $servicio = Servicio::where('matricula', $matricula)->firstOrFail();
           $id_ser=$servicio->id_servicio;

           $faseFinal = FaseFinal::where('id_servicio', $id_ser)->firstOrFail();

            $faseFinal->estatus_envio = $estado;
          

          if ($estado == 2 ) {

              $estudiante = Estudiantes::where('matricula', $matricula)->firstOrFail();
               $estudiante->estado_tramite = "Comprobante de pago";
                $estudiante->save();
            }elseif($estado == 4){

                
              $estudiante = Estudiantes::where('matricula', $matricula)->firstOrFail();
              $estudiante->estado_tramite = "Constancia solicitada";
               $estudiante->save();
            }

    
            $faseFinal->save();
    
            return ApiResponses::success('Estatus se cambio', 200, $faseFinal->estatus_envio);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }


    public function enviarComentarioRecibo($matricula, $comentario)
    {
        try {

            // $estudiante = Estudiantes::findOrFail($id);
           $servicio = Servicio::where('matricula', $matricula)->firstOrFail();
    $id_ser=$servicio->id_servicio;

    $faseFinal = FaseFinal::where('id_servicio', $id_ser)->firstOrFail();

            $faseFinal->comentario = $comentario;
            $faseFinal->save();
    
            return ApiResponses::success('Comentario Enviado', 200, $faseFinal->comentario);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

}
