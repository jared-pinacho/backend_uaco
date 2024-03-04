<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\AreasConsejeria;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AreasConsejeriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $areas = AreasConsejeria::all();
            return ApiResponses::success('Lista de Areas consejeria', 200, $areas);
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
    public function show(AreasConsejeria $areasConsejeria)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AreasConsejeria $areasConsejeria)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AreasConsejeria $areasConsejeria)
    {
        //
    }
}
