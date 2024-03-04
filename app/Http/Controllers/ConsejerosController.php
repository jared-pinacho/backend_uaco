<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\Consejeros;
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

class ConsejerosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $consejero = Consejeros::with('usuario.rol', 'cuc','direccion.colonia.cp', 'direccion.colonia.municipio.estado', 'tiposangre', 'nacionalidad', 'estado')->get();
            return ApiResponses::success('Lista de Consejeros', 200, $consejero);
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
                'matricula' => 'required|unique:consejeros,matricula',
                'nombre' => 'required',
                'apellidopaterno' => 'required',
                'apellidomaterno' => 'required',
                'fecha_nacimiento'=> 'required',
                'sexo' => 'required',
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
                'clavecuc' => 'required|exists:cucs,clave_cuc',
                'email' => 'unique:users,email',
                'tipos_consejero' => 'array'
            ], [
                'matricula.unique' => 'La matricula ya está en uso.', // Personaliza el mensaje de error para 'clave'
                'email.unique' => 'El correo ya esta en uso',
                'clavecuc.exists' => 'La clave_cuc no existe'
            ]);
            $nacionalidad = (string) $request->nacionalidad;
            $nacionaalidad = $request->otra_nacionalidad;
            $tipoconsejeroData = $request->tipos_consejero;

            $direccion = new Direcciones();
            $direccion->calle = $request->input('calle');
            $direccion->num_exterior = $request->input('num_exterior');
            $direccion->id_colonia = $request->input('colonia');
            $direccion->save();

            $siacademico = false;
            $consejero = new Consejeros();
            foreach ($tipoconsejeroData as $data) {
                if($data === '1'){
                    $siacademico = true;
                }
            }

            if($siacademico){
                $usuario = new User();
                $usuario->email = $request->email;
                $usuario->password = $request->password;
                $usuario->id_rol = 3;
                $usuario->save();
                $consejero->usuario()->associate($usuario);
            }else {
                $consejero->id='3';
            }
            
            
            $consejero->matricula = $request->input('matricula');
            $consejero->nombre = $request->nombre;
            $consejero->apellido_paterno = $request->apellidopaterno;
            $consejero->apellido_materno = $request->apellidomaterno;
            $consejero->fecha_nacimiento = $request->fecha_nacimiento;
            $consejero->sexo = $request->sexo;
            $consejero->rfc = $request->rfc;
            $consejero->nivel_educativo = $request->niveleducativo;
            $consejero->perfil_academico = $request->perfil_academico;
            $consejero->telefono = $request->telefono;
            $consejero->id_tiposangre = $request->id_tiposangre;
            $consejero->telefono_emergencia = $request->telefono_emergencia;
            $consejero->padecimiento = $request->padecimiento;
            $consejero->clave_cuc = $request->clavecuc;

            if ($request->nacionalidad === '1' &&  $nacionaalidad !== '') {
                $n = Nacionalidades::where('nombre', $nacionaalidad)->first();

                if (!$n) {
                    $nueva_nacionalidad = new Nacionalidades();
                    $nueva_nacionalidad->nombre = $request->otra_nacionalidad;
                    $nueva_nacionalidad->save();
                    $consejero->nacionalidad()->associate($nueva_nacionalidad);
                } else {
                    $consejero->nacionalidad()->associate($n);
                    
                }
            }else{
                $consejero->id_nacionalidad = $request->nacionalidad;
            }

            if($nacionalidad !== '2'){
                $consejero->curp = '';
                $consejero->estado_nacimiento = '33';
            }else {
                $consejero->curp = $request->curp;
                $consejero->estado_nacimiento = $request->estado_nacimiento;
            }
            $consejero->direccion()->associate($direccion);
            $consejero->save();

            if (!empty($request->tipo_consejero)) {
                $consejero = Consejeros::findOrFail($request->matricula);
                foreach ($request->tipo_consejero as $idTipo) {
                    $area = AreasConsejeria::where('id_areaconsejero', $idTipo)->firstOrFail();
                    $consejero->areas()->syncWithoutDetaching([$area->id_areaconsejero]);
                }
            }

            DB::commit();
            return ApiResponses::success('Registro exitoso', 201, $consejero);
            //return $cuc;

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
        //
        try {
            $consejero = Consejeros::with('usuario.rol', 'cuc','direccion.colonia.cp', 'direccion.colonia.municipio.estado','tiposangre', 'nacionalidad', 'estado')->findOrFail($id);
            return ApiResponses::success('Encontrado', 200, $consejero);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idConsejero)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'nombre' => 'required',
                'apellidopaterno' => 'required',
                'apellidomaterno' => 'required',
                //'curp' => 'required',
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
                'nacionalidad'=> 'required',
                //'municipio' => 'required',
                //'cp' => 'required',
                'clavecuc' => 'required',
                //'email' => 'required',
            ]);
            $nacionalidad = (string) $request->nacionalidad;
            $nacionaalidad = $request->otra_nacionalidad;

            $consejero = Consejeros::findOrFail($idConsejero);

            $direccion = $consejero->direccion;
            // $colonia = $direccion->colonia;
            // $cp = $colonia->cp;

            // $usuario = $consejero->usuario;
            // $usuario->email = $request->email;
            // $usuario->update();
            $consejero->matricula = $request->input('matricula');
            $consejero->nombre = $request->nombre;
            $consejero->apellido_paterno = $request->apellidopaterno;
            $consejero->apellido_materno = $request->apellidomaterno;
            $consejero->fecha_nacimiento = $request->fecha_nacimiento;
            $consejero->sexo = $request->sexo;
            $consejero->rfc = $request->rfc;
            $consejero->nivel_educativo = $request->niveleducativo;
            $consejero->perfil_academico = $request->perfil_academico;
            $consejero->telefono = $request->telefono;
            $consejero->id_tiposangre = $request->id_tiposangre;
            $consejero->telefono_emergencia = $request->telefono_emergencia;
            $consejero->padecimiento = $request->padecimiento;
            $consejero->clave_cuc = $request->clavecuc;
            
            if ($request->nacionalidad === '1' &&  $nacionaalidad !== '') {
                $n = Nacionalidades::where('nombre', $nacionaalidad)->first();

                if (!$n) {
                    $nueva_nacionalidad = new Nacionalidades();
                    $nueva_nacionalidad->nombre = $request->otra_nacionalidad;
                    $nueva_nacionalidad->save();
                    $consejero->nacionalidad()->associate($nueva_nacionalidad);
                } else {
                    $consejero->nacionalidad()->associate($n);
                    
                }
            }else{
                $consejero->id_nacionalidad = $request->nacionalidad;
            }

            if($nacionalidad !== '2'){
                $consejero->curp = '';
                $consejero->estado_nacimiento = '33';
            }else {
                $consejero->curp = $request->curp;
                $consejero->estado_nacimiento = $request->estado_nacimiento;
            }

            $direccion->calle = $request->input('calle');
            $direccion->num_exterior = $request->input('num_exterior');
            $direccion->id_colonia = $request->input('colonia');

            // $cp->codigo = $request->input('cp');
            // $cp->update();

            //$colonia->nombre = $request->input('colonia');
            //$colonia->id_municipio = $request->input('municipio');

            //$colonia->update();
            $direccion->update();

            $consejero->update();

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
            $consejero = Consejeros::with('direccion.colonia')->findOrFail($id);
            $usuario = $consejero->usuario;
            $direccion = $consejero->direccion;
            //$colonia = $direccion->colonia;

            $consejero->delete();
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

    public function obtenerCucDeConsejero(Request $request)
    {
        try {
            // Verificar si el usuario autenticado es un consejero
            $usuarioConsejero = auth()->user();
            // Obtener la clave_cuc del consejero
            $claveCucConsejero = $usuarioConsejero->consejero->clave_cuc;
            $cuc = Cucs::with('direccion.colonia.cp', 'direccion.colonia.municipio.estado')->findOrFail($claveCucConsejero);
            // Obtener el CUC correspondiente a la clave_cuc del consejero
            if ($cuc) {
                return ApiResponses::success('Cuc de consejero', 200, $cuc);
            }
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado c' . $ex, 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }
}
