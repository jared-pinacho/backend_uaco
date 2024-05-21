<?php

namespace App\Http\Controllers;

use App\Models\faseUno;
use App\Models\User;
use App\Models\Servicio;
use App\Models\Estudiantes;
use App\Http\Responses\ApiResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Dotenv\Exception\ValidationException;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class FaseUnoController extends Controller
{

     private $disk = "public";

    public function storeFileSolicitud(Request $request)
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
                    $nombreArchivo = 'solicitud'.$matricula . '.pdf';
    
                    // Almacenar el archivo en el sistema de archivos (en storage/app/public)
                    $path = $archivo->storeAs('public', $nombreArchivo);
    
                    try {
                        // Verificar si existe un registro previo
                        $existeRegistro = FaseUno::where('id_servicio', $id_servicio)->where('carta_presentacion', '!=', null)->first();
            
                        if (!$existeRegistro) {
                            // Crear una nueva instancia de FaseUno y guardarla en la base de datos
                            $faseuno = new FaseUno();
                            $faseuno->carta_presentacion = $nombreArchivo;
                            $faseuno->id_servicio = $id_servicio;
                            $faseuno->carta_aceptacion = ""; 
                            $faseuno->com_pres="";
                            $faseuno->come_acep="";
                            $faseuno->pres_estado=1;
                            $faseuno->save();
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
    

   
    public function downloadFile($name)
    {
        if(Storage::disk($this->disk)->exists($name)){
            return Storage::disk($this->disk)->download($name);
        }else{

            return response()->json(['mensaje' => 'Archivo no encontrado'], 404);
        }

       
    }

    /**
     * Display the specified resource.
     */
    public function enviarComentarioPresentacion($matricula, $comentario)
    {
        try {

            // $estudiante = Estudiantes::findOrFail($id);
           $servicio = Servicio::where('matricula', $matricula)->firstOrFail();
    $id_ser=$servicio->id_servicio;

    $faseUno = FaseUno::where('id_servicio', $id_ser)->firstOrFail();

            $faseUno->com_pres = $comentario;
            $faseUno->save();
    
            return ApiResponses::success('Comentario Enviado', 200, $faseUno->comentario);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

   
    public function enviarComentarioAceptacion($matricula, $comentario)
    {
        try {

            // $estudiante = Estudiantes::findOrFail($id);
           $servicio = Servicio::where('matricula', $matricula)->firstOrFail();
    $id_ser=$servicio->id_servicio;

    $faseUno = FaseUno::where('id_servicio', $id_ser)->firstOrFail();

            $faseUno->come_acep = $comentario;
            $faseUno->save();
    
            return ApiResponses::success('Comentario Enviado', 200, $faseUno->come_acep);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

   





    public function recuperarArchivoPorNombre($nombreArchivo)
{
    // Directorio donde se guardan los archivos
    $directorio = 'public';
    // Verificar si el archivo existe en el directorio
    if (Storage::exists($directorio . '/' . $nombreArchivo)) {
        // Obtener la URL pública del archivo
        $urlArchivo = asset('storage/' . $directorio . '/' . $nombreArchivo);
       // $urlArchivo = Storage::url('public/' . $directorio . '/' . $nombreArchivo);
        // Devolver la URL del archivo
        return response()->json(['url' => $urlArchivo], 200);
    } else {
        // Archivo no encontrado, devolver respuesta apropiada
        return response()->json(['mensaje' => 'Archivo no encontrado'], 404);
    }
}




public function getArchivo($nombreArchivo)
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



public function cambiarEstadoPresentacion($estado)
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
        $faseUno = FaseUno::where('id_servicio', $id_servicio)->firstOrFail();

        // Actualizar el estado de la presentación
        $faseUno->pres_estado = $estado;
        $faseUno->save();

        return ApiResponses::success('Estado cambiado correctamente', 200, $faseUno->pres_estado);
    } catch (ModelNotFoundException $ex) {
        return ApiResponses::error('No se encontró el estudiante o servicio', 404);
    } catch (Exception $e) {
        return ApiResponses::error('Error: ' . $e->getMessage(), 500);
    }
}




public function cambiarEstado($matricula, $estado)
    {
        try {

            // $estudiante = Estudiantes::findOrFail($id);
           $servicio = Servicio::where('matricula', $matricula)->firstOrFail();
           $id_ser=$servicio->id_servicio;

           $faseUno = FaseUno::where('id_servicio', $id_ser)->firstOrFail();

            $faseUno->pres_estado = $estado;
          

          if ($estado == 2 &&  $faseUno->acep_estado==2 ) {

              $estudiante = Estudiantes::where('matricula', $matricula)->firstOrFail();
               $estudiante->estado_tramite = "Inicio de servicio";
                $estudiante->save();
            }
    
            $faseUno->save();
    
            return ApiResponses::success('Estatus se cambio', 200, $faseUno->pres_estado);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }



    public function cambiarEstadoAceptacionEscolar($matricula, $estado)
    {
        try {

            // $estudiante = Estudiantes::findOrFail($id);
           $servicio = Servicio::where('matricula', $matricula)->firstOrFail();
           $id_ser=$servicio->id_servicio;

           $faseUno = FaseUno::where('id_servicio', $id_ser)->firstOrFail();

            $faseUno->acep_estado = $estado;
          

          if ($estado == 2 &&  $faseUno->pres_estado==2 ) {

              $estudiante = Estudiantes::where('matricula', $matricula)->firstOrFail();
               $estudiante->estado_tramite = "Inicio de servicio";
               $faseUno->estatus_envio=2;
                $estudiante->save();
            }
    
            $faseUno->save();
    
            return ApiResponses::success('Estatus se cambio', 200, $faseUno->pres_estado);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }





    public function storeFileAceptacion(Request $request)
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
                    $nombreArchivo = 'aceptacion'.$matricula . '.pdf';
    
                    // Almacenar el archivo en el sistema de archivos (en storage/app/public)
                    $path = $archivo->storeAs('public', $nombreArchivo);
    
                    try {
                        // Verificar si existe un registro previo
                        $existeRegistro = FaseUno::where('id_servicio', $id_servicio)->where('carta_presentacion', '!=', null)->first();
                        $faseuno = FaseUno::where('id_servicio', $id_servicio)->firstOrFail();

$faseuno->carta_aceptacion =  $nombreArchivo; 
$faseuno->save();
            
                        if (!$existeRegistro) {
                            return response()->json(['message' => 'Sube primero la solicitud'], 200);
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
    



    public function cambiarEstadoAceptacion($estado)
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
            $faseUno = FaseUno::where('id_servicio', $id_servicio)->firstOrFail();
    
            // Actualizar el estado de la presentación
            $faseUno->acep_estado = $estado;


            if ($estado == 2 &&  $faseUno->pres_estado==2 ) {

                $estudiante = Estudiantes::where('matricula', $matricula)->firstOrFail();
                 $estudiante->estado_tramite = "Inicio de servicio";
                 $faseUno->estatus_envio=2;
                  $estudiante->save();
              }


            $faseUno->save();
    
            return ApiResponses::success('Estado cambiado correctamente', 200, $faseUno->acep_estado);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No se encontró el estudiante o servicio', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }
    



}
