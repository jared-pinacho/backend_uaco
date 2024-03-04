<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\Facilitadores;
use App\Models\Cucs;
use App\Models\User;
use App\Models\CodigoPostal;
use App\Models\Colonia;
use App\Models\Direcciones;
use App\Models\Estados;
use App\Models\Municipios;
use App\Models\Clases;
use App\Models\Nacionalidades;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FacilitadoresController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // $facilitador = Facilitadores::with('usuario.rol')->get();
            $facilitador = Facilitadores::with('usuario.rol', 'direccion.colonia.cp', 'direccion.colonia.municipio.estado','tiposangre','nacionalidad', 'estado')
            ->selectRaw('*, CONCAT(nombre, " ", apellido_paterno, " ", apellido_materno) as nombreC')
            ->get();
            return ApiResponses::success('Lista de Facilitadores', 200, $facilitador);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'matricula' => 'required | unique:facilitadores,matricula',
                'nombre' => 'required',
                'apellidopaterno' => 'required',
                'apellidomaterno' => 'required',
                'fecha_nacimiento'=> 'required',
                'sexo'=> 'required',
                'rfc' => 'required',
                'niveleducativo' => 'required',
                'perfil_academico' => 'required',
                'telefono' => 'required',
                'id_tiposangre' => 'required',
                'telefono_emergencia' => 'required',
                'padecimiento' => 'required',
                'calle' => 'required',
                'num_exterior' => 'required',
                'colonia' => 'required',
                'nacionalidad' => 'required',
                // 'municipio' => 'required',
                // 'cp' => 'required',
                'email' => 'required|unique:users,email',
                'password' => 'required',
            ], [
                'matricula.unique' => 'La matricula ya está en uso.', // Personaliza el mensaje de error para 'clave'
                'email.unique' => 'El correo ya esta en uso'
            ]);
            $nacionaalidad = $request->otra_nacionalidad;
            $nacionalidad = (string) $request->nacionalidad;
            // $cp = new CodigoPostal();
            // $cp->codigo = $request->input('cp');
            // $cp->save();

            // $colonia = new Colonia();
            // $colonia->nombre = $request->input('colonia');
            // $colonia->cp()->associate($cp);
            // $colonia->id_municipio = $request->input('municipio');
            // $colonia->save();
            
            $direccion = new Direcciones();
            $direccion->calle = $request->input('calle');
            $direccion->num_exterior = $request->input('num_exterior');
            $direccion->id_colonia = $request->input('colonia');
            $direccion->save();

            $usuario = new User();
            $usuario->email=$request->email;
            $usuario->password=$request->password;
            $usuario->id_rol=5;
            $usuario->save();
            $facilitador = new Facilitadores();
            $facilitador->matricula =$request->input('matricula');
            $facilitador->nombre = $request->nombre;
            $facilitador->apellido_paterno = $request->apellidopaterno;
            $facilitador->apellido_materno = $request->apellidomaterno;
            $facilitador->fecha_nacimiento = $request->fecha_nacimiento;
            $facilitador->sexo = $request->sexo;
            $facilitador->rfc = $request->rfc;
            $facilitador->nivel_educativo = $request->niveleducativo;
            $facilitador->perfil_academico = $request->perfil_academico;
            $facilitador->telefono = $request->telefono;
            $facilitador->id_tiposangre = $request->id_tiposangre;
            $facilitador->telefono_emergencia = $request->telefono_emergencia;
            $facilitador->padecimiento = $request->padecimiento;
            
            if ($request->nacionalidad === '1' &&  $nacionaalidad !== '') {
                $n = Nacionalidades::where('nombre', $nacionaalidad)->first();

                if (!$n) {
                    $nueva_nacionalidad = new Nacionalidades();
                    $nueva_nacionalidad->nombre = $request->otra_nacionalidad;
                    $nueva_nacionalidad->save();
                    $facilitador->nacionalidad()->associate($nueva_nacionalidad);
                } else {
                    $facilitador->nacionalidad()->associate($n);
                    
                }
            }else{
                $facilitador->id_nacionalidad = $request->nacionalidad;
            }

            if($nacionalidad !== '2'){
                $facilitador->curp = '';
                $facilitador->estado_nacimiento = '33';
            }else {
                $facilitador->curp = $request->curp;
                $facilitador->estado_nacimiento = $request->estado_nacimiento;
            }

            $facilitador->usuario()->associate($usuario);
            $facilitador->direccion()->associate($direccion);
            $facilitador->save();

            DB::commit();
            return ApiResponses::success('Registro exitoso',201,$facilitador);

        }catch(ValidationException $e){
            DB::rollBack();
            return ApiResponses::error('Error: '.$e->getMessage(),500); 
        }catch (\Exception $ex) {
            DB::rollBack();
            return ApiResponses::error('Error: '.$ex->getMessage(),500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try{
            $facilitador = Facilitadores::with('usuario.rol', 'direccion.colonia.cp', 'direccion.colonia.municipio.estado','tiposangre', 'nacionalidad','estado')->findOrFail($id);
                return ApiResponses::success('Encontrado',200,$facilitador);   
        }catch(ModelNotFoundException $e){
            return ApiResponses::error('Error: '.$e->getMessage(),404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idFacilitador)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'nombre' => 'required',
                'apellidopaterno' => 'required',
                'apellidomaterno' => 'required',
                'fecha_nacimiento'=> 'required',
                'sexo'=> 'required',
                'rfc' => 'required',
                'niveleducativo' => 'required',
                'perfil_academico' => 'required',
                'telefono' => 'required',
                'id_tiposangre' => 'required',
                'telefono_emergencia' => 'required',
                'padecimiento' => 'required',
                'calle' => 'required',
                'num_exterior' => 'required',
                'colonia' => 'required',
                'nacionalidad' => 'required',
                // 'municipio' => 'required',
                // 'cp' => 'required',
                'email' => 'required',
            ]);
            $nacionalidad = (string) $request->nacionalidad;
            $nacionaalidad = $request->otra_nacionalidad;

            $facilitador=Facilitadores::findOrFail($idFacilitador);

            $direccion = $facilitador->direccion;
            $colonia = $direccion->colonia;
            $cp = $colonia->cp;

            $usuario = $facilitador -> usuario;
            $usuario->email=$request->email;
            $usuario->update();
            $facilitador->matricula =$request->input('matricula');
            $facilitador->nombre = $request->nombre;
            $facilitador->apellido_paterno = $request->apellidopaterno;
            $facilitador->apellido_materno = $request->apellidomaterno;
            $facilitador->fecha_nacimiento = $request->fecha_nacimiento;
            $facilitador->sexo = $request->sexo;
            $facilitador->rfc = $request->rfc;
            $facilitador->nivel_educativo = $request->niveleducativo;
            $facilitador->perfil_academico = $request->perfil_academico;
            $facilitador->telefono = $request->telefono;
            $facilitador->id_tiposangre = $request->id_tiposangre;
            $facilitador->telefono_emergencia = $request->telefono_emergencia;
            $facilitador->padecimiento = $request->padecimiento;
            
            if ($request->nacionalidad === '1' &&  $nacionaalidad !== '') {
                $n = Nacionalidades::where('nombre', $nacionaalidad)->first();

                if (!$n) {
                    $nueva_nacionalidad = new Nacionalidades();
                    $nueva_nacionalidad->nombre = $request->otra_nacionalidad;
                    $nueva_nacionalidad->save();
                    $facilitador->nacionalidad()->associate($nueva_nacionalidad);
                } else {
                    $facilitador->nacionalidad()->associate($n);
                    
                }
            }else{
                $facilitador->id_nacionalidad = $request->nacionalidad;
            }

            if($nacionalidad !== '2'){
                $facilitador->curp = '';
                $facilitador->estado_nacimiento = '33';
            }else {
                $facilitador->curp = $request->curp;
                $facilitador->estado_nacimiento = $request->estado_nacimiento;
            }

            $direccion->calle = $request->input('calle');
            $direccion->num_exterior = $request->input('num_exterior');
            $direccion->id_colonia = $request->input('colonia');

            // $cp->codigo = $request->input('cp');
            // $cp->update();

            // $colonia->nombre = $request->input('colonia');
            // $colonia->id_municipio = $request->input('municipio');

            // $colonia->update();
            $direccion->update();
            $facilitador->update();

            DB::commit();
            return ApiResponses::success('Actualizado',201);

        }catch(ModelNotFoundException $ex){
            DB::rollBack();
            return ApiResponses::error('Error: '.$ex->getMessage(),404); 
        }catch (Exception $e) {
            DB::rollBack();
            return ApiResponses::error('Error: '.$e->getMessage(),500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $facilitador = Facilitadores::with('usuario.rol','direccion.colonia.cp', 'direccion.colonia.municipio.estado', 'tiposangre', 'nacionalidad', 'estado')->findOrFail($id);
            if ($facilitador->cucs->isNotEmpty()) {
                return ApiResponses::error('No se puede eliminar el Facilitador porque asociado a algun CUC', 422);
            }
            $usuario = $facilitador -> usuario;
            $direccion = $facilitador->direccion;
            $colonia = $direccion->colonia;
            
            $facilitador->delete();
            if($usuario){
                $usuario ->delete();
            }

            if ($direccion) {
                $direccion->delete();
            }

            // if ($colonia) {
            //     $colonia->delete();
            // }
            
            DB::commit();
            return ApiResponses::success('Facilitador Eliminado', 201);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 404);
        }
    }

    public function asociarCuc(Request $request)
    {
        DB::beginTransaction();
        try {
            $usuarioConsejero = auth()->user();
            $claveCucConsejero = $usuarioConsejero->consejero->clave_cuc;
            $facilitador = Facilitadores::findOrFail($request->facilitadorId);
            $cuc = Cucs::findOrFail($claveCucConsejero);
            $facilitador->cucs()->syncWithoutDetaching([$claveCucConsejero]); //SyncWith..Para que no haya asociaciones repetidas
            
            DB::commit();
            return ApiResponses::success('Asociado', 201);
        } catch (ModelNotFoundException $ex) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $ex->getMessage(), 404);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 422);
        }
    }

    public function eliminarAsociacionCuc(Request $request)
    {
        DB::beginTransaction();
        try {
            $usuarioConsejero = auth()->user();
            $claveCucConsejero = $usuarioConsejero->consejero->clave_cuc;
            $facilitador = Facilitadores::findOrFail($request->facilitadorId);

            $clases = $facilitador->clases;

            if ($facilitador->clases->contains('clave_cuc', $claveCucConsejero)) {
                return ApiResponses::error('No se puede eliminar el facilitador porque tiene clases asociadas en este CUC', 422);
            }

            $facilitador->cucs()->detach($claveCucConsejero);
            DB::commit();
            return ApiResponses::success('Facilitador Eliminado', 200);
        } catch (ModelNotFoundException $ex) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $ex->getMessage(), 404);
        }catch (Exception $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 422);
        }
    }

    public function cucsDeFacilitadores(Request $request)
    {
        try {
            $usuarioFacilitador = auth()->user();
            $claveFacilitador = $usuarioFacilitador->facilitador->matricula;
            $facilitador = Facilitadores::findOrFail($claveFacilitador);

            $cucsAsociados = $facilitador->cucs()->with('direccion.colonia.cp', 'direccion.colonia.municipio.estado')->get();
            return ApiResponses::success('Lista de Cucs del Facilitador', 200, $cucsAsociados);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function clasesDeFacilitadores($claveCuc,$clavePeriodo)
    {
        try {
            $usuarioFacilitador = auth()->user();
            $claveFacilitador = $usuarioFacilitador->facilitador->matricula;

            $clases = Clases::where('id_periodo', $clavePeriodo)
            ->where('clave_cuc', $claveCuc)
            ->where('matricula', $claveFacilitador)
            ->get();

            return ApiResponses::success('Lista de Clases del Facilitador', 200, $clases);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

}
