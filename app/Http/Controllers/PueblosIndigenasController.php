<?php

namespace App\Http\Controllers;

use App\Models\PueblosIndigenas;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponses;
use Exception;

class PueblosIndigenasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $pueblos = PueblosIndigenas::all();
            return ApiResponses::success('Lista de pueblos indigenas', 200, $pueblos);
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
    public function show(PueblosIndigenas $pueblosIndigenas)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PueblosIndigenas $pueblosIndigenas)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PueblosIndigenas $pueblosIndigenas)
    {
        //
    }
}
