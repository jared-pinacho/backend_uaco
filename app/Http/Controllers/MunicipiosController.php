<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\Estados;
use App\Models\Municipios;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class MunicipiosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        try {
            $municipio = Municipios::with('estado')->get();
            return ApiResponses::success('Lista de Municipios', 200, $municipio);
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
    public function show(Municipios $municipios)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Municipios $municipios)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Municipios $municipios)
    {
        //
    }
    public function municipiosPorEstado($estadoId)
    {
        try {
            $estado = Estados::findOrFail($estadoId);
            $municipios = $estado->municipios;
            return ApiResponses::success('Lista de municipios de un estado', 200, $municipios);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }
}
