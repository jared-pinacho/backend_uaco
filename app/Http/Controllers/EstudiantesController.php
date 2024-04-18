<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\Estudiantes;
use App\Models\User;
use App\Models\Cucs;
use App\Models\cuc_carrera;
use App\Models\CodigoPostal;
use App\Models\Colonia;
use App\Models\Direcciones;
use App\Models\Estados;
use App\Models\Municipios;
use App\Models\LenguasIndigenas;
use App\Models\PueblosIndigenas;
use App\Models\Nacionalidades;
use App\Models\estadoDocumentacion;
use App\Models\Grupos;
use App\Models\Carreras;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class EstudiantesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $estudiantes = Estudiantes::with('usuario.rol', 'grupo.carrera', 'direccion.colonia.cp', 'direccion.colonia.municipio.estado', 'tiposangre', 'lenguaindigena', 'puebloindigena', 'nacionalidad', 'estado')->get();

            // Transforma la respuesta para personalizar el formato de 'documentacion'
            $estudiantesTransformados = $estudiantes->map(function ($estudiante) {
                $documentos = [
                    [
                        'nombre' => 'Certificado de terminacion de estudios',
                        'estado' => boolval($estudiante->documento->certificado_terminacion_estudios),
                    ],
                    [
                        'nombre' => 'Acta de examen',
                        'estado' => boolval($estudiante->documento->acta_examen),
                    ],
                    [
                        'nombre' => 'Titulo electronico',
                        'estado' => boolval($estudiante->documento->titulo_electronico),
                    ],
                    [
                        'nombre' => 'Liberacion de servicio social',
                        'estado' => boolval($estudiante->documento->liberacion_servicio_social),
                    ],
                ];

                // Agrega el campo 'documentacion' con el formato personalizado al objeto estudiante
                $estudiante->documentacion = $documentos;

                // Elimina el campo 'documento' original
                unset($estudiante->documento);

                return $estudiante;
            });

            return ApiResponses::success('Lista de Estudiantes', 200, $estudiantesTransformados);
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

        try{

            $request->validate([
                'nombre'=> 'required',
                'apellidopaterno'=> 'required',
                'apellidomaterno'=> 'required',
                'edad'=> 'required',
                'sexo'=> 'required',
                'fecha_nacimiento'=> 'required',
                'niveleducativo'=> 'required',
                'telefono'=> 'required',
                'telefono_emergencia'=> 'required',
                'num_exterior' => 'required',
                'calle' => 'required',
                'colonia' => 'required',
                'nacionalidad'=> 'required',
                'id_tiposangre'=> 'required',
                'padecimiento'=> 'required',
                'discapacidad'=> 'required',
                'semestre'=> 'required',
                'lengua_indigena'=> 'required',
                'pueblo_indigena'=> 'required',
                'clave_grupo'=> 'required|exists:grupos,clave_grupo',
                'email' => 'required|unique:users,email',
                'password' => 'required',
                'clave_carrera' => 'required'
            ], [
                'email.unique' => 'El correo ya esta en uso',
                'clave_grupo.exists' => 'La clave_grupo no existe'
            ]);
            $lengua = $request->otra_lengua;
            $pueblo = $request->otro_pueblo;
            $nacionaalidad = $request->otra_nacionalidad;
            $nacionalidad = (string) $request->nacionalidad;
            
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;

            $year = date('y');
            // Obtén los 3 últimos dígitos de la clave de carrera
            $carreraSuffix = substr($request->clave_carrera, -3);

            //obteniendo cuc
            $cuc = CUCS::findOrfail($claveCucEscolar);
            //obteniendo numero de cuc
            $numeroCuc = $cuc->numero;
            // Obtén los 2 últimos dígitos del año actual
            

            $estudiantes = Estudiantes::withTrashed()
            ->whereHas('grupo', function ($query) use ($request) {
                $query->whereHas('carrera', function ($subquery) use ($request) {
                    $subquery->where('clave_carrera', $request->clave_carrera);
                });
            })
            ->whereRaw("SUBSTRING(matricula, 1, 2) = ?", [$numeroCuc]);

            // Autoincremento
            $ultimoEstudiante = $estudiantes->whereYear('created_at', date('Y'))->latest()->first();
            if ($ultimoEstudiante) {
                $autoincremento = str_pad((int)substr($ultimoEstudiante->matricula, -4) + 1, 4, '0', STR_PAD_LEFT);
            } else {
                // Si es el primer estudiante del año para esa carrera, comienza desde 1
                $autoincremento = '0001';
            }

            // Genera la matrícula del estudiante
            $matricula = $numeroCuc . $year . $carreraSuffix . $autoincremento;
            
            $direccion = new Direcciones();
            $direccion->calle = $request->input('calle');
            $direccion->num_exterior = $request->input('num_exterior');
            $direccion->id_colonia = $request->input('colonia');
            $direccion->save();

            $usuario = new User();
            $usuario->email = $request->email;
            $usuario->password = $request->password;
            $usuario->id_rol = 6;
            $usuario->save();

            $estudiante = new Estudiantes();
            $estudiante->matricula = $matricula; //manda la matricula creada
            $estudiante->nombre = $request->nombre;
            $estudiante->apellido_paterno = $request->apellidopaterno;
            $estudiante->apellido_materno = $request->apellidomaterno;
            $estudiante->edad = $request->edad;
            $estudiante->sexo = $request->sexo;
            $estudiante->fecha_nacimiento = $request->fecha_nacimiento;
            $estudiante->nivel_educativo = $request->niveleducativo;
            $estudiante->telefono = $request->telefono;
            $estudiante->telefono_emergencia = $request->telefono_emergencia;
            $estudiante->id_tiposangre = $request->id_tiposangre;
            $estudiante->padecimiento = $request->padecimiento;
            $estudiante->discapacidad = $request->discapacidad;
            $estudiante->regular = "Si";
            $estudiante->semestre = $request->semestre;
            $estudiante->estatus = "Activo";
            $estudiante->creditos_acumulados = 0;
            $estudiante -> clave_grupo = $request->clave_grupo;

            if ($request->nacionalidad === '1' &&  $nacionaalidad !== '') {
                $n = Nacionalidades::where('nombre', $nacionaalidad)->first();

                if (!$n) {
                    $nueva_nacionalidad = new Nacionalidades();
                    $nueva_nacionalidad->nombre = $request->otra_nacionalidad;
                    $nueva_nacionalidad->save();
                    $estudiante->nacionalidad()->associate($nueva_nacionalidad);
                } else {
                    $estudiante->nacionalidad()->associate($n);
                    
                }
            }else{
                $estudiante->id_nacionalidad = $request->nacionalidad;
            }

            if($nacionalidad !== '2'){
                $estudiante->curp = '';
                $estudiante->estado_nacimiento= '33';
            }else {
                $estudiante->curp = $request->curp;
                $estudiante->estado_nacimiento= $request->estado_nacimiento;
            }

            if ($request->lengua_indigena === '2' &&  $lengua !== '') {
                $l = LenguasIndigenas::where('nombre', $lengua)->first();

                if (!$l) {
                    $nueva_lengua = new LenguasIndigenas();
                    $nueva_lengua->nombre = $request->otra_lengua;
                    $nueva_lengua->save();
                    $estudiante->lenguaindigena()->associate($nueva_lengua);
                } else {
                    $estudiante->lenguaindigena()->associate($l);
                    
                }
            }else{
                $estudiante->id_lenguaindigena = $request->lengua_indigena;
            }

            if ($request->pueblo_indigena === '2' &&  $pueblo !== '') {
                $p = PueblosIndigenas::where('nombre', $pueblo)->first();
                
                if (!$p) {
                    $nuevo_pueblo = new PueblosIndigenas();
                    $nuevo_pueblo->nombre =$request->otro_pueblo;
                    $nuevo_pueblo->save();
                    $estudiante->puebloindigena()->associate($nuevo_pueblo);
                } else {
                    $estudiante->puebloindigena()->associate($p);
                }
            }else{
                $estudiante->id_puebloindigena = $request->pueblo_indigena;
            }

            $estudiante->usuario()->associate($usuario);
            $estudiante->direccion()->associate($direccion);
            $estudiante->save();

            $estudianteRecienGuardado = Estudiantes::where('matricula', $matricula)->first();

            $documentos = new estadoDocumentacion();
            $documentos->certificado_terminacion_estudios = false;
            $documentos->acta_examen = false;
            $documentos->titulo_electronico = false;
            $documentos->liberacion_servicio_social = false;

            // Asocia el estudiante utilizando su ID
            $documentos->estudiante()->associate($estudianteRecienGuardado);
            $documentos->save();

            DB::commit();
            return ApiResponses::success('Registro exitoso', 201, $estudiante);

        } catch (ValidationException $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        } catch (Exception $ex) {
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
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;
            $estudiante = Estudiantes::with('usuario.rol','grupo' ,'direccion.colonia.cp',
                'direccion.colonia.municipio.estado', 'tiposangre','lenguaindigena','puebloindigena', 'nacionalidad', 'estado', 'documento')->findOrFail($id);
            $cuc = Cucs::where('clave_cuc', $claveCucEscolar)->first();
            $grupoEstudiante = $estudiante->grupo;

            // Transforma la respuesta para personalizar el formato de 'documentacion'
            $documentos = [
                [
                    'nombre' => 'Certificado de terminacion de estudios',
                    'estado' => boolval($estudiante->documento->certificado_terminacion_estudios),
                ],
                [
                    'nombre' => 'Acta de examen',
                    'estado' => boolval($estudiante->documento->acta_examen),
                ],
                [
                    'nombre' => 'Titulo electronico',
                    'estado' => boolval($estudiante->documento->titulo_electronico),
                ],
                [
                    'nombre' => 'Liberacion de servicio social',
                    'estado' => boolval($estudiante->documento->liberacion_servicio_social),
                ],
            ];

            // Agrega el campo 'documentacion' con el formato personalizado al objeto estudiante
            $estudiante->documentacion = $documentos;

            // Elimina el campo 'documento' original
            unset($estudiante->documento);

            if ($grupoEstudiante) {
                $ultiomosTresDigitosCuc = substr($cuc->clave_cuc, 7, 9);
                if (strpos($grupoEstudiante->clave_grupo, $ultiomosTresDigitosCuc) === 0) {
                    return ApiResponses::success('Estudiante encontrado', 200, $estudiante);
                }
            }
            return ApiResponses::error('El estudiante no pertenece a ese cuc', 404);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 404);
        }
    }  /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idEstudiante)
    {
        DB::beginTransaction();
        try{
            $request->validate([
                'nombre'=> 'required',
                'apellidopaterno'=> 'required',
                'apellidomaterno'=> 'required',
                'edad'=> 'required',
                'sexo'=> 'required',
                'fecha_nacimiento'=> 'required',
                'niveleducativo'=> 'required',
                'telefono'=> 'required',
                'telefono_emergencia'=> 'required',
                'num_exterior' => 'required',
                'calle' => 'required',
                'colonia' => 'required',
                'nacionalidad'=> 'required',
                'id_tiposangre'=> 'required',
                'padecimiento'=> 'required',
                'discapacidad'=> 'required',
                'regular'=> 'required',
                'semestre'=> 'required',
                'estatus'=> 'required',
                'lengua_indigena'=> 'required',
                'pueblo_indigena'=> 'required',
                'clave_grupo'=> 'required|exists:grupos,clave_grupo',
                'email' => 'required',
                'otro_pueblo',
                'otra_lengua',
                //'documentacion' => 'array'
            ]);
            $lengua = $request->otra_lengua;
            $pueblo = $request->otro_pueblo;
            $nacionaalidad = $request->otra_nacionalidad;
            $nacionalidad = (string) $request->nacionalidad;

            $estudiante = Estudiantes::findOrFail($idEstudiante);

            $direccion = $estudiante->direccion;
            $usuario = $estudiante->usuario;
            $usuario->email = $request->email;
            $usuario->update();

            $estudiante->matricula = $request->input('matricula');
            $estudiante->nombre = $request->nombre;
            $estudiante->apellido_paterno = $request->apellidopaterno;
            $estudiante->apellido_materno = $request->apellidomaterno;
            $estudiante->edad = $request->edad;
            $estudiante->sexo = $request->sexo;
            $estudiante->fecha_nacimiento = $request->fecha_nacimiento;
            $estudiante->nivel_educativo = $request->niveleducativo;
            $estudiante->telefono = $request->telefono;
            $estudiante->telefono_emergencia = $request->telefono_emergencia;
            $estudiante->id_tiposangre = $request->id_tiposangre;
            $estudiante->padecimiento = $request->padecimiento;
            $estudiante->discapacidad = $request->discapacidad;
            $estudiante->regular = $request->regular;
            $estudiante->semestre = $request->semestre;
            $estudiante->estatus = $request->estatus;

            if ($request->nacionalidad === '1' &&  $nacionaalidad !== '') {
                $n = Nacionalidades::where('nombre', $nacionaalidad)->first();

                if (!$n) {
                    $nueva_nacionalidad = new Nacionalidades();
                    $nueva_nacionalidad->nombre = $request->otra_nacionalidad;
                    $nueva_nacionalidad->save();
                    $estudiante->nacionalidad()->associate($nueva_nacionalidad);
                } else {
                    $estudiante->nacionalidad()->associate($n);
                    
                }
            }else{
                $estudiante->id_nacionalidad = $request->nacionalidad;
            }

            if($nacionalidad !== '2'){
                $estudiante->curp = '';
                $estudiante->estado_nacimiento= '33';
            }else {
                $estudiante->curp = $request->curp;
                $estudiante->estado_nacimiento= $request->estado_nacimiento;
            }

            if ($request->lengua_indigena === '2' &&  $lengua !== '') {
                $l = LenguasIndigenas::where('nombre', $lengua)->first();

                if (!$l) {
                    $nueva_lengua = new LenguasIndigenas();
                    $nueva_lengua->nombre = $request->otra_lengua;
                    $nueva_lengua->save();
                    $estudiante->lenguaindigena()->associate($nueva_lengua);
                } else {
                    $estudiante->lenguaindigena()->associate($l);
                    
                }
            }else{
                $estudiante->id_lenguaindigena = $request->lengua_indigena;
            }

            if ($request->pueblo_indigena === '2' &&  $pueblo !== '') {
                $p = PueblosIndigenas::where('nombre', $pueblo)->first();
                
                if (!$p) {
                    $nuevo_pueblo = new PueblosIndigenas();
                    $nuevo_pueblo->nombre =$request->otro_pueblo;
                    $nuevo_pueblo->save();
                    $estudiante->puebloindigena()->associate($nuevo_pueblo);
                } else {
                    $estudiante->puebloindigena()->associate($p);
                }
            }else{
                $estudiante->id_puebloindigena = $request->pueblo_indigena;
            }
            
            $estudiante -> clave_grupo = $request->clave_grupo;
            $direccion->calle = $request->input('calle');
            $direccion->num_exterior = $request->input('num_exterior');
            $direccion->id_colonia = $request->input('colonia');
            $direccion->update();
            $estudiante->update();            

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
            $estudiante = Estudiantes::with('direccion.colonia')->findOrFail($id);
            $usuario = $estudiante->usuario;
            $direccion = $estudiante->direccion;
            $colonia = $direccion->colonia;

            $estudiante->delete();
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

    public function numEstudiantesPorCuc()
    {
        try {
            $usuarios = auth()->user();
            $rol = $usuarios->rol->nombre;

            if ($rol === 'consejero') {
                // Obtener la clave_cuc del consejero
                $claveCuc = $usuarios->consejero->clave_cuc;
            }else if($rol === 'escolar'){
                $claveCuc = $usuarios->escolar->clave_cuc;
            }
            $cuc = Cucs::findOrfail($claveCuc);
            $numeroCuc = $cuc->numero;

            $estudiantes = Estudiantes::whereRaw("SUBSTRING(matricula, 1, 2) = ?", [$numeroCuc])->get();

            $totalEstudiantes = $estudiantes->count();
            
            $totalMujeres = $estudiantes->where('sexo', 'M')->count();
            $totalHombres = $estudiantes->where('sexo', 'H')->count();
            $totalEstudiantesLengua = $estudiantes->where('id_lenguaindigena', '!=', 1)->count();
            $totalEstudiantesLenguaNinguno = $estudiantes->where('id_lenguaindigena', '=', 1)->count();
            $totalEstudiantesPueblo = $estudiantes->where('id_puebloindigena', '!=', 1)->count();
            $totalEstudiantesPuebloNinguno = $estudiantes->where('id_puebloindigena', '=', 1)->count();
            $totalEstudiantesMexicanos = $estudiantes->where('id_nacionalidad', '=', 2)->count();
            $totalEstudiantesNoMexicanos = $estudiantes->where('id_nacionalidad', '!=', 2)->count();

            $totalEstudiantesDiscapacidad = $estudiantes->filter(function ($estudiantes) {
                return strcasecmp($estudiantes->discapacidad, 'ninguno') !== 0;})->count();
            $totalEstudiantesDiscapacidadNinguna = $estudiantes->filter(function ($estudiantes) {
                return strcasecmp($estudiantes->discapacidad, 'ninguno') == 0;})->count();
            
                $resultados = [];
                // Obtener todas las carreras asociadas al CUC
                $carreras = Carreras::whereHas('grupos', function ($query) use ($claveCuc) {
                    $query->where('clave_grupo', 'like', substr($claveCuc, 7, 9) . '%');
                })->get();
    
                foreach ($carreras as $carrera) {
                    // Obtener todos los grupos asociados a la carrera y al CUC
                    $grupos = Grupos::where('clave_carrera', $carrera->clave_carrera)
                        ->where('clave_grupo', 'like', substr($claveCuc, 7, 9) . '%')
                        ->get();
                    $totalEstudiantess = 0;
                    foreach ($grupos as $grupo) {
                        // Obtener todos los estudiantes asociados al grupo
                        $estudiantess = Estudiantes::where('clave_grupo', $grupo->clave_grupo)->get();
                        // Sumar la cantidad de estudiantes en el grupo actual
                        $totalEstudiantess += $estudiantess->count();
                    }
                    // Almacenar el resultado en el arreglo incluso si no hay estudiantes
                    $resultados[$carrera->nombre] = $totalEstudiantess;
                }

            return ApiResponses::success('Numero de Estudiantes', 200, [
                'total_estudiantes' => $totalEstudiantes,
                'total_mujeres' => $totalMujeres,
                'total_hombres' => $totalHombres,
                'total_estudiantes_lengua' => $totalEstudiantesLengua,
                'total_estudiantes_lengua_ninguno' => $totalEstudiantesLenguaNinguno,
                'total_estudiantes_pueblo' => $totalEstudiantesPueblo,
                'total_estudiantes_pueblo_ninguno' => $totalEstudiantesPuebloNinguno,
                'total_estudiantes_mexicanos' => $totalEstudiantesMexicanos,
                'total_estudiantes_no_mexicanos' => $totalEstudiantesNoMexicanos,
                'total_estudiantes_discapacidad' => $totalEstudiantesDiscapacidad,
                'total_estudiantes_discapacidad_ninguna' => $totalEstudiantesDiscapacidadNinguna,
                'estudiantes_carrera' => $resultados,
            ]);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Error: ' . $ex->getMessage(), 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function numEstudiantesPorCucCoordinador($claveCuc)
    {
        try {
            $cuc = Cucs::findOrfail($claveCuc);
            $numeroCuc = $cuc->numero;

            $estudiantes = Estudiantes::whereRaw("SUBSTRING(matricula, 1, 2) = ?", [$numeroCuc])->get();

            $totalEstudiantes = $estudiantes->count();
            
            $totalMujeres = $estudiantes->where('sexo', 'M')->count();
            $totalHombres = $estudiantes->where('sexo', 'H')->count();
            $totalEstudiantesLengua = $estudiantes->where('id_lenguaindigena', '!=', 1)->count();
            $totalEstudiantesLenguaNinguno = $estudiantes->where('id_lenguaindigena', '=', 1)->count();
            $totalEstudiantesPueblo = $estudiantes->where('id_puebloindigena', '!=', 1)->count();
            $totalEstudiantesPuebloNinguno = $estudiantes->where('id_puebloindigena', '=', 1)->count();
            $totalEstudiantesMexicanos = $estudiantes->where('id_nacionalidad', '=', 2)->count();
            $totalEstudiantesNoMexicanos = $estudiantes->where('id_nacionalidad', '!=', 2)->count();

            $totalEstudiantesDiscapacidad = $estudiantes->filter(function ($estudiantes) {
                return strcasecmp($estudiantes->discapacidad, 'ninguno') !== 0;})->count();
            $totalEstudiantesDiscapacidadNinguna = $estudiantes->filter(function ($estudiantes) {
                return strcasecmp($estudiantes->discapacidad, 'ninguno') == 0;})->count();

            return ApiResponses::success('Numero de Estudiantes', 200, [
                'total_estudiantes' => $totalEstudiantes,
                'total_mujeres' => $totalMujeres,
                'total_hombres' => $totalHombres,
                'total_estudiantes_lengua' => $totalEstudiantesLengua,
                'total_estudiantes_lengua_ninguno' => $totalEstudiantesLenguaNinguno,
                'total_estudiantes_pueblo' => $totalEstudiantesPueblo,
                'total_estudiantes_pueblo_ninguno' => $totalEstudiantesPuebloNinguno,
                'total_estudiantes_mexicanos' => $totalEstudiantesMexicanos,
                'total_estudiantes_no_mexicanos' => $totalEstudiantesNoMexicanos,
                'total_estudiantes_discapacidad' => $totalEstudiantesDiscapacidad,
                'total_estudiantes_discapacidad_ninguna' => $totalEstudiantesDiscapacidadNinguna,
            ]);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Error: ' . $ex->getMessage(), 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }
    public function totEstudiantes()
    {
        try {
            $estudiante = Estudiantes::all();;
            $totalEstudiantess = $estudiante->count();
            $totalMujeres = $estudiante->where('sexo', 'M')->count();
            $totalHombres = $estudiante->where('sexo', 'H')->count();
            $totalEstudiantesLengua = $estudiante->where('id_lenguaindigena', '!=', 1)->count();
            $totalEstudiantesLenguaNinguno = $estudiante->where('id_lenguaindigena', '=', 1)->count();
            $totalEstudiantesPueblo = $estudiante->where('id_puebloindigena', '!=', 1)->count();
            $totalEstudiantesPuebloNinguno = $estudiante->where('id_puebloindigena', '=', 1)->count();
            $totalEstudiantesMexicanos = $estudiante->where('id_nacionalidad', '=', 2)->count();
            $totalEstudiantesNoMexicanos = $estudiante->where('id_nacionalidad', '!=', 2)->count();

            $totalEstudiantesDiscapacidad = $estudiante->filter(function ($estudiante) {
                return strcasecmp($estudiante->discapacidad, 'ninguno') !== 0;})->count();  
            $totalEstudiantesDiscapacidadNinguna = $estudiante->filter(function ($estudiante) {
                return strcasecmp($estudiante->discapacidad, 'ninguno') == 0;})->count();

                $resultados = [];

                // Obtener todas las carreras
                $carreras = Carreras::all();
    
                foreach ($carreras as $carrera) {
                    // Obtener todos los grupos asociados a la carrera
                    $grupos = Grupos::where('clave_carrera', $carrera->clave_carrera)->get();
    
                    $totalEstudiantes = 0;
    
                    foreach ($grupos as $grupo) {
                        // Obtener todos los estudiantes asociados al grupo
                        $estudiantes = Estudiantes::where('clave_grupo', $grupo->clave_grupo)->get();
    
                        // Sumar la cantidad de estudiantes en el grupo actual
                        $totalEstudiantes += $estudiantes->count();
                    }
    
                    // Almacenar el resultado en el arreglo incluso si no hay estudiantes
                    $resultados[$carrera->nombre] = $totalEstudiantes;
                }


            return ApiResponses::success('Numero de Estudiantes', 200, [
                'total_estudiantes' => $totalEstudiantess,
                'total_mujeres' => $totalMujeres,
                'total_hombres' => $totalHombres,
                'total_estudiantes_lengua' => $totalEstudiantesLengua,
                'total_estudiantes_lengua_ninguno' => $totalEstudiantesLenguaNinguno,
                'total_estudiantes_pueblo' => $totalEstudiantesPueblo,
                'total_estudiantes_pueblo_ninguno' => $totalEstudiantesPuebloNinguno,
                'total_estudiantes_mexicanos' => $totalEstudiantesMexicanos,
                'total_estudiantes_no_mexicanos' => $totalEstudiantesNoMexicanos,
                'total_estudiantes_discapacidad' => $totalEstudiantesDiscapacidad,
                'total_estudiantes_discapacidad_ninguna' => $totalEstudiantesDiscapacidadNinguna,
                'estudiantes_carrer' => $resultados,
            ]);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function totalEstudiantesPorCarreraPorCuc($claveCarrera)
    {
        try {
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;
            $cuc = Cucs::findOrFail($claveCucEscolar);
            $claveCuc = $cuc->clave_cuc;

            $carrera = Carreras::where('clave_carrera', $claveCarrera)->firstOrFail();
            $grupos = $carrera->grupos;

            $totalEstudiantes = 0;

            foreach ($grupos as $grupo) {
                $claveGrupo = $grupo->clave_grupo;
                // Verificar si los dos primeros caracteres coinciden con $claveCuc
                if ((int)substr($claveGrupo, 0, 2) === (int)substr($claveCucEscolar, 7, 9)) {
                    $totalEstudiantes += Estudiantes::where('clave_grupo', $claveGrupo)->count();
                }
            }

            return ApiResponses::success('Total de estudiantes', 200, ['total_estudiantes_programa' => $totalEstudiantes]);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function totalEstudiantesPorCarreraPorCucCoordinador($claveCuc, $claveCarrera)
    {
        try {
            $cuc = Cucs::findOrFail($claveCuc);
            $carrera = Carreras::where('clave_carrera', $claveCarrera)->firstOrFail();
            $grupos = $carrera->grupos;
            $totalEstudiantes = 0;

            foreach ($grupos as $grupo) {
                $claveGrupo = $grupo->clave_grupo;
                // Verificar si los dos primeros caracteres coinciden con $claveCuc
                if ((int)substr($claveGrupo, 0, 2) === (int)substr($claveCuc, 7, 9)) {
                    $totalEstudiantes += Estudiantes::where('clave_grupo', $claveGrupo)->count();
                }
            }
            return ApiResponses::success('Total de estudiantes', 200, ['total_estudiantes_programa' => $totalEstudiantes]);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function totalEstudiantesPorCarreraCucs($claveCarrera)
    {
        try {
            $carrera = Carreras::where('clave_carrera', $claveCarrera)->firstOrFail();
            $grupos = $carrera->grupos;

            $totalEstudiantes = 0;

            foreach ($grupos as $grupo) {
                $claveGrupo = $grupo->clave_grupo;
                $totalEstudiantes += Estudiantes::where('clave_grupo', $claveGrupo)->count();
            }

            return ApiResponses::success('Total de estudiantes', 200, ['total_estudiantes_programa' => $totalEstudiantes]);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function totalEstudiantesCarreras()
    {
        try {
            $resultados = [];
            // Obtener todas las carreras
            $carreras = Carreras::all();
            foreach ($carreras as $carrera) {
                // Obtener todos los grupos asociados a la carrera
                $grupos = Grupos::where('clave_carrera', $carrera->clave_carrera)->get();
                $totalEstudiantes = 0;
                foreach ($grupos as $grupo) {
                    // Obtener todos los estudiantes asociados al grupo
                    $estudiantes = Estudiantes::where('clave_grupo', $grupo->clave_grupo)->get();
                    // Sumar la cantidad de estudiantes en el grupo actual
                    $totalEstudiantes += $estudiantes->count();
                }
                // Almacenar el resultado en el arreglo incluso si no hay estudiantes
                $resultados[$carrera->nombre] = $totalEstudiantes;
            }

            // Devolver la respuesta exitosa con los resultados
            return ApiResponses::success('Total de estudiantes por programa', 200,[$resultados]);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function totalEstudiantesCarrerasPorCuc()
    {
        try {
            $usuarios = auth()->user();
            $rol = $usuarios->rol->nombre;

            if ($rol === 'consejero') {
                $claveCuc = $usuarios->consejero->clave_cuc;
            }else if($rol === 'escolar'){
                $claveCuc = $usuarios->escolar->clave_cuc;
            }
            $resultados = [];
            // Obtener todas las carreras asociadas al CUC
            $carreras = Carreras::whereHas('grupos', function ($query) use ($claveCuc) {
                $query->where('clave_grupo', 'like', substr($claveCuc, 7, 9) . '%');
            })->get();

            foreach ($carreras as $carrera) {
                // Obtener todos los grupos asociados a la carrera y al CUC
                $grupos = Grupos::where('clave_carrera', $carrera->clave_carrera)
                    ->where('clave_grupo', 'like', substr($claveCuc, 7, 9) . '%')
                    ->get();
                $totalEstudiantess = 0;
                foreach ($grupos as $grupo) {
                    // Obtener todos los estudiantes asociados al grupo
                    $estudiantes = Estudiantes::where('clave_grupo', $grupo->clave_grupo)->get();
                    // Sumar la cantidad de estudiantes en el grupo actual
                    $totalEstudiantess += $estudiantes->count();
                }
                // Almacenar el resultado en el arreglo incluso si no hay estudiantes
                $resultados[$carrera->nombre] = $totalEstudiantess;
            }
            return ApiResponses::success('Total de estudiantes por programa', 200, [$resultados]);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }


    public function servicioEstatus($id)
    {
      try {
       
        // Buscar el estudiante por su ID
        $estudiante = Estudiantes::findOrFail($id);
    
        return ApiResponses::success('Encontrado', 200, $estudiante->servicio_estatus);
      } catch (ModelNotFoundException $e) {
        return ApiResponses::error('Estudiante no encontrado', 404);
      } catch (Exception $e) { // Capturar cualquier otra excepción
        return ApiResponses::error('Error interno del servidor', 500);
      }
    }

    public function matriculaActual()
    {
        
try{

        $user = Auth::user();
        $id = $user->id;

       // $estudiante = Estudiantes::findOrFail($id);
        $estudiante = Estudiantes::where('id', $id)->firstOrFail();

        return ApiResponses::success('Encontrado', 200,$estudiante->matricula);
    } catch (ModelNotFoundException $e) {
      return ApiResponses::error('Estudiante no encontrado', 404);
    } catch (Exception $e) { // Capturar cualquier otra excepción
      return ApiResponses::error('Error interno del servidor', 500);
    }
    }


    public function infoPersonal()
    {
        try {
           
            $user = Auth::user();
            $id = $user->id;
    
           // $estudiante = Estudiantes::findOrFail($id);
          $estudia = Estudiantes::where('id', $id)->firstOrFail();

          $matricula = $estudia->matricula;



          $estudiante = Estudiantes::with('usuario.rol','grupo' ,'direccion.colonia.cp',
          'direccion.colonia.municipio.estado', 'tiposangre','lenguaindigena','puebloindigena', 'nacionalidad', 'estado', 'documento')->findOrFail($matricula);

    
            // Buscar al estudiante por su matrícula y cargar las relaciones necesarias
//$estudiante = Estudiantes::where('matricula', $matricula)->firstOrFail();

return ApiResponses::success('Encontrado', 200,$estudiante);
} catch (ModelNotFoundException $e) {
  return ApiResponses::error('Estudiante no encontrado', 404);
} catch (Exception $e) { // Capturar cualquier otra excepción
  return ApiResponses::error('Error interno del servidor', 500);
}
    }




    public function actualizaInfo(Request $request, $idEstudiante)
    {
        DB::beginTransaction();
        try{
            $request->validate([
                'nombre'=> 'required',
                'apellidopaterno'=> 'required',
                'apellidomaterno'=> 'required',
                'edad'=> 'required',
                'sexo'=> 'required',
                'fecha_nacimiento'=> 'required',
                'niveleducativo'=> 'required',
                'telefono'=> 'required',
                'telefono_emergencia'=> 'required',
                'num_exterior' => 'required',
                'calle' => 'required',
                'colonia' => 'required',
                'nacionalidad'=> 'required',
                'id_tiposangre'=> 'required',
                'padecimiento'=> 'required',
                'discapacidad'=> 'required',
                'regular'=> 'required',
                'semestre'=> 'required',
                'estatus'=> 'required',
                'lengua_indigena'=> 'required',
                'pueblo_indigena'=> 'required',
                'clave_grupo'=> 'required|exists:grupos,clave_grupo',
                'email' => 'required',
                'otro_pueblo',
                'otra_lengua',
                //'documentacion' => 'array'
            ]);
            $lengua = $request->otra_lengua;
            $pueblo = $request->otro_pueblo;
            $nacionaalidad = $request->otra_nacionalidad;
            $nacionalidad = (string) $request->nacionalidad;

            $estudiante = Estudiantes::findOrFail($idEstudiante);

            $direccion = $estudiante->direccion;
            $usuario = $estudiante->usuario;
            $usuario->email = $request->email;
            $usuario->update();

            $estudiante->matricula = $request->input('matricula');
            $estudiante->nombre = $request->nombre;
            $estudiante->apellido_paterno = $request->apellidopaterno;
            $estudiante->apellido_materno = $request->apellidomaterno;
            $estudiante->edad = $request->edad;
            $estudiante->sexo = $request->sexo;
            $estudiante->fecha_nacimiento = $request->fecha_nacimiento;
            $estudiante->nivel_educativo = $request->niveleducativo;
            $estudiante->telefono = $request->telefono;
            $estudiante->telefono_emergencia = $request->telefono_emergencia;
            $estudiante->id_tiposangre = $request->id_tiposangre;
            $estudiante->padecimiento = $request->padecimiento;
            $estudiante->discapacidad = $request->discapacidad;
            $estudiante->regular = $request->regular;
            $estudiante->semestre = $request->semestre;
            $estudiante->estatus = $request->estatus;

            if ($request->nacionalidad === '1' &&  $nacionaalidad !== '') {
                $n = Nacionalidades::where('nombre', $nacionaalidad)->first();

                if (!$n) {
                    $nueva_nacionalidad = new Nacionalidades();
                    $nueva_nacionalidad->nombre = $request->otra_nacionalidad;
                    $nueva_nacionalidad->save();
                    $estudiante->nacionalidad()->associate($nueva_nacionalidad);
                } else {
                    $estudiante->nacionalidad()->associate($n);
                    
                }
            }else{
                $estudiante->id_nacionalidad = $request->nacionalidad;
            }

            if($nacionalidad !== '2'){
                $estudiante->curp = '';
                $estudiante->estado_nacimiento= '33';
            }else {
                $estudiante->curp = $request->curp;
                $estudiante->estado_nacimiento= $request->estado_nacimiento;
            }

            if ($request->lengua_indigena === '2' &&  $lengua !== '') {
                $l = LenguasIndigenas::where('nombre', $lengua)->first();

                if (!$l) {
                    $nueva_lengua = new LenguasIndigenas();
                    $nueva_lengua->nombre = $request->otra_lengua;
                    $nueva_lengua->save();
                    $estudiante->lenguaindigena()->associate($nueva_lengua);
                } else {
                    $estudiante->lenguaindigena()->associate($l);
                    
                }
            }else{
                $estudiante->id_lenguaindigena = $request->lengua_indigena;
            }

            if ($request->pueblo_indigena === '2' &&  $pueblo !== '') {
                $p = PueblosIndigenas::where('nombre', $pueblo)->first();
                
                if (!$p) {
                    $nuevo_pueblo = new PueblosIndigenas();
                    $nuevo_pueblo->nombre =$request->otro_pueblo;
                    $nuevo_pueblo->save();
                    $estudiante->puebloindigena()->associate($nuevo_pueblo);
                } else {
                    $estudiante->puebloindigena()->associate($p);
                }
            }else{
                $estudiante->id_puebloindigena = $request->pueblo_indigena;
            }
            
            $estudiante -> clave_grupo = $request->clave_grupo;
            $direccion->calle = $request->input('calle');
            $direccion->num_exterior = $request->input('num_exterior');
            $direccion->id_colonia = $request->input('colonia');
            $direccion->update();
            $estudiante->update();            

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



    public function obtenerEnvio()
    {
      try {
       
        $user = Auth::user();
        $id = $user->id;
    
        

        // $estudiante = Estudiantes::findOrFail($id);
       $estudiante = Estudiantes::where('id', $id)->firstOrFail();

       $envio = $estudiante->estatus_envio;
    
        return ApiResponses::success('Envio', 200, $envio);
      } catch (ModelNotFoundException $e) {
        return ApiResponses::error('Estudiante no encontrado', 404);
      } catch (Exception $e) { // Capturar cualquier otra excepción
        return ApiResponses::error('Error interno del servidor', 500);
      }
    }

    
    public function enviadoEstatus()
    {
        try {

            $user = Auth::user();
            $id = $user->id;
        
            
    
            // $estudiante = Estudiantes::findOrFail($id);
           $estudiante = Estudiantes::where('id', $id)->firstOrFail();
    

            $estudiante->estatus_envio = 1;
            $estudiante->save();
    
            return ApiResponses::success('Estatus enviado ', 200, $estudiante->estatus_envio);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function cambiarEstatus($matricula, $estado)
    {
        try {

            // $estudiante = Estudiantes::findOrFail($id);
           $estudiante = Estudiantes::where('matricula', $matricula)->firstOrFail();
    

            $estudiante->estatus_envio = $estado;
            $estudiante->save();
    
            return ApiResponses::success('Estatus se cambio', 200, $estudiante->estatus_envio);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function enviarComentario($matricula, $comentario)
    {
        try {

            // $estudiante = Estudiantes::findOrFail($id);
           $estudiante = Estudiantes::where('matricula', $matricula)->firstOrFail();
    

            $estudiante->comentario = $comentario;
            $estudiante->save();
    
            return ApiResponses::success('Comentario Enviado', 200, $estudiante->comentario);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }





    public function obtenerEnvioInfo($matricula)
    {
      try {
       
        $estudiante = Estudiantes::where('matricula', $matricula)->firstOrFail();


       $envio = $estudiante->estatus_envio;
    
        return ApiResponses::success('Envio', 200, $envio);
      } catch (ModelNotFoundException $e) {
        return ApiResponses::error('Estudiante no encontrado', 404);
      } catch (Exception $e) { // Capturar cualquier otra excepción
        return ApiResponses::error('Error interno del servidor', 500);
      }
    }



    
    public function infoPage()
    {
        try {
            $user = Auth::user();
            $id = $user->id;

            $email= $user->email;


            $estudiante = Estudiantes::with(
                'grupo.carrera'
                
            )->where('id', $id)->firstOrFail();

            $carrera = $estudiante->grupo->clave_carrera;

           $cuc = cuc_carrera::where('clave_carrera', $carrera)->first();
           $claveCuc = $cuc->clave_cuc;
            

           $c=Cucs::where('clave_cuc', $claveCuc)->first();
           $nombre = $c->nombre;


           $estudianteData = [
            'matricula' =>$estudiante->matricula,
            'apellido_paterno' => $estudiante->apellido_paterno,
            'apellido_materno' => $estudiante->apellido_materno,
            'nombre' => $estudiante->nombre,
            'correo' => $email,
            'semestre' => $estudiante->semestre,
            'carrera' => $estudiante->grupo->carrera->nombre,
            'cuc' =>$nombre
            // Agrega más campos aquí si es necesario
        ];



           $response = [
            'estudiante' => $estudianteData,
            'nombre' => $nombre
        ];


            // Si se encuentra el servicio, devolver una respuesta exitosa
            return ApiResponses::success('Servicio encontrado', 200, $estudianteData);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('Estudiante no encontrado', 404);
        } catch (Exception $e) {
            // Capturar cualquier otra excepción y devolver un error interno del servidor
            return ApiResponses::error('Error interno del servidor: ' . $e->getMessage(), 500);
        }
    }




    public function obtenerEstado($matricula)
    {
      try {
       
       
       $estudiante = Estudiantes::where('matricula', $matricula)->firstOrFail();

       $envio = $estudiante->estatus_envio;
    
        return ApiResponses::success('Envio', 200, $envio);
      } catch (ModelNotFoundException $e) {
        return ApiResponses::error('Estudiante no encontrado', 404);
      } catch (Exception $e) { // Capturar cualquier otra excepción
        return ApiResponses::error('Error interno del servidor', 500);
      }
    }



    public function obtenerComentario( )
    {
      try {
        
       
 $user = Auth::user();
 $id = $user->id;

 

 // $estudiante = Estudiantes::findOrFail($id);
$estudiante = Estudiantes::where('id', $id)->firstOrFail();

$comentario = $estudiante->comentario;

 return ApiResponses::success('Envio', 200, $comentario);
      } catch (ModelNotFoundException $e) {
        return ApiResponses::error('Estudiante no encontrado', 404);
      } catch (Exception $e) { // Capturar cualquier otra excepción
        return ApiResponses::error('Error interno del servidor', 500);
      }
    }




}