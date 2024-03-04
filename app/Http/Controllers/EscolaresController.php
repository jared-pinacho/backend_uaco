<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\Escolares;
use App\Models\Cucs;
use App\Models\User;
use App\Models\CodigoPostal;
use App\Models\Colonia;
use App\Models\Direcciones;
use App\Models\Estados;
use App\Models\Municipios;
use App\Models\Nacionalidades;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class EscolaresController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $escolar = Escolares::with('usuario.rol', 'cuc','direccion.colonia.cp', 'direccion.colonia.municipio.estado', 'tiposangre', 'nacionalidad', 'estado')->get();
            return ApiResponses::success('Lista de Escolares', 200, $escolar);
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
                'matricula' => 'required|unique:escolares,matricula',
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
                'clavecuc' => 'required|exists:cucs,clave_cuc',
                'email' => 'required|unique:users,email',
                'password' => 'required',

            ], [
                'matricula.unique' => 'La matricula ya está en uso.',
                'email.unique' => 'El correo ya esta en uso',
                'clavecuc.exists' => 'La clave_cuc no existe'
            ]);
            $nacionaalidad = $request->otra_nacionalidad;
            $nacionalidad = (string) $request->nacionalidad;            
            $direccion = new Direcciones();
            $direccion->calle = $request->input('calle');
            $direccion->num_exterior = $request->input('num_exterior');
            $direccion->id_colonia = $request->input('colonia');
            $direccion->save();

            $usuario = new User();
            $usuario->email = $request->email;
            $usuario->password = $request->password;
            $usuario->id_rol = 4;
            $usuario->save();
            $escolar = new Escolares();
            $escolar->matricula = $request->input('matricula');
            $escolar->nombre = $request->nombre;
            $escolar->apellido_paterno = $request->apellidopaterno;
            $escolar->apellido_materno = $request->apellidomaterno;
            $escolar->fecha_nacimiento = $request->fecha_nacimiento;
            $escolar->sexo = $request->sexo;
            $escolar->rfc = $request->rfc;
            $escolar->nivel_educativo = $request->niveleducativo;
            $escolar->perfil_academico = $request->perfil_academico;
            $escolar->telefono = $request->telefono;
            $escolar->id_tiposangre = $request->id_tiposangre;
            $escolar->telefono_emergencia = $request->telefono_emergencia;
            $escolar->padecimiento = $request->padecimiento;
            $escolar->clave_cuc = $request->clavecuc;
            
            if ($request->nacionalidad === '1' &&  $nacionaalidad !== '') {
                $n = Nacionalidades::where('nombre', $nacionaalidad)->first();

                if (!$n) {
                    $nueva_nacionalidad = new Nacionalidades();
                    $nueva_nacionalidad->nombre = $request->otra_nacionalidad;
                    $nueva_nacionalidad->save();
                    $escolar->nacionalidad()->associate($nueva_nacionalidad);
                } else {
                    $escolar->nacionalidad()->associate($n);
                    
                }
            }else{
                $escolar->id_nacionalidad = $request->nacionalidad;
            }

            if($nacionalidad !== '2'){
                $escolar->curp = '';
                $escolar->estado_nacimiento = '33';
            }else {
                $escolar->curp = $request->curp;
                $escolar->estado_nacimiento = $request->estado_nacimiento;
            }

            $escolar->usuario()->associate($usuario);
            $escolar->direccion()->associate($direccion);
            $escolar->save();

            DB::commit();
            return ApiResponses::success('Registro exitoso', 201, $escolar);
        } catch (ValidationException $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $ex->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $escolar = Escolares::with('usuario.rol', 'cuc','direccion.colonia.cp', 'direccion.colonia.municipio.estado','tiposangre', 'nacionalidad', 'estado')->findOrFail($id);
            return ApiResponses::success('Encontrado', 200, $escolar);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 404);
        }
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idEscolar)
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
                'clavecuc' => 'required',
                'email' => 'required',
            ]);
            $nacionalidad = (string) $request->nacionalidad;
            $nacionaalidad = $request->otra_nacionalidad;

            $escolar = Escolares::findOrFail($idEscolar);

            $direccion = $escolar->direccion;
            // $colonia = $direccion->colonia;
            // $cp = $colonia->cp;

            $usuario = $escolar->usuario;
            $usuario->email = $request->email;
            $usuario->update();
            $escolar->matricula = $request->input('matricula');
            $escolar->nombre = $request->nombre;
            $escolar->apellido_paterno = $request->apellidopaterno;
            $escolar->apellido_materno = $request->apellidomaterno;
            $escolar->fecha_nacimiento = $request->fecha_nacimiento;
            $escolar->sexo = $request->sexo;
            $escolar->rfc = $request->rfc;
            $escolar->nivel_educativo = $request->niveleducativo;
            $escolar->perfil_academico = $request->perfil_academico;
            $escolar->telefono = $request->telefono;
            $escolar->id_tiposangre = $request->id_tiposangre;
            $escolar->telefono_emergencia = $request->telefono_emergencia;
            $escolar->padecimiento = $request->padecimiento;
            $escolar->clave_cuc = $request->clavecuc;
            
            if ($request->nacionalidad === '1' &&  $nacionaalidad !== '') {
                $n = Nacionalidades::where('nombre', $nacionaalidad)->first();

                if (!$n) {
                    $nueva_nacionalidad = new Nacionalidades();
                    $nueva_nacionalidad->nombre = $request->otra_nacionalidad;
                    $nueva_nacionalidad->save();
                    $escolar->nacionalidad()->associate($nueva_nacionalidad);
                } else {
                    $escolar->nacionalidad()->associate($n);
                    
                }
            }else{
                $escolar->id_nacionalidad = $request->nacionalidad;
            }

            if($nacionalidad !== '2'){
                $escolar->curp = '';
                $escolar->estado_nacimiento = '33';
            }else {
                $escolar->curp = $request->curp;
                $escolar->estado_nacimiento = $request->estado_nacimiento;
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

            $escolar->update();

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
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $escolar = Escolares::with('direccion.colonia')->findOrFail($id);
            $usuario = $escolar->usuario;
            $direccion = $escolar->direccion;
            $colonia = $direccion->colonia;

            $escolar->delete();
            if ($usuario) {
                $usuario->delete();
            }

            if ($direccion) {
                $direccion->delete();
            }

            // if ($colonia) {
            //     $colonia->delete();
            // }

            DB::commit();
            return ApiResponses::success('Eliminado', 201);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 404);
        }
    }

    public function obtenerCucDeEscolar(Request $request)
    {
        try {
            $usuarioEscolar = auth()->user();

            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;
            $cuc = Cucs::with('direccion.colonia.cp', 'direccion.colonia.municipio.estado')->findOrFail($claveCucEscolar);
            // Obtener el CUC correspondiente a la clave_cuc del escolar

            if ($cuc) {
                return ApiResponses::success('Cuc del escolar', 200, $cuc);
            }
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado' . $ex, 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }
}
