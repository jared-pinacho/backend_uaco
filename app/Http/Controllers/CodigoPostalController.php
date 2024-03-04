<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\CodigoPostal;
use Exception;
use Illuminate\Http\Request;

class CodigoPostalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        try {
            $cp = CodigoPostal::all();
            return ApiResponses::success('Lista de Codigos', 200, $cp);
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
    }

    /**
     * Display the specified resource.
     */
    public function show(CodigoPostal $codigoPostal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CodigoPostal $codigoPostal)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CodigoPostal $codigoPostal)
    {
        //
    }

    public function coloniasMunicipioEstadoDeCodigoP($cpId)
    {
        try {
            $cp = CodigoPostal::findOrFail($cpId);
            $colonias = $cp->colonias;
            if ($colonias->isEmpty()) {
                return ApiResponses::error('No hay colonias asociadas a este código postal', 404);
            }
            $primeraColonia = $colonias->first();
            $municipio = $primeraColonia->municipio;
            $estado = $municipio->estado;
            $data = [
                'colonias' => $colonias->map(function ($colonia) {
                    unset($colonia['municipio']);
                    return $colonia->toArray();
                })->toArray(),
                'municipio' => $municipio->toArray(),
                'estado' => $estado->toArray(),
            ];
    
            return ApiResponses::success('Información de colonias, municipio y estado', 200, $data);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }
}
