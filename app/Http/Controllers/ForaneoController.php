<?php

namespace App\Http\Controllers;

use App\Models\Escolares;
use App\Models\Foraneo;
use Illuminate\Http\Request;

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
use Illuminate\Support\Facades\Auth;
use App\Models\Servicio;

class ForaneoController extends Controller
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
     * Store a newly created resource in storage.efon
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $request->validate([
                'nombre' => 'required',
                'apellido_paterno' => 'required',
                'apellido_materno' => 'required',
                'edad' => 'required',
                'sexo' => 'required',
                'telefono' => 'required',
                'email' => 'required',
                'semestre' => 'required',
                'discapacidad' => 'required',
                'institucion' => 'required',
                'matricula_escolar' => 'required',
                'licenciatura' => 'required',
                'programa' => 'required',
                'titular_dep' => 'required',          
                'cargo_tit' => 'required',
                'grado_tit' => 'required',
                'responsable' => 'required',
                'horas' => 'required',
                'lengua_indigena' => 'required',
                

            ], );

            $lengua = $request->otra_lengua;



            $user = Auth::user();
            $id = $user->id;

            // $estudiante = Estudiantes::findOrFail($id);
            $escolar = Escolares::where('id', $id)->firstOrFail();

            $claveCuc = $escolar->clave_cuc;
            $matri = $escolar->matricula;


            $foraneo = new Foraneo();

            $foraneo->nombre = $request->nombre;
            $foraneo->apellido_paterno = $request->apellido_paterno;
            $foraneo->apellido_materno = $request->apellido_materno;
            $foraneo->edad = $request->edad;
            $foraneo->sexo = $request->sexo;
            $foraneo->telefono = $request->grado_tit;
            $foraneo->correo = $request->email;
            $foraneo->semestre = $request->semestre;
            $foraneo->discapacidad = $request->discapacidad;
            $foraneo->institucion = $request->institucion;
            $foraneo->matricula_escolar = $request->matricula_escolar;
            $foraneo->licenciatura=$request->licenciatura;
            $foraneo->programa=$request->programa;
            $foraneo->resp_seg=$request->responsable;
            $foraneo->titular_dep=$request->titular_dep;
            $foraneo->cargo_titular=$request->cargo_tit;
            $foraneo->grado_titular=$request->grado_tit;
            $foraneo->fecha_inicio = $request->fecha_inicio;
            $foraneo->fecha_final = $request->fecha_final;
            $foraneo->horas = $request->horas;
            $foraneo->matricula=$matri;
           $foraneo->CUC=$claveCuc;
           
           
           if ($request->lengua_indigena === '2' && $lengua !== '') {
            $l = LenguasIndigenas::where('nombre', $lengua)->first();

            if (!$l) {
                $nueva_lengua = new LenguasIndigenas();
                $nueva_lengua->nombre = $request->otra_lengua;
                $nueva_lengua->save();
                $foraneo->lenguaindigena()->associate($nueva_lengua);
            } else {
                $foraneo->lenguaindigena()->associate($l);

            }
        } else {
            $foraneo->id_lenguaindigena = $request->lengua_indigena;
        }

            
            $foraneo->save();

            DB::commit();
            return ApiResponses::success('Registro exitoso', 201, $foraneo);

        } catch (ValidationException $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        } catch (Exception $ex) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $ex->getMessage(), 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;


            $estudiante = Foraneo::with(
                'lenguaindigena'
            )->findOrFail($id);

         

            
            return ApiResponses::success('foraneo encontrado', 200, $estudiante);

           
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 404);
        }
    }  

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Foraneo $foraneo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idEstudiante)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'nombre' => 'required',
                'apellido_paterno' => 'required',
                'apellido_materno' => 'required',
                'edad' => 'required',
                'sexo' => 'required',
                'telefono' => 'required',
                'correo' => 'required',
                'semestre' => 'required',
                'discapacidad' => 'required',
                'institucion' => 'required',
                'matricula_escolar' => 'required',
                'licenciatura' => 'required',
                'programa' => 'required',
                'titular_dep' => 'required',          
                'cargo_tit' => 'required',
                'grado_tit' => 'required',
                'resp_seg' => 'required',
                'horas' => 'required',
                'lengua_indigena' => 'required',
            ], );

            $lengua = $request->otra_lengua;

            $foraneo = Foraneo::findOrFail($idEstudiante);

           
      


            $foraneo->nombre = $request->nombre;
            $foraneo->apellido_paterno = $request->apellido_paterno;
            $foraneo->apellido_materno = $request->apellido_materno;
            $foraneo->edad = $request->edad;
            $foraneo->sexo = $request->sexo;
            $foraneo->telefono = $request->grado_tit;
            $foraneo->correo = $request->correo;
            $foraneo->semestre = $request->semestre;
            $foraneo->discapacidad = $request->discapacidad;
            $foraneo->institucion = $request->institucion;
            $foraneo->matricula_escolar = $request->matricula_escolar;
            $foraneo->licenciatura=$request->licenciatura;
            $foraneo->programa=$request->programa;
            $foraneo->resp_seg=$request->resp_seg;
            $foraneo->titular_dep=$request->titular_dep;
            $foraneo->cargo_titular=$request->cargo_tit;
            $foraneo->grado_titular=$request->grado_tit;
            $foraneo->fecha_inicio = $request->fecha_inicio;
            $foraneo->fecha_final = $request->fecha_final;
            $foraneo->horas = $request->horas;

           
            if ($request->lengua_indigena === '2' && $lengua !== '') {
                $l = LenguasIndigenas::where('nombre', $lengua)->first();

                if (!$l) {
                    $nueva_lengua = new LenguasIndigenas();
                    $nueva_lengua->nombre = $request->otra_lengua;
                    $nueva_lengua->save();
                    $foraneo->lenguaindigena()->associate($nueva_lengua);
                } else {
                    $foraneo->lenguaindigena()->associate($l);

                }
            } else {
                $foraneo->id_lenguaindigena = $request->lengua_indigena;
            }

            $foraneo->update();

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

    /**
     * Remove the specified resource from storage.
     */
  




     public function foraneosDeCUC(Request $request)
     {
         try {
             $usuarioEscolar = auth()->user();
             $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;
     
     
             // Obtener estudiantes foráneos asociados al CUC con relación 'lenguaindigena' cargada
             $estudiantess = Foraneo::where('CUC', $claveCucEscolar)
                                     ->with('lenguaindigena')
                                     ->get();
     

                                     
             return ApiResponses::success('Estudiantes en el CUC', 200, $estudiantess);
         } catch (ModelNotFoundException $ex) {
             return ApiResponses::error('No encontrado', 404);
         } catch (Exception $e) {
             return ApiResponses::error('Error: ' . $e->getMessage(), 500);
         }
     }
     



}

