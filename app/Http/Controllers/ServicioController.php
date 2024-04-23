<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\Estudiantes;
use App\Models\User;
use App\Models\Cucs;
use App\Models\CodigoPostal;
use App\Models\Colonia;
use App\Models\Direcciones;
use App\Models\Estados;
use App\Models\Municipios;
use App\Models\LenguasIndigenas;
use App\Models\PueblosIndigenas;
use App\Models\Nacionalidades;
use App\Models\estadoDocumentacion;
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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
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

            // $estudiante = Estudiantes::findOrFail($id);
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



            // Asocia la dirección al servicio
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







}
