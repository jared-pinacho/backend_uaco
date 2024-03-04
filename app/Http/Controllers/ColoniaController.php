<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\Colonia;
use Exception;
use Illuminate\Http\Request;

class ColoniaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        try {
            $colonia = Colonia::with('cp','municipio')->get();
            return ApiResponses::success('Lista de Colonias', 200, $colonia);
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
    public function show(Colonia $colonia)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Colonia $colonia)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Colonia $colonia)
    {
        //
    }
}
