<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\Periodos;
use App\Models\Carreras;
use Dotenv\Exception\ValidationException;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeriodosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $periodo = Periodos::all();
            return ApiResponses::success('Lista de Periodos', 200, $periodo);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|unique:periodos,nombre',
                'periodicidad' => 'required',
                'fecha_inicio' => 'required',
                'fecha_final' => 'required'
            ],[
                'nombre.unique' => 'El nombre ya se encuentra en uso'
            ]);

            $periodo = new Periodos();

            $periodo->nombre = $request->nombre;
            $periodo->periodicidad = $request->periodicidad;
            $periodo->fecha_inicio = $request->fecha_inicio;
            $periodo->fecha_final = $request->fecha_final;

            $periodo->save();
            return ApiResponses::success('Registro exitoso', 201, $periodo);
        } catch (ValidationException $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        } catch (Exception $ex) {
            return ApiResponses::error('Error: ' . $ex->getMessage(), 500);
        }
        
    }

    /**
     * Display the specified resource.
     */
    public function show($idPeriodo)
    {
        try {
            $periodo = Periodos::findOrFail($idPeriodo);
            return ApiResponses::success('Encontrado', 200, $periodo);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exeption $ex){
            return ApiResponses::error('Error: ' . $ex->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idPeriodo)
    {
        try {
            $request->validate([
                'nombre' => 'required',
                'periodicidad' => 'required',
                'fecha_inicio' => 'required',
                'fecha_final' => 'required'
            ]);

            $periodo = Periodos::findOrFail($idPeriodo);

            $periodo->nombre = $request->nombre;
            $periodo->periodicidad = $request->periodicidad;
            $periodo->fecha_inicio = $request->fecha_inicio;
            $periodo->fecha_final = $request->fecha_final;
            $periodo->update();

            return ApiResponses::success('Actualizado', 201);

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
        try{
            $periodo = Periodos::findOrFail($id);
                $periodo->delete();
                return ApiResponses::success('Periodo Eliminado',201);
        }catch(ModelNotFoundException $e){
            return ApiResponses::error('Error: '.$e->getMessage(),404);
        }

        // try {
        //     $periodo = Periodos::findOrFail($id);
        //     if ($periodo->clases->isNotEmpty()) {
        //         return ApiResponses::error('No se puede eliminar el periodo porque hay Clases asociados al periodo', 422);
        //     }
        //     $carrera->delete();
        //     return ApiResponses::success('Periodo Eliminado', 201);
        // } catch (ModelNotFoundException $e) {
        //     return ApiResponses::error('No encontrado', 404);
        // }
    }

    public function periodosAnioActual(Request $request)
    {
        try {
            $currentDate = Carbon::now()->toDateString(); 

            $periodos = Periodos::where('fecha_inicio', '<=', $currentDate)
                ->where('fecha_final', '>=', $currentDate)
                ->get();

            return ApiResponses::success('Lista de Periodos', 200, $periodos);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function periodosAnioActualporCarrera($claveCarrera)
    {
        try {
            $currentDate = Carbon::now()->toDateString(); 
            $carrera = Carreras::findOrFail($claveCarrera);
            $periodicidad = $carrera->periodicidad;
            $periodos = Periodos::where('fecha_inicio', '<=', $currentDate)
                ->where('fecha_final', '>=', $currentDate)
                ->where('periodicidad', $periodicidad)
                ->get();

            return ApiResponses::success('Lista de Periodos', 200, $periodos);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function periodosPorCarrera($claveCarrera)
    {
        try {
            $currentDate = Carbon::now()->toDateString(); 
            $carrera = Carreras::findOrFail($claveCarrera);
            $periodicidad = $carrera->periodicidad;
            $periodos = Periodos::where('periodicidad', $periodicidad)
                ->get();

            return ApiResponses::success('Lista de Periodos', 200, $periodos);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }
}
