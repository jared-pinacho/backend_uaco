<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\cuc_carrera;
use App\Models\Cucs;
use App\Models\FaseCinco;
use App\Models\FaseCuatro;
use App\Models\FaseDos;
use App\Models\FaseFinal;
use App\Models\FaseTres;
use App\Models\FaseUno;
use App\Models\Servicio;
use App\Models\Direcciones;
use App\Models\Estudiantes;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\Grupos;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Clases;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\CancelacionMailable;
use App\Mail\ContactoMailable;
class CucsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        try {
            $cuc = Cucs::with('direccion.colonia.cp', 'direccion.colonia.municipio.estado')->get();
            return ApiResponses::success('Lista de Cucs', 200, $cuc);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'nombre' => 'required',
                'clave' => 'required|unique:cucs,clave_cuc',
                'numero' => 'required|unique:cucs,numero',
                'calle' => 'required',
                'num_exterior' => 'required',
                'colonia' => 'required',
                // 'municipio' => 'required',
                // 'cp' => 'required'
            ], [
                'clave.unique' => 'La clave ya está en uso.', // Personaliza el mensaje de error para 'clave'
            ]);


            // $estado= new Estados();
            // $estado->nombre=$request->input('estado');
            // $estado->save();

            // $cp= new CodigoPostal();
            // $cp->codigo=$request->input('cp');
            // $cp->save();

            // $municipio= new Municipios();
            // $municipio->nombre=$request->input('municipio');
            // $municipio->estado()->associate($estado);
            // $municipio->save();

            // $cp = new CodigoPostal();
            // $cp->codigo = $request->input('cp');
            // $cp->save();
            // $colonia = new Colonia();
            // $colonia->nombre = $request->input('colonia');
            // $colonia->cp()->associate($cp);
            // $colonia->id_municipio = $request->input('municipio');
            // $colonia->save();

            $direccion = new Direcciones();
            $direccion->calle = $request->input('calle');
            $direccion->num_exterior = $request->input('num_exterior');
            $direccion->id_colonia = $request->input('colonia');
            $direccion->save();



            $cuc = new Cucs();

            $cuc->clave_cuc = $request->input('clave');
            $cuc->nombre = $request->nombre;
            $cuc->numero = $request->numero;
            $cuc->direccion()->associate($direccion);
            $cuc->save();

            DB::commit();
            return ApiResponses::success('Registro exitoso', 201, $cuc);
            //return $cuc;

        } catch (ValidationException $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $ex->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        try {
            $cuc = Cucs::with('direccion.colonia.cp', 'direccion.colonia.municipio.estado')->findOrFail($id);
            return ApiResponses::success('Encontrado', 200, $cuc);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('No encontrado', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idCuc)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'nombre' => 'required',
                'clave' => 'required',
                'numero' => 'required',
                'calle' => 'required',
                'num_exterior' => 'required',
                'colonia' => 'required',
                // 'municipio' => 'required',
                // 'cp' => 'required'
            ]);
            $cuc = Cucs::findOrFail($idCuc);

            //$cuc -> clave_cuc = $request->clave;
            $direccion = $cuc->direccion;
            // $colonia = $direccion->colonia;
            // $cp = $colonia->cp;

            $cuc->nombre = $request->input('nombre');
            $cuc->numero = $request->numero;
            $direccion->calle = $request->input('calle');
            $direccion->num_exterior = $request->input('num_exterior');
            $direccion->id_colonia = $request->input('colonia');
            // $cp->codigo = $request->input('cp');
            // $cp->update();

            // $colonia->nombre = $request->input('colonia');
            // $colonia->id_municipio = $request->input('municipio');

            // $colonia->update();
            $direccion->update();

            $cuc->update();
            DB::commit();
            return ApiResponses::success('Actualizado', 201);
        } catch (ModelNotFoundException $ex) {
            DB::rollBack();
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $cuc = Cucs::with('direccion.colonia')->findOrFail($id);
            if ($cuc->carreras->isNotEmpty()) {
                return ApiResponses::error('No se puede eliminar el CUC porque hay carreras asociadas a él', 422);
            }
            if ($cuc->consejeros->isNotEmpty()) {
                return ApiResponses::error('No se puede eliminar el CUC porque hay consejeros asociadas a él', 422);
            }
            if ($cuc->facilitadores->isNotEmpty()) {
                return ApiResponses::error('No se puede eliminar el CUC porque hay facilitadores asociadas a él', 422);
            }

            $direccion = $cuc->direccion;
            //$colonia = $direccion->colonia;

            $cuc->delete();

            if ($direccion) {
                $direccion->delete();
            }

            // if ($colonia) {
            //     $colonia->delete();
            // }


            DB::commit();
            return ApiResponses::success('CUC eliminado', 201);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return ApiResponses::error('No encontrado', 404);
        }
    }



    public function carrerasPorCuc(Request $request, $cucId)
    {
        try {
            $cuc = Cucs::findOrFail($cucId);
            $carrerasAsociadas = $cuc->carreras;
            return ApiResponses::success('Lista de Carreras de Cuc', 200, $carrerasAsociadas);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function pruebaCarrerasPorCuc(Request $request)
    {
        try {
            // Verificar si el usuario autenticado es un consejero
            $usuarioConsejero = auth()->user();
            $rol = $usuarioConsejero->rol->nombre;

            if ($rol === 'consejero') {
                // Obtener la clave_cuc del consejero
                $claveCuc = $usuarioConsejero->consejero->clave_cuc;
            } else if ($rol === 'escolar') {
                $claveCuc = $usuarioConsejero->escolar->clave_cuc;
            }
            // Obtener el CUC correspondiente a la clave_cuc del consejero
            $cuc = Cucs::where('clave_cuc', $claveCuc)->first();

            if ($cuc) {
                // Obtener las carreras asociadas al CUC
                $carrerasAsociadas = $cuc->carreras;

                return ApiResponses::success('Lista de Carreras de Cuc', 200, $carrerasAsociadas);
            }
            // }

            // return ApiResponses::error('Acceso no autorizado', 403);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }


    public function regresaCarrerasPorCuc(Request $request)
    {
        try {
            // Verificar si el usuario autenticado es un consejero
            $usuario = auth()->user();
            $iduser=$usuario->id;

            $estudiante = Estudiantes::where('id', $iduser)->first();

           $claveGrupo = $estudiante->clave_grupo;

        //   $claveCarrera = Grupos::where('id', $iduser)->first();
          
           $grupo = Grupos::findOrFail($claveGrupo);

          $claveCarrera=$grupo->clave_carrera;

          $carrera = cuc_carrera::where('clave_carrera',$claveCarrera)->first() ;

          $claveCuc=$carrera->clave_cuc;

            
            $cuc = Cucs::where('clave_cuc', $claveCuc)->first();

            if ($cuc) {
                // Obtener las carreras asociadas al CUC
                $carrerasAsociadas = $cuc->carreras;

                return ApiResponses::success('Lista de Carreras de Cuc', 200, $carrerasAsociadas);
            }
            // }

            // return ApiResponses::error('Acceso no autorizado', 403);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }









    public function facilitadoresPorCucDeConsejero(Request $request)
    {
        try {
            $usuarioConsejero = auth()->user();
            $claveCucConsejero = $usuarioConsejero->consejero->clave_cuc;
            $cuc = Cucs::findOrFail($claveCucConsejero);
            // $facilitadoresAsociadas = $cuc->facilitadores()->with('usuario.rol')->get();
            $facilitadoresAsociados = $cuc->facilitadores()->with('usuario.rol', 'direccion.colonia.cp', 'direccion.colonia.municipio.estado', 'tiposangre', 'nacionalidad', 'estado')
                ->selectRaw('*,CONCAT(nombre, " ", apellido_paterno, " ", apellido_materno) as nombreC')->with('usuario.rol')->get();
            return ApiResponses::success('Lista de Facilitadores de Cuc', 200, $facilitadoresAsociados);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function facilitadoresPorCucDeEscolar(Request $request)
    {
        try {
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;
            $cuc = Cucs::findOrFail($claveCucEscolar);
            // $facilitadoresAsociadas = $cuc->facilitadores()->with('usuario.rol')->get();
            $facilitadoresAsociados = $cuc->facilitadores()->selectRaw('*,CONCAT(nombre, " ", apellido_paterno, " ", apellido_materno) as nombreC')->with('usuario.rol')->get();
            return ApiResponses::success('Lista de Facilitadores de Cuc', 200, $facilitadoresAsociados);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function facilitadoresPorCuc(Request $request, $cucId)
    {
        try {
            $cuc = Cucs::findOrFail($cucId);
            // $facilitadoresAsociadas = $cuc->facilitadores()->with('usuario.rol')->get();
            $facilitadoresAsociadas = $cuc->facilitadores()->with('usuario.rol', 'direccion.colonia.cp', 'direccion.colonia.municipio.estado', 'tiposangre', 'nacionalidad', 'estado')
                ->selectRaw('*, CONCAT(nombre, " ", apellido_paterno, " ", apellido_materno) as nombreC')
                ->get();
            return ApiResponses::success('Lista de Facilitadores de Cuc', 200, $facilitadoresAsociadas);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function consejerosPorCuc(Request $request, $cucId)
    {
        try {
            $cuc = Cucs::findOrFail($cucId);
            $consejeros = $cuc->consejeros()->with('usuario.rol', 'cuc', 'direccion.colonia.cp', 'direccion.colonia.municipio.estado', 'tiposangre', 'nacionalidad', 'estado')->get();
            // $cuc = Cucs::with('consejeros')->findOrFail($cucId);
            return ApiResponses::success('Consejero del cuc', 200, $consejeros);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('No encontrado', 404);
        }
    }

    public function escolaresPorCuc(Request $request, $cucId)
    {
        try {
            $cuc = Cucs::findOrFail($cucId);
            $escolares = $cuc->escolares()->with('usuario.rol', 'cuc', 'direccion.colonia.cp', 'direccion.colonia.municipio.estado', 'tiposangre', 'nacionalidad', 'estado')->get();
            return ApiResponses::success('Escolar del cuc', 200, $escolares);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('No encontrado', 404);
        }
    }

    public function gruposPorCuc(Request $request, $cucId)
    {
        try {
            // Obtén el CUC
            $cuc = Cucs::findOrFail($cucId);

            // Obtiene los dos ultimos dígitos de la clave_cuc
            $primerosDosDigitosCuc = substr($cuc->clave_cuc, 7, 9);

            // Busca los grupos cuya clave_grupo comience con los dos primeros dígitos del CUC
            $grupos = Grupos::where('clave_grupo', 'like', $primerosDosDigitosCuc . '%')->with('carrera')->get();

            return ApiResponses::success('Grupos relacionados con el CUC', 200, $grupos);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function pruebaGruposPorCuc(Request $request)
    {
        try {
            // Verificar si el usuario autenticado es un consejero
            $usuarioEscolar = auth()->user();
            // Obtener la clave_cuc del consejero
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;
            // Obtener el CUC correspondiente a la clave_cuc del consejero
            $cuc = Cucs::where('clave_cuc', $claveCucEscolar)->first();

            if ($cuc) {
                // Obtiene los dos ultimos dígitos de la clave_cuc
                $primerosDosDigitosCuc = substr($cuc->clave_cuc, 7, 9);

                // Busca los grupos cuya clave_grupo comience con los dos ultimos dígitos del CUC
                $grupos = Grupos::where('clave_grupo', 'like', $primerosDosDigitosCuc . '%')->with('carrera')->get();

                return ApiResponses::success('Grupos relacionados con el CUC', 200, $grupos);
            }
            // }

            // return ApiResponses::error('Acceso no autorizado', 403);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function estudiantesPorGrupoPorCUC(Request $request, $claveGrupo)
    {
        try {
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;
            $cuc = Cucs::where('clave_cuc', $claveCucEscolar)->first();

            if ($cuc) {
                $ultimosDosDigitosCuc = substr($cuc->clave_cuc, 7, 9);
                // Verifica si la clave del grupo proporcionada comienza con los ultimos dos dígitos del CUC
                if (strpos($claveGrupo, $ultimosDosDigitosCuc) === 0) {
                    // Busca los estudiantes que pertenecen al grupo proporcionado
                    $estudiantes = Estudiantes::where('grupo_id', $claveGrupo)->get();
                    return ApiResponses::success('Estudiantes en el grupo ' . $claveGrupo, 200, $estudiantes);
                } else {
                    return ApiResponses::error('El grupo proporcionado no pertenece al CUC del usuario', 403);
                }
            }
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function estudiantesDeCUC(Request $request)
    {
        try {
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;
            $cuc = Cucs::findOrfail($claveCucEscolar);
            $numeroCuc = $cuc->numero;

            $estudiantess = Estudiantes::whereRaw("SUBSTRING(matricula, 1, 2) = ?", [$numeroCuc])->with(
                'usuario.rol',
                'grupo.carrera',
                'direccion.colonia.cp',
                'direccion.colonia.municipio.estado',
                'tiposangre',
                'lenguaindigena',
                'puebloindigena',
                'nacionalidad',
                'estado'
            )->get();

            $estudiantesTransformados = $estudiantess->map(function ($estudiante) {
                $documentos = [
                    [
                        'nombre' => 'Certificado de terminacion de estudios',
                        'estado' => boolval($estudiante->documento->certificado_terminacion_estudios),
                    ],
                    [
                        'nombre' => 'Acta de examen',
                        'estado' => boolval($estudiante->documento->acta_examen),
                    ],
                    [
                        'nombre' => 'Titulo electronico',
                        'estado' => boolval($estudiante->documento->titulo_electronico),
                    ],
                    [
                        'nombre' => 'Liberacion de servicio social',
                        'estado' => boolval($estudiante->documento->liberacion_servicio_social),
                    ],
                ];

                // Agrega el campo 'documentacion' con el formato personalizado al objeto estudiante
                $estudiante->documentacion = $documentos;

                // Elimina el campo 'documento' original
                unset ($estudiante->documento);

                return $estudiante;
            });

            return ApiResponses::success('Estudiantes en el cuc ', 200, $estudiantesTransformados);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function clasesDeCuc(Request $request)
    {
        try {
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;
            $cuc = Cucs::findOrFail($claveCucEscolar);

            $clases = Clases::where('clave_cuc', $claveCucEscolar)->with(
                'periodo',
                'materia',
                'facilitador',
                'cuc',
                'carrera'
            )->get();

            return ApiResponses::success('Clases en el cuc ', 200, $clases);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }


    public function estudiantesDeCUCServicio(Request $request)
    {
        try {
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;
            $cuc = Cucs::findOrfail($claveCucEscolar);
            $numeroCuc = $cuc->numero;
            $estudiantess = Estudiantes::whereRaw("SUBSTRING(matricula, 1, 2) = (?)", [$numeroCuc])
                ->where('servicio_estatus', '=', true)
                ->with(
                    'usuario.rol',
                    'grupo.carrera',
                    'direccion.colonia.cp',
                    'direccion.colonia.municipio.estado',
                    'tiposangre',
                    'lenguaindigena',
                    'puebloindigena',
                    'nacionalidad',
                    'estado'
                )
                ->get();
  $estudiantesTransformados = $estudiantess;
            return ApiResponses::success('Estudiantes en el cuc ', 200, $estudiantesTransformados);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }

    }
    public function candidatosDeCUCServicio(Request $request)
    {
        try {
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;
            $cuc = Cucs::findOrfail($claveCucEscolar);
            $numeroCuc = $cuc->numero;
            $estudiantess = Estudiantes::whereRaw("SUBSTRING(matricula, 1, 2) = (?)", [$numeroCuc])
                ->where('servicio_estatus', '=', false)
                ->with(
                    'usuario.rol',
                    'grupo.carrera',
                    'direccion.colonia.cp',
                    'direccion.colonia.municipio.estado',
                    'tiposangre',
                    'lenguaindigena',
                    'puebloindigena',
                    'nacionalidad',
                    'estado'
                )
                ->get();
            $estudiantesTransformados = $estudiantess;
            return ApiResponses::success('Estudiantes en el cuc ', 200, $estudiantesTransformados);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }


    public function bajasDeCUCServicio(Request $request)
    {
        try {
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;
            $cuc = Cucs::findOrFail($claveCucEscolar);
            $numeroCuc = $cuc->numero;
            $fechaLimite = Carbon::now()->subDays(65);

        $estudiantess = Estudiantes::whereRaw("SUBSTRING(matricula, 1, 2) = ?", [$numeroCuc])
            ->where('servicio_estatus', true)
            ->where(function ($query) use ($fechaLimite) {
                $query->where('estado_tramite_updated_at', '<', $fechaLimite)
                      ->orWhereNull('estado_tramite_updated_at');
            })

            ->with(
                    'usuario.rol',
                    'grupo.carrera',
                    'direccion.colonia.cp',
                    'direccion.colonia.municipio.estado',
                    'tiposangre',
                    'lenguaindigena',
                    'puebloindigena',
                    'nacionalidad',
                    'estado'
                )


            ->get();
    
            $estudiantesTransformados = $estudiantess;
    
            return ApiResponses::success('Estudiantes en el cuc', 200, $estudiantesTransformados);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }


    public function bajasGeneralServicio(Request $request)
    {
        try {
            $estudiante = Estudiantes::where('estado_tramite', 'BAJA POR INCUMPLIMIENTO')
            ->where('servicio_estatus',0)
            ->join('grupos', 'estudiantes.clave_grupo', '=', 'grupos.clave_grupo')
            ->join('cuc_carrera', 'grupos.clave_carrera', '=', 'cuc_carrera.clave_carrera')
            ->join('cucs', 'cuc_carrera.clave_cuc', '=', 'cucs.clave_cuc')
        
            ->join('servicios', 'estudiantes.matricula', '=', 'servicios.matricula')
            ->with(
                'usuario.rol',
                'grupo.carrera',
                'direccion.colonia.cp',
                'direccion.colonia.municipio.estado',
                'tiposangre',
                'lenguaindigena',
                'puebloindigena',
                'nacionalidad',
                'estado',  
                
            )
            ->select('estudiantes.*', 'servicios.*','cucs.nombre as cuc_nombre') // Selecciona todas las columnas de ambas tablas
            ->orderBy('estudiantes.updated_at', 'desc')
            ->get();
        
    
    
            return ApiResponses::success('Prestadores solicitados ', 200, $estudiante);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }







    public function activarServicio(Request $request, $matricula)
    {
        try {

            $estudiante = Estudiantes::where('matricula', $matricula)->firstOrFail();

            $id=$estudiante->id;
            $user = User::findOrFail($id);
            $correo = $user->email;

            $estudiante->servicio_estatus = true;
            $estudiante->estado_tramite="Inicio";
            $estudiante->estado_tramite_updated_at = Carbon::now();
            $estudiante->save();
    

            Mail::to( $correo)
                ->send(new ContactoMailable);


            return ApiResponses::success('Servicio Estatus actulizado', 200,$estudiante);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }



    public function cancelarServicio($matricula)
{
    try {
        // Obtener el estudiante por matrícula o lanzar una excepción si no se encuentra
        $estudiante = Estudiantes::where('matricula', $matricula)->firstOrFail();
        $id=$estudiante->id;

        $user = User::findOrFail($id);
        $correo = $user->email;

        // Actualizar el estado del estudiante
        $estudiante->servicio_estatus = 0;
        $estudiante->estado_tramite = "BAJA POR INCUMPLIMIENTO";
        $estudiante->save();

        
         Mail::to( $correo)
         ->send(new CancelacionMailable);

        return ApiResponses::success('Servicio Estatus actualizado', 200, $estudiante);
    } catch (ModelNotFoundException $ex) {
        return ApiResponses::error('Estudiante no encontrado', 404);
    } catch (Exception $e) {
        return ApiResponses::error('Error: ' . $e->getMessage(), 500);
    }
}



public function eliminarServicio($matricula)
{
    try {
       
    // Obtener el servicio por matrícula
    $servicio = Servicio::where('matricula', $matricula)->first();

    // Verificar si el servicio existe
    if ($servicio) {
    $idservicio = $servicio->id_servicio; // Asegúrate de que esta propiedad exista

        // Eliminar registros de todas las fases asociadas al servicio
        FaseUno::where('id_servicio', $idservicio)->delete();
        FaseDos::where('id_servicio', $idservicio)->delete();
        FaseTres::where('id_servicio', $idservicio)->delete();
        FaseCuatro::where('id_servicio', $idservicio)->delete();
        FaseCinco::where('id_servicio', $idservicio)->delete();
        FaseFinal::where('id_servicio', $idservicio)->delete();

      //  Eliminar el registro del servicio
        $servicio->delete();
}

        return ApiResponses::success('Servicio Estatus actualizado', 200);
    } catch (ModelNotFoundException $ex) {
        return ApiResponses::error('Estudiante no encontrado', 404);
    } catch (Exception $e) {
        return ApiResponses::error('Error: ' . $e->getMessage(), 500);
    }
}



    
    public function revisadoEstatus( $matricula)
    {
        try {
            $estudiante = Estudiantes::where('matricula', $matricula)->firstOrFail();

           
            $estudiante->estatus_envio = 2;
            $estudiante->save();
    
            return ApiResponses::success('Estatus revisado ', 200, $estudiante->estatus_envio);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }





    public function estudiantesTramite(Request $request)
    {
        try {
          
            $estudiante = Estudiantes::where('estado_tramite', 'Constancia solicitada')
    ->join('grupos', 'estudiantes.clave_grupo', '=', 'grupos.clave_grupo')
    ->join('cuc_carrera', 'grupos.clave_carrera', '=', 'cuc_carrera.clave_carrera')
    ->join('cucs', 'cuc_carrera.clave_cuc', '=', 'cucs.clave_cuc')

    ->join('servicios', 'estudiantes.matricula', '=', 'servicios.matricula')
    ->with(
        'usuario.rol',
        'grupo.carrera',
        'direccion.colonia.cp',
        'direccion.colonia.municipio.estado',
        'tiposangre',
        'lenguaindigena',
        'puebloindigena',
        'nacionalidad',
        'estado',  
        
    )
    ->select('estudiantes.*', 'servicios.*','cucs.nombre as cuc_nombre') // Selecciona todas las columnas de ambas tablas
    ->orderBy('estudiantes.updated_at', 'desc')
    ->get();

            return ApiResponses::success('Prestadores solicitados ', 200, $estudiante);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }


}
