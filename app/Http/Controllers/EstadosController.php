<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\Estados;
use Exception;
use Illuminate\Http\Request;

class EstadosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        try {
            $estado = Estados::all();
            return ApiResponses::success('Lista de Estados', 200, $estado);
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
    public function show(Estados $estados)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Estados $estados)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Estados $estados)
    {
        //
    }

    
}
