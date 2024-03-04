<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\Coordinadores;
use App\Models\User;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CoordinadoresController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $coordinador = Coordinadores::with('usuario.rol')->get();
        return ApiResponses::success('Lista de Coordinadores',200,$coordinador);
        }catch(Exception $e){
            return ApiResponses::error('Error: '.$e->getMessage(),500);
        } 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'matricula' => 'required|unique:coordinadores,matricula',
                'nombre' => 'required',
                'apellidopaterno' => 'required',
                'apellidomaterno' => 'required',
                'curp' => 'required',
                'rfc' => 'required',
                'niveleducativo' => 'required',
                'email' => 'required|unique:users,email',
                'password' => 'required',
            ], [
                'matricula.unique' => 'La matricula ya está en uso.', // Personaliza el mensaje de error para 'clave'
                'email.unique' => 'El correo ya esta en uso'
            ]);

            $usuario = new User();
            $usuario->email=$request->email;
            $usuario->password=$request->password;
            $usuario->id_rol=2;
            $usuario->save();
            $coordinador = new Coordinadores();
            $coordinador->matricula =$request->input('matricula');
            $coordinador->nombre = $request->nombre;
            $coordinador->apellido_paterno = $request->apellidopaterno;
            $coordinador->apellido_materno = $request->apellidomaterno;
            $coordinador->curp = $request->curp;
            $coordinador->rfc = $request->rfc;
            $coordinador->nivel_educativo = $request->niveleducativo;

            $coordinador->usuario()->associate($usuario);
            $coordinador->save();
            
            
            return ApiResponses::success('Registro exitoso',201,$coordinador);
            //return $cuc;

        }catch(ValidationException $e){
            return ApiResponses::error('Error: '.$e->getMessage(),500); 
        }catch (\Exception $ex) {
            return ApiResponses::error('Error: '.$ex->getMessage(),500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try{
            $coordinador = Coordinadores::with('usuario.rol')->findOrFail($id);
                return ApiResponses::success('Encontrado',200,$coordinador);   
        }catch(ModelNotFoundException $e){
            return ApiResponses::error('Error: '.$e->getMessage(),404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idCoordinador)
    {
        try {
            $request->validate([
                'nombre' => 'required',
                'apellidopaterno' => 'required',
                'apellidomaterno' => 'required',
                'curp' => 'required',
                'rfc' => 'required',
                'niveleducativo' => 'required',
                'email' => 'required',
            ]);
            $coordinador=Coordinadores::findOrFail($idCoordinador);
            $usuario = $coordinador -> usuario;
            $usuario->email=$request->email;
            $usuario->update();
            $coordinador->matricula =$request->input('matricula');
            $coordinador->nombre = $request->nombre;
            $coordinador->apellido_paterno = $request->apellidopaterno;
            $coordinador->apellido_materno = $request->apellidomaterno;
            $coordinador->curp = $request->curp;
            $coordinador->rfc = $request->rfc;
            $coordinador->nivel_educativo = $request->niveleducativo;
            $coordinador->update();
            return ApiResponses::success('Actualizado',201);

        }catch(ModelNotFoundException $ex){
            return ApiResponses::error('Error: '.$ex->getMessage(),404); 
        }catch (Exception $e) {
            return ApiResponses::error('Error: '.$e->getMessage(),500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
            $coordinador = Coordinadores::findOrFail($id);
            $usuario = $coordinador -> usuario;
            if($usuario){
                $usuario ->delete();
            }
                $coordinador->delete();
                return ApiResponses::success('Eliminado',201);
        }catch(ModelNotFoundException $e){
            return ApiResponses::error('Error: '.$e->getMessage(),404);
        }
    }
}
