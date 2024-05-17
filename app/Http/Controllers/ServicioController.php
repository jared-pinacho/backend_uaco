<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\Consejeros;
use App\Models\cuc_carrera;
use App\Models\Estudiantes;
use App\Models\FaseUno;
use App\Models\FaseDos;
use App\Models\FaseTres;
use App\Models\FaseCuatro;
use App\Models\FaseCinco;
use App\Models\User;
use App\Models\Cucs;
use App\Models\Direcciones;
use App\Models\Grupos;
use App\Models\Carreras;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Servicio;

class ServicioController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $request->validate([
                'modalidad' => 'required',
                'tipo_dep' => 'required',
                'nombre_dep' => 'required',
                'titular_dep' => 'required',
                'cargo_tit' => 'required',
                'grado_tit' => 'required',
                'responsable' => 'required',
                'programa' => 'required',
                'actividad' => 'required',
                'num_exterior' => 'required',
                'calle' => 'required',
                'colonia' => 'required',
                'horas' => 'required',
            ], );


            $direccion = new Direcciones();
            $direccion->calle = $request->input('calle');
            $direccion->num_exterior = $request->input('num_exterior');
            $direccion->id_colonia = $request->input('colonia');
            $direccion->save();

            $user = Auth::user();
            $id = $user->id;

            $estudiante = Estudiantes::where('id', $id)->firstOrFail();
            $matri = $estudiante->matricula;
            $servicio = new Servicio();

            $servicio->modalidad = $request->modalidad;
            $servicio->tipo_dep = $request->tipo_dep;
            $servicio->nombre_dep = $request->nombre_dep;
            $servicio->titular_dep = $request->titular_dep;
            $servicio->cargo_tit = $request->cargo_tit;
            $servicio->grado_tit = $request->grado_tit;
            $servicio->responsable = $request->responsable;
            $servicio->programa = $request->programa;
            $servicio->actividad = $request->actividad;
            $servicio->fecha_ini = $request->fecha_inicio;
            $servicio->fecha_fin = $request->fecha_final;
            $servicio->horas = $request->horas;
            $servicio->matricula = $matri;

            $servicio->direccion()->associate($direccion);
            $servicio->save();

            DB::commit();
            return ApiResponses::success('Registro exitoso', 201, $servicio);

        } catch (ValidationException $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        } catch (Exception $ex) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $ex->getMessage(), 500);
        }
    }

    public function infoServicio()
    {
        try {
            $user = Auth::user();
            $id = $user->id;

            // Obtener el estudiante por su ID
            $estudia = Estudiantes::where('id', $id)->firstOrFail();
            $matricula = $estudia->matricula;

            // Buscar el servicio por matrícula del estudiante
            $servicio = Servicio::with(
                'direccion.colonia.cp',
                'direccion.colonia.municipio.estado'
            )->where('matricula', $matricula)->firstOrFail();


            // Si se encuentra el servicio, devolver una respuesta exitosa
            return ApiResponses::success('Servicio encontrado', 200, $servicio);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            // Capturar cualquier otra excepción y devolver un error interno del servidor
            return ApiResponses::error('Error interno del servidor: ' . $e->getMessage(), 500);
        }
    }


    public function obtenerEnvio()
    {
        try {
            $user = Auth::user();
            $id = $user->id;

            // Obtener el estudiante por su ID
            $estudia = Estudiantes::where('id', $id)->firstOrFail();
            $matricula = $estudia->matricula;

            // Buscar el servicio por matrícula del estudiante
            $servicio = Servicio::where('matricula', $matricula)->firstOrFail();
            $envio = $servicio->estatus_envio;

            return ApiResponses::success('Envio', 200, $envio);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) { // Capturar cualquier otra excepción
            return ApiResponses::error('Error interno del servidor', 500);
        }
    }


    public function enviadoEstatus()
    {
        try {

            $user = Auth::user();
            $id = $user->id;
        
            // $estudiante = Estudiantes::findOrFail($id);
           $estudiante = Estudiantes::where('id', $id)->firstOrFail();
           $matricula = $estudiante->matricula;

           // Buscar el servicio por matrícula del estudiante
           $servicio = Servicio::where('matricula', $matricula)->firstOrFail();

            $servicio->estatus_envio = 1;
            $servicio->save();
    
            return ApiResponses::success('Estatus enviado ', 200, $servicio->estatus_envio);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }




    public function cambiarEst($matricula, $estado)
    {
        try {

            // $estudiante = Estudiantes::findOrFail($id);
           $servicio = Servicio::where('matricula', $matricula)->firstOrFail();
    

            $servicio->estatus_envio = $estado;

            if ($estado == 2) {

                $estudiante = Estudiantes::where('matricula', $matricula)->firstOrFail();
                $estudiante->estado_tramite = "Información de servicio";
                $estudiante->save();
            }
    
            $servicio->save();
    
            return ApiResponses::success('Estatus se cambio', 200, $servicio->estatus_envio);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }





    public function infoServicioEscolar($dato)
    {
        try {
            

            // Buscar el servicio por matrícula del estudiante
            $servicio = Servicio::with(
                'direccion.colonia.cp',
                'direccion.colonia.municipio.estado'
            )->where('matricula', $dato)->firstOrFail();


            // Si se encuentra el servicio, devolver una respuesta exitosa
            return ApiResponses::success('Servicio encontrado', 200, $servicio);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            // Capturar cualquier otra excepción y devolver un error interno del servidor
            return ApiResponses::error('Error interno del servidor: ' . $e->getMessage(), 500);
        }
    }



    public function enviarComentarioSocial($matricula, $comentario)
    {
        try {

            // $estudiante = Estudiantes::findOrFail($id);
           $servicio = Servicio::where('matricula', $matricula)->firstOrFail();
    

            $servicio->comentario = $comentario;
            $servicio->save();
    
            return ApiResponses::success('Comentario Enviado', 200, $servicio->comentario);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }



    public function obtenerComentarioSocial( )
    {
        try {
            $user = Auth::user();
            $id = $user->id;
        
            // $estudiante = Estudiantes::findOrFail($id);
           $estudiante = Estudiantes::where('id', $id)->firstOrFail();
           $matricula = $estudiante->matricula;

            $servicio = Servicio::where('matricula', $matricula)->firstOrFail();
    

            $comentario = $servicio->comentario;

            return ApiResponses::success('Envio', 200, $comentario);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) { // Capturar cualquier otra excepción
            return ApiResponses::error('Error interno del servidor', 500);
        }
    }







    public function actualizaInfoSocial(Request $request, $matricula)
    {
        DB::beginTransaction();
        try {
           
    $request->validate([
        'modalidad' => 'required',
        'tipo_dep' => 'required',
        'nombre_dep' => 'required',
        'titular_dep' => 'required',
        'cargo_tit' => 'required',
        'grado_tit' => 'required',
        'responsable' => 'required',
        'programa' => 'required',
        'actividad' => 'required',
        'num_exterior' => 'required',
        'calle' => 'required',
        'colonia' => 'required',
        'horas' => 'required',
    ], );


    $servicio = Servicio::where('matricula',$matricula)->firstOrFail();
    
   
    $servicio->modalidad = $request->modalidad;
    $servicio->tipo_dep = $request->tipo_dep;
    $servicio->nombre_dep = $request->nombre_dep;
    $servicio->titular_dep = $request->titular_dep;
    $servicio->cargo_tit = $request->cargo_tit;
    $servicio->grado_tit = $request->grado_tit;
    $servicio->responsable = $request->responsable;
    $servicio->programa = $request->programa;
    $servicio->actividad = $request->actividad;
    $servicio->fecha_ini = $request->fecha_inicio;
    $servicio->fecha_fin = $request->fecha_final;
    $servicio->horas = $request->horas;
    $servicio->matricula = $matricula;

    $direccion = $servicio->direccion;

 
    $direccion->calle = $request->input('calle');
    $direccion->num_exterior = $request->input('num_exterior');
    $direccion->id_colonia = $request->input('colonia');
    $direccion->update();
    $servicio->update();


            DB::commit();
            return ApiResponses::success('Actualizado', 201);
        } catch (ModelNotFoundException $ex) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $ex->getMessage(), 404);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }



    public function infoGeneralPropia()
    {
        try {
            
            $user = Auth::user();
            $id = $user->id;
        
            // $estudiante = Estudiantes::findOrFail($id);
           $estudiante = Estudiantes::where('id', $id)->firstOrFail();
           $dato = $estudiante->matricula;
   


            // Buscar el servicio por matrícula del estudiante
            $servicio = Servicio::with(
                'direccion.colonia.cp',
                'direccion.colonia.municipio.estado'
            )->where('matricula', $dato)->firstOrFail();

           $id_ser=$servicio->id_servicio;
           
            $estudiante = Estudiantes::select('matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'sexo', 'telefono', 'semestre','clave_grupo')
        ->where('matricula', $dato)
        ->firstOrFail();

        $mat=$estudiante->clave_grupo;

        

    $grupo=Grupos::where("clave_grupo",$mat)->firstOrFail();
    $clave_carrera=$grupo->clave_carrera;

    $carrera=Carreras::where('clave_carrera',$clave_carrera)->firstOrFail();
    $nombreCarrera=$carrera->nombre;

    $cuc_carrera=cuc_carrera::where('clave_carrera',$clave_carrera)->firstOrFail();
    $a= $cuc_carrera->clave_cuc;



    $cuc=Cucs::with(
        
        'direccion.colonia.municipio.estado'
    )->where('clave_cuc',$a)->firstOrFail();

    

    $consejero= Consejeros::select('matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'sexo', 'telefono')
    ->where('clave_cuc',$a)
    ->firstOrFail();


  $faseUno=FaseUno::where('id_servicio', $id_ser)->first();

   $faseDos=FaseDos::where('id_servicio', $id_ser)->first();

   $faseTres=FaseTres::where('id_servicio', $id_ser)->first();

   $faseCuatro=FaseCuatro::where('id_servicio', $id_ser)->first();

   $faseCinco=FaseCinco::where('id_servicio', $id_ser)->first();


 // Combinar datos del servicio y del estudiante
    $datosCombinados = [
        'servicio' => $servicio,
        'estudiante' => $estudiante,
        'carrera' =>$nombreCarrera,
        'cuc' =>$cuc,
        'consejero'=>$consejero,
        'faseUno'=>$faseUno,
        'faseDos'=>$faseDos,
        'faseTres'=>$faseTres,
        'faseCuatro'=>$faseCuatro,
        'faseCinco'=>$faseCinco,
    ];

            // Si se encuentra el servicio, devolver una respuesta exitosa
            return ApiResponses::success('Servicio encontrado', 200, $datosCombinados);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            // Capturar cualquier otra excepción y devolver un error interno del servidor
            return ApiResponses::error('Error interno del servidor: ' . $e->getMessage(), 500);
        }
    }








    public function infoGeneral( $dato)
    {
        try {
        


            // Buscar el servicio por matrícula del estudiante
            $servicio = Servicio::with(
                'direccion.colonia.cp',
                'direccion.colonia.municipio.estado'
            )->where('matricula', $dato)->firstOrFail();

            $id_ser=$servicio->id_servicio;
           
            $estudiante = Estudiantes::select('matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'sexo', 'telefono', 'semestre','clave_grupo')
        ->where('matricula', $dato)
        ->firstOrFail();

        $mat=$estudiante->clave_grupo;

        

    $grupo=Grupos::where("clave_grupo",$mat)->firstOrFail();
    $clave_carrera=$grupo->clave_carrera;

    $carrera=Carreras::where('clave_carrera',$clave_carrera)->firstOrFail();
    $nombreCarrera=$carrera->nombre;

    $cuc_carrera=cuc_carrera::where('clave_carrera',$clave_carrera)->firstOrFail();
    $a= $cuc_carrera->clave_cuc;



    $cuc=Cucs::with(
        
        'direccion.colonia.municipio.estado'
    )->where('clave_cuc',$a)->firstOrFail();

    

    $consejero= Consejeros::select('matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'sexo', 'telefono')
    ->where('clave_cuc',$a)
    ->firstOrFail();


    $faseUno=FaseUno::where('id_servicio', $id_ser)->first();
    $faseDos=FaseDos::where('id_servicio', $id_ser)->first();
    
    $faseTres=FaseTres::where('id_servicio', $id_ser)->first();
    $faseCuatro=FaseCuatro::where('id_servicio', $id_ser)->first();
    $faseCinco=FaseCinco::where('id_servicio', $id_ser)->first();

 // Combinar datos del servicio y del estudiante
    $datosCombinados = [
        'servicio' => $servicio,
        'estudiante' => $estudiante,
        'carrera' =>$nombreCarrera,
        'cuc' =>$cuc,
        'consejero'=>$consejero,
        'faseUno'=>$faseUno,
        'faseDos'=>$faseDos,
        'faseTres'=>$faseTres,
        'faseCuatro'=>$faseCuatro,
        'faseCinco'=>$faseCinco,
    ];

            // Si se encuentra el servicio, devolver una respuesta exitosa
            return ApiResponses::success('Servicio encontrado', 200, $datosCombinados);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            // Capturar cualquier otra excepción y devolver un error interno del servidor
            return ApiResponses::error('Error interno del servidor: ' . $e->getMessage(), 500);
        }
    }







}
