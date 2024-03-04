<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\Clases;
use App\Models\Dias;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $dia = Dias::all();
            return ApiResponses::success('Lista de Dias', 200, $dia);
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
    public function show(Dias $dias)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dias $dias)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dias $dias)
    {
        //
    }
}
