<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\Administrativos;
use App\Models\User;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class AdministrativosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $administrativo = Administrativos::with('usuario.rol')->get();
        return ApiResponses::success('Lista de Administrativos',200,$administrativo);
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
                'matricula' => 'required|unique:administrativos,matricula',
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
            $usuario->id_rol=1;
            $usuario->save();
            $administrativo = new Administrativos();
            $administrativo->matricula =$request->input('matricula');
            $administrativo->nombre = $request->nombre;
            $administrativo->apellido_paterno = $request->apellidopaterno;
            $administrativo->apellido_materno = $request->apellidomaterno;
            $administrativo->curp = $request->curp;
            $administrativo->rfc = $request->rfc;
            $administrativo->nivel_educativo = $request->niveleducativo;

            $administrativo->usuario()->associate($usuario);
            $administrativo->save();
            
            
            return ApiResponses::success('Registro exitoso',201,$administrativo);
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
            $administrativo = Administrativos::with('usuario.rol')->findOrFail($id);
                return ApiResponses::success('Encontrado',200,$administrativo);   
        }catch(ModelNotFoundException $e){
            return ApiResponses::error('Error: '.$e->getMessage(),404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idAdministrativo)
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
            $administrativo=Administrativos::findOrFail($idAdministrativo);
            $usuario = $administrativo -> usuario;
            $usuario->email=$request->email;
            $usuario->update();
            $administrativo->matricula =$request->input('matricula');
            $administrativo->nombre = $request->nombre;
            $administrativo->apellido_paterno = $request->apellidopaterno;
            $administrativo->apellido_materno = $request->apellidomaterno;
            $administrativo->curp = $request->curp;
            $administrativo->rfc = $request->rfc;
            $administrativo->nivel_educativo = $request->niveleducativo;
            $administrativo->update();
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
            $administrativo = Administrativos::findOrFail($id);
            $usuario = $administrativo -> usuario;
            if($usuario){
                $usuario ->delete();
            }
                $administrativo->delete();
                return ApiResponses::success('Eliminado',201);
        }catch(ModelNotFoundException $e){
            return ApiResponses::error('Error: '.$e->getMessage(),404);
        }
    }
}
