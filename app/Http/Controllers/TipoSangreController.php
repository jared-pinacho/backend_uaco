<?php

namespace App\Http\Controllers;

use App\Models\TipoSangre;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponses;
use Exception;

class TipoSangreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $tiposangre = TipoSangre::all();
            return ApiResponses::success('Lista de tipos de sangre', 200, $tiposangre);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }


    public function regresaSangre(Request $request)
    {
        try {
            $tiposangre = TipoSangre::all();
            return ApiResponses::success('Lista de tipos de sangre', 200, $tiposangre);
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
    public function show(TipoSangre $tipoSangre)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoSangre $tipoSangre)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoSangre $tipoSangre)
    {
        //
    }
}
