<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\Carreras;
use App\Models\Materias;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MateriasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $materia = Materias::all();
            return ApiResponses::success('Lista de Materias', 200, $materia);
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
                'clave_materia' => 'required|unique:materias,clave_materia',
                'nombre' => 'required',
                'creditos' => 'required',
                'carreras' => 'array',
            ],[
                'clave_materia.unique' => 'La clave ya está en uso.', // Personaliza el mensaje de error para 'clave'
            ]);

            $materia = new Materias();
            $materia->clave_materia = $request->clave_materia;
            $materia->nombre = $request->nombre;
            $materia->creditos = $request->creditos;

            $materia->save();

            if (!empty($request->carreras)) {
                $materia = Materias::findOrFail($request->clave_materia);
                foreach ($request->carreras as $claveCarrera) {
                    $carrera = Carreras::where('clave_carrera', $claveCarrera)->firstOrFail();
                    $materia->carreras()->syncWithoutDetaching([$carrera->clave_carrera]);
                }
            }

            DB::commit();
            return ApiResponses::success('Registro exitoso', 201, $materia);
        } catch (ValidationException $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }catch (ModelNotFoundException $es){
            DB::rollBack();
            return ApiResponses::error('Error no encontrado: ' . $es->getMessage(), 500);
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
        try {
            $materia = Materias::findOrFail($id);
           $claves_carreras = $materia->carreras->pluck('clave_carrera')->all(); 
           $materia->carrerasR = $claves_carreras;
            return ApiResponses::success('Encontrado', 200, $materia);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idMateria)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'nombre' => 'required',
                'creditos' => 'required',
                'carreras' => 'array',
            ]);

            $materia=Materias::findOrFail($idMateria);

            $carrerasSeleccionadas = $request->carreras;
            $carrerasActuales = $materia->carreras->pluck('clave_carrera')->all();

            $carrerasADesasociar = array_diff($carrerasActuales, $carrerasSeleccionadas);
            $materia->carreras()->detach($carrerasADesasociar);

            $carrerasAAsociar = array_diff($carrerasSeleccionadas, $carrerasActuales);
            $carreras = Carreras::whereIn('clave_carrera', $carrerasAAsociar)->get();
            $materia->carreras()->attach($carreras);

            $materia->nombre = $request->nombre;
            $materia->creditos = $request->creditos;
            $materia->update();

            DB::commit();
            return ApiResponses::success('Actualizado',201);

        }catch(ModelNotFoundException $ex){
            DB::rollBack();
            return ApiResponses::error('Error: '.$ex->getMessage(),404);
        }catch (Exception $e) {
            DB::rollBack();
            return ApiResponses::error('Error: '.$e->getMessage(),500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
            $materia = Materias::findOrFail($id);
                $materia->delete();
                return ApiResponses::success('Materia Eliminado',201);
        }catch(ModelNotFoundException $e){
            return ApiResponses::error('Error: '.$e->getMessage(),404);
        }
    }
}
