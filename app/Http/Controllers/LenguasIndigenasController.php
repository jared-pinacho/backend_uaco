<?php

namespace App\Http\Controllers;

use App\Models\LenguasIndigenas;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponses;
use Exception;

class LenguasIndigenasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $lenguasind = LenguasIndigenas::all();
            return ApiResponses::success('Lista de lenguas indigenas', 200, $lenguasind);
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
    public function show(LenguasIndigenas $lenguasIndigenas)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LenguasIndigenas $lenguasIndigenas)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LenguasIndigenas $lenguasIndigenas)
    {
        //
    }
}
