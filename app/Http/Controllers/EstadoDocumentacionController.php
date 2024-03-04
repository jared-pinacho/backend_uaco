<?php

namespace App\Http\Controllers;


use App\Http\Responses\ApiResponses;
use App\Models\estadoDocumentacion;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstadoDocumentacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $documentos = estadoDocumentacion::all();
            return ApiResponses::success('Lista de documentos', 200, $documentos);
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
    public function show(estadoDocumentacion $estadoDocumentacion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $claveEstudiante)
    {
        DB::beginTransaction();
        try{
            $request->validate([
                'documentacion' => 'array'
            ]);       
            
            $documentosEst = estadoDocumentacion::where('matricula', $claveEstudiante)->first();

            foreach ($request->documentacion as $documento) {
                $nombreDocumento = $documento['nombre'];
                $estadoDocumento = $documento['estado'];
            
                switch ($nombreDocumento) {
                    case 'Certificado de terminacion de estudios':
                        $documentosEst->certificado_terminacion_estudios = ($estadoDocumento === true);
                        break;
                    case 'Acta de examen':
                        $documentosEst->acta_examen = ($estadoDocumento === true);
                        break;
                    case 'Titulo electronico':
                        $documentosEst->titulo_electronico = ($estadoDocumento === true);
                        break;
                    case 'Liberacion de servicio social':
                        $documentosEst->liberacion_servicio_social = ($estadoDocumento === true);
                        break;
                }
            
                $documentosEst->save();
            }

            DB::commit();
            return ApiResponses::success('Actualizado', 201);
        } catch (ModelNotFoundException $ex) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $ex->getMessage(), 404);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(estadoDocumentacion $estadoDocumentacion)
    {
        //
    }
}
