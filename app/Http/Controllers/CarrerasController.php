<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\Carreras;
use App\Models\Cucs;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CarrerasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //$clavec = $request->clavecu;
        //$carrera = Carreras::where('clave_cuc', $clavec)->get();
        try {
            $carrera = Carreras::all();
            return ApiResponses::success('Lista de Programas', 200, $carrera);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        try {
            $request->validate([
                'clave' => 'required|unique:carreras,clave_carrera',
                'nombre' => 'required',
                'grado' => 'required',
                'creditos' => 'required',
                'periodicidad' => 'required',
                'duracion' => 'required',
                'modalidad' => 'required'


            ], [
                'clave.unique' => 'La clave ya está en uso.', // Personaliza el mensaje de error para 'clave'
            ]);
            $carrera = new Carreras();

            $carrera->clave_carrera = $request->input('clave');
            $carrera->nombre = $request->nombre;
            $carrera->grado = $request->grado;
            $carrera->creditos = $request->creditos;
            $carrera->periodicidad = $request->periodicidad;
            $carrera->duracion = $request->duracion;
            $carrera->modalidad = $request->modalidad;
            $carrera->save();
            return ApiResponses::success('Registro exitoso', 201, $carrera);
            //return $cuc;

        } catch (ValidationException $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        } catch (\Exception $ex) {
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
            $carrera = Carreras::findOrFail($id);
            return ApiResponses::success('Encontrado', 200, $carrera);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('No encontrado', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idCarrera)
    {
        //
        try {
            $request->validate([
                //'clave'=>'required',
                'nombre' => 'required',
                'grado' => 'required',
                'creditos' => 'required',
                'periodicidad' => 'required',
                'duracion' => 'required',
                'modalidad' => 'required'

            ]);

            //$carrera -> clave_carrera = $request->input('clave');
            $carrera = Carreras::findOrFail($idCarrera);
            $carrera->nombre = $request->nombre;
            $carrera->grado = $request->grado;
            $carrera->creditos = $request->creditos;
            $carrera->periodicidad = $request->periodicidad;
            $carrera->duracion = $request->duracion;
            $carrera->modalidad = $request->modalidad;
            $carrera->update();
            return ApiResponses::success('Actualizado', 201);
            //return $cuc;

        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //

        try {
            $carrera = Carreras::findOrFail($id);
            if ($carrera->cucs->isNotEmpty()) {
                return ApiResponses::error('No se puede eliminar el programa porque hay CUCs asociados al programa', 422);
            }
            if ($carrera->grupos->isNotEmpty()) {
                return ApiResponses::error('No se puede eliminar el programa porque hay grupos asociados al programa', 422);
            }
            $carrera->delete();
            return ApiResponses::success('Programa Eliminado', 201);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('No encontrado', 404);
        }
    }


    public function asociarCuc(Request $request)
    {
        DB::beginTransaction(); // Iniciar la transacción
        try {
            $usuarioConsejero = auth()->user();

            // Obtener la clave_cuc del consejero
            $claveCucConsejero = $usuarioConsejero->consejero->clave_cuc;
            $carrera = Carreras::findOrFail($request->carreraId);
            $cuc = Cucs::findOrFail($claveCucConsejero);

            $carrera->cucs()->syncWithoutDetaching([$claveCucConsejero]); //SyncWith..Para que no haya asociaciones repetidas

            DB::commit(); // Confirma la transacción si todas las operaciones se completaron con éxito

            return ApiResponses::success('Asociado', 201);
        } catch (ModelNotFoundException $ex) {
            DB::rollBack(); // Revierte la transacción en caso de excepción
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            DB::rollBack(); // Revierte la transacción en caso de excepción
            return ApiResponses::error('Error: ' . $e->getMessage(), 422);
        }
    }

    public function eliminarAsociacionCuc(Request $request)
    {
        DB::beginTransaction();
        try {
            $usuarioConsejero = auth()->user();

            // Obtener la clave_cuc del consejero
            $claveCucConsejero = $usuarioConsejero->consejero->clave_cuc;
            $carrera = Carreras::findOrFail($request->carreraId);
            $cuc = Cucs::findOrFail($claveCucConsejero);

            $grupos = $carrera->grupos;
            foreach ($grupos as $grupo) {
                $claveGrupo = $grupo->clave_grupo;
                // Verificar si los dos primeros caracteres coinciden con $claveCuc
                if (substr($claveGrupo, 0, 3) === substr($claveCucConsejero, -3)) {
                    return ApiResponses::error('No se puede eliminar el programa porque tiene grupos asociados en este CUC', 422);
                }
            }
            $carrera->cucs()->detach($claveCucConsejero);
            DB::commit();

            return ApiResponses::success('Programa Eliminado', 200);
        } catch (ModelNotFoundException $ex) {
            DB::rollBack();
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 422);
        }
    }

    public function gruposPorCarrera(Request $request, $claveCarrera)
    {
        try {
            // $carrera = Carreras::where('clave_carrera', $claveCarrera)->first();
            //$carrera = Carreras::findOrFail($claveCarrera);
            $carrera = Carreras::with('grupos')->findOrFail($claveCarrera);
            //$grupos = $carrera->grupos;
            return ApiResponses::success('Grupos del programa', 200, $carrera);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('No encontrado', 404);
        }
    }

    public function materiasPorCarrera(Request $request, $carreraId)
    {
        try {
            $carrera = Carreras::findOrFail($carreraId);
            $materiasAsociadas = $carrera->materias;
            return ApiResponses::success('Lista de Materias del programa', 200, $materiasAsociadas);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function gruposPorCarreraPorCuc(Request $request, $claveCarrera, $claveCuc)
    {
        try {
            $carrera = Carreras::where('clave_carrera', $claveCarrera)->firstOrFail();
            $cuc = Cucs::where('clave_cuc', $claveCuc)->firstOrFail();
            $grupos = $carrera->grupos;
            $gruposCumplenCondicion = [];
            foreach ($grupos as $grupo) {
                $claveGrupo = $grupo->clave_grupo;
                // Verificar si los dos primeros caracteres coinciden con $claveCuc
                if ((int)substr($claveGrupo, 0, 2) === (int)substr($claveCuc, 7, 9)) {
                    $gruposCumplenCondicion[] = $grupo;
                }
            }
            return ApiResponses::success('Grupos de cuc y programa', 200, $gruposCumplenCondicion);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function pruebaGruposPorCarreraPorCuc(Request $request, $claveCarrera)
    {
        try {

            $usuarioEscolar = auth()->user();

            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;
            $carrera = Carreras::where('clave_carrera', $claveCarrera)->firstOrFail();
            $cuc = Cucs::where('clave_cuc', $claveCucEscolar)->firstOrFail();
            $grupos = $carrera->grupos()->with('carrera')->get();
            $gruposCumplenCondicion = [];
            foreach ($grupos as $grupo) {
                $claveGrupo = $grupo->clave_grupo;
                // Verificar si los dos primeros caracteres coinciden con $claveCuc
                if (substr($claveGrupo, 0, 3) === substr($claveCucEscolar, -3)) {

                    $gruposCumplenCondicion[] = $grupo;
                }
            }
            return ApiResponses::success('Grupos de cuc y programa xd', 200, ['grupos' => $gruposCumplenCondicion]);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function totalCarrerasPorCuc(Request $request)
    {
        try {
            // Verificar si el usuario autenticado es un consejero
            $usuarioConsejero = auth()->user();
            $rol = $usuarioConsejero->rol->nombre;

            if ($rol === 'consejero') {
                // Obtener la clave_cuc del consejero
                $claveCuc = $usuarioConsejero->consejero->clave_cuc;
            }else if($rol === 'escolar'){
                $claveCuc = $usuarioConsejero->escolar->clave_cuc;
            }
                // Obtener el CUC correspondiente a la clave_cuc del consejero
                $cuc = Cucs::where('clave_cuc', $claveCuc)->first();

                if ($cuc) {
                    // Obtener las carreras asociadas al CUC
                    $carrerasAsociadas = $cuc->carreras;
                    $totalcarreras = $carrerasAsociadas->count();
                    return ApiResponses::success('Total de programas en este CUC', 200, $totalcarreras);
                }
            // }

            // return ApiResponses::error('Acceso no autorizado', 403);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function totalCarrerasPorCucCoordinador($claveCuc)
    {
        try {
            $cuc = Cucs::where('clave_cuc', $claveCuc)->first();
            $carrerasAsociadas = $cuc->carreras;
            $totalcarreras = $carrerasAsociadas->count();
            return ApiResponses::success('Total de programas en este CUC', 200, $totalcarreras);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function totalCarrerasCoordinador(Request $request)
    {
        try {
            $carrera = Carreras::all();
            $totalCarreras = $carrera->count();
            return ApiResponses::success('Total de Programas', 200, $totalCarreras);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function totalGruposPorCarreraPorCuc($claveCarrera)
    {
        try {
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;
            $cuc = Cucs::findOrfail($claveCucEscolar);
            $claveCuc = $cuc->clave_cuc;

            $carrera = Carreras::where('clave_carrera', $claveCarrera)->firstOrFail();
            $cuc = Cucs::where('clave_cuc', $claveCucEscolar)->firstOrFail();
            $grupos = $carrera->grupos;

            $gruposCumplenCondicion = [];
            foreach ($grupos as $grupo) {
                $claveGrupo = $grupo->clave_grupo;
                // Verificar si los dos primeros caracteres coinciden con $claveCuc
                if ((int)substr($claveGrupo, 0, 2) === (int)substr($claveCucEscolar, 7, 9)) {
                    $gruposCumplenCondicion[] = $grupo;
                }
            }

            $totalGrupos = count($gruposCumplenCondicion);

            return ApiResponses::success('total de grupos', 200, ['total_grupos' => $totalGrupos]);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function totalGruposPorCarreraPorCucCoordiandor($claveCuc, $claveCarrera)
    {
        try {
            $cuc = Cucs::findOrfail($claveCuc);

            $carrera = Carreras::where('clave_carrera', $claveCarrera)->firstOrFail();
            $cuc = Cucs::where('clave_cuc', $claveCuc)->firstOrFail();
            $grupos = $carrera->grupos;

            $gruposCumplenCondicion = [];
            foreach ($grupos as $grupo) {
                $claveGrupo = $grupo->clave_grupo;
                // Verificar si los dos primeros caracteres coinciden con $claveCuc
                if ((int)substr($claveGrupo, 0, 2) === (int)substr($claveCuc, 7, 9)) {
                    $gruposCumplenCondicion[] = $grupo;
                }
            }

            $totalGrupos = count($gruposCumplenCondicion);

            return ApiResponses::success('total de grupos', 200, ['total_grupos' => $totalGrupos]);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

}
