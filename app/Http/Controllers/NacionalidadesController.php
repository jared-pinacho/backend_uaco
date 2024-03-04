<?php

namespace App\Http\Controllers;

use App\Models\Nacionalidades;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponses;
use Exception;

class NacionalidadesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $nacionalidad = Nacionalidades::all();
            return ApiResponses::success('Lista de Nacionalidades', 200, $nacionalidad);
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
    public function show(Nacionalidades $nacionalidades)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nacionalidades $nacionalidades)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nacionalidades $nacionalidades)
    {
        //
    }
}
