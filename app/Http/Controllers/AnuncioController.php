<?php

namespace App\Http\Controllers;

use App\Models\Anuncio;
use App\Models\cuc_carrera;
use Illuminate\Http\Request;
use App\Models\Escolares;
use App\Http\Responses\ApiResponses;
use App\Models\Estudiantes;
use App\Models\Grupos;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class AnuncioController extends Controller
{



    public function show($id)
    {
        try {
            $anuncio = Anuncio::where('id_anuncio', $id)->firstOrFail();
    
            return ApiResponses::success('Encontrado', 200, $anuncio);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('Error: Anuncio no encontrado', 404);
        } catch (Exception $e) {
            // Handle general exceptions (e.g., database errors)
            return ApiResponses::error('Error interno: ' . $e->getMessage(), 500);
        }
    }



    public function anunciosDeCUC(Request $request)
    {
        try {
            $usuarioEscolar = auth()->user();   
           $matricula=$usuarioEscolar->id;
           $user = Auth::user();
           $id = $user->id;
           $escolar = Escolares::where('id', $id)->first();
          $matricula= $escolar->matricula;
          
          $anuncios = Anuncio::where('matricula', $matricula)
          ->orderBy('created_at', 'desc')
          ->get();    
           
           
        //    $anuncios = Anuncio::where('matricula', $matricula)
        //    ->orderBy('fecha_creacion', 'desc')
        //    ->get();   

            return ApiResponses::success('Estudiantes en el CUC', 200, $anuncios);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function anuncios(Request $request)
{
    try {   
        $user = Auth::user();
        $id = $user->id;
        $estudiante= Estudiantes::where('id',$id)->first();
        $clave_grupo = $estudiante->clave_grupo;
        $grupo = Grupos::find($clave_grupo);
        $clave_carrera= $grupo->clave_carrera;
        $cuc_carrera = cuc_carrera::where('clave_carrera',$clave_carrera)->first();
        $clave_cuc = $cuc_carrera->clave_cuc;
        $escolar = Escolares::where('clave_cuc',$clave_cuc)->first();    
        $matricula= $escolar->matricula;
        $anuncios = Anuncio::where('matricula',$matricula)
        ->orderBy('created_at', 'desc')
        ->get();

        return ApiResponses::success('Anuncio encontrado', 200, $anuncios);
    } catch (ModelNotFoundException $e) {
        return ApiResponses::error('Error: ' . $e->getMessage(), 404);
    }
}


public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'titulo' => 'required',
                'descripcion' => 'required',
                'fecha' => 'required',
            ], );       
            $user = Auth::user();
            $id = $user->id;
            $escolar = Escolares::where('id', $id)->firstOrFail();
            $matri = $escolar->matricula;
            $anuncio = new Anuncio();
            $anuncio->titulo = $request->titulo;
            $anuncio->descripcion = $request->descripcion;
            $anuncio->fecha = $request->fecha;
            $anuncio->matricula=$matri;        
            $anuncio->save();

            DB::commit();
            return ApiResponses::success('Registro exitoso', 201, $anuncio);
        } catch (ValidationException $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        } catch (Exception $ex) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $ex->getMessage(), 500);
        }
    }


    public function update(Request $request, $idEstudiante)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'titulo' => 'required',
                'descripcion' => 'required',
                'fecha' => 'required',
            ]);
    
            $anuncio = Anuncio::findOrFail($idEstudiante);
            $matri=$anuncio->matricula;
            $anuncio->titulo = $request->titulo;
            $anuncio->descripcion = $request->descripcion;
            $anuncio->fecha = $request->fecha;
            $anuncio->matricula=$matri;
            $anuncio->update();
  
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


    public function destroy($id)
{
    DB::beginTransaction();
    try {
        $anuncio = Anuncio::findOrFail($id);
        $anuncio->delete();
        DB::commit();
        return response()->json(['message' => 'Anuncio eliminado correctamente'], 200);
    } catch (ModelNotFoundException $e) {
        DB::rollBack();
        return response()->json(['error' => 'Anuncio no encontrado'], 404);
    } catch (Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
    }
}
}
