<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Http\Responses\ApiResponses;
use App\Models\Clases;
use App\Models\Dias;
use App\Models\ClaseEstudiante;
use App\Models\Cucs;
use App\Models\User;
use App\Models\Carreras;
use App\Models\Materias;
use App\Models\Grupos;
use App\Models\Estudiantes;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Clase_estudiantes;
use App\Models\Periodos;
use App\Mail\ContactoMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class ClasesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $clase = Clases::with('periodo','materia','facilitador',
            'cuc','carrera')->get();
            return ApiResponses::success('Lista de Clases', 200, $clase);
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
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;

            $request->validate([
                'clave_clase'=> 'required|unique:clases,clave_clase',
                'nombre'=> 'required',
                'salon'=> 'required',
                'id_periodo'=> 'required',
                'clave_materia'=> 'required',
                'matricula'=> 'required',
                'hora_inicio'=> 'required',
                'hora_final'=> 'required',
                'clave_carrera'=> 'required',
                'dias' => 'array'
            ], [
                'clave_clase.unique' => 'La matricula ya está en uso.', // Personaliza el mensaje de error para 'clave'
            ]);
            $clase = new Clases();
            $clase->clave_clase = $request->input('clave_clase');
            $clase->nombre = $request->nombre;
            $clase->salon = $request->salon;
            $clase->id_periodo = $request->id_periodo;
            $clase->clave_materia = $request->clave_materia;
            $clase->matricula = $request->matricula;
            $clase->hora_inicio = $request->hora_inicio;
            $clase->hora_final = $request->hora_final;
            $clase->clave_carrera = $request->clave_carrera;
            $clase->clave_cuc = $claveCucEscolar;
            $clase->status_escolar = false;
            $clase->status_facilitador = false;

            $clase->save();

            if (!empty($request->dias)) {
                $clase = Clases::findOrFail($request->clave_clase);
                foreach ($request->dias as $idDia) {
                    $dia = Dias::where('id_dia', $idDia)->firstOrFail();
                    $clase->dias()->syncWithoutDetaching([$dia->id_dia]);
                }
            }

            DB::commit();
            return ApiResponses::success('Registro exitoso', 201, $clase);
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
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;
            $cuc = Cucs::where('clave_cuc', $claveCucEscolar)->first();
            $clase = Clases::with('periodo','materia','facilitador','carrera')->findOrFail($id);
            if ($clase->clave_cuc === $cuc->clave_cuc) {
                $id_dias = $clase->dias->pluck('id_dia')->all(); 
                $id_dias = array_map('strval', $id_dias);
                $clase->diasR = $id_dias;
                return ApiResponses::success('Clase encontrado', 200, $clase);
            }
            return ApiResponses::error('La clase no pertenece a ese cuc', 404);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idClase)
    {
        DB::beginTransaction();
        try{
            $request->validate([
                'nombre'=> 'required',
                'salon'=> 'required',
                'id_periodo'=> 'required',
                'clave_materia'=> 'required',
                'matricula'=> 'required',
                'hora_inicio'=> 'required',
                'hora_final'=> 'required',
                'clave_carrera'=> 'required',
                'dias' => 'array'
            ]);

            $clase = Clases::findOrFail($idClase);

            $diasSeleccionados = $request->dias;
            $diasActuales = $clase->dias->pluck('id_dia')->all();

            $diasADesasociar = array_diff($diasActuales, $diasSeleccionados);
            $clase->dias()->detach($diasADesasociar);

            $diasAAsociar = array_diff($diasSeleccionados, $diasActuales);
            $dias = Dias::whereIn('id_dia', $diasAAsociar)->get();
            $clase->dias()->attach($dias);

            $clase->nombre = $request->nombre;
            $clase->salon = $request->salon;
            $clase->id_periodo = $request->id_periodo;
            $clase->clave_materia = $request->clave_materia;
            $clase->matricula = $request->matricula;
            $clase->hora_inicio = $request->hora_inicio;
            $clase->hora_final = $request->hora_final;
            $clase->clave_carrera = $request->clave_carrera;

            $clase->update();
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
    public function destroy(Clases $clases)
    {
        //
    }

    public function clasesPorCarreraPorMateria($claveCarrera, $claveMateria, $idPeriodo){
        try {
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;

            $clases = Clases::where('clave_cuc', $claveCucEscolar)
            ->where('id_periodo', $idPeriodo)
            ->where('clave_carrera', $claveCarrera)
            ->where('clave_materia', $claveMateria)
            ->where('status_facilitador', False)
            ->with('periodo','materia','facilitador',
            'cuc','carrera')->get();

            return ApiResponses::success('Encontrados', 200, $clases);

        } catch(ModelNotFoundException $ex){
            return ApiResponses::error('Error: '.$ex->getMessage(),404);
        }catch (Exception $e) {
            return ApiResponses::error('Error: '.$e->getMessage(),500);
        }
        
    }

    public function clasesPorCarreraPorMateriaDeEscolar($claveCarrera, $claveMateria, $idPeriodo){
        try {
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;

            $clases = Clases::where('clave_cuc', $claveCucEscolar)
            ->where('id_periodo', $idPeriodo)
            ->where('clave_carrera', $claveCarrera)
            ->where('clave_materia', $claveMateria)
            ->where('status_facilitador', True)
            ->with('periodo','materia','facilitador',
            'cuc','carrera')->get();

            return ApiResponses::success('Encontrados', 200, $clases);

        } catch(ModelNotFoundException $ex){
            return ApiResponses::error('Error: '.$ex->getMessage(),404);
        }catch (Exception $e) {
            return ApiResponses::error('Error: '.$e->getMessage(),500);
        }
        
    }

    public function clasesGenerales($claveCarrera, $claveMateria, $idPeriodo){
        try {
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;

            $clases = Clases::where('clave_cuc', $claveCucEscolar)
            ->where('id_periodo', $idPeriodo)
            ->where('clave_carrera', $claveCarrera)
            ->where('clave_materia', $claveMateria)
            ->with('periodo', 'materia', 'facilitador', 'cuc', 'carrera')
            ->get();


            return ApiResponses::success('Encontrados', 200, $clases);

        } catch(ModelNotFoundException $ex){
            return ApiResponses::error('Error: '.$ex->getMessage(),404);
        }catch (Exception $e) {
            return ApiResponses::error('Error: '.$e->getMessage(),500);
        }
    }

    public function asociarEstudiantes(Request $request){
        DB::beginTransaction();
        try {
            $request->validate([
                'clave_clase'=> 'required',
                'estudiantes' => 'array'
            ]);
                $clase = Clases::findOrFail($request->clave_clase);
                $estudiantesSeleccionados = $request->estudiantes;
                $estudiantesActuales = $clase->estudiantes->pluck('matricula')->all();

                $estudiantesADesasociar = array_diff($estudiantesActuales, $estudiantesSeleccionados);
                $clase->estudiantes()->detach($estudiantesADesasociar);

                $estudiantesAAsociar = array_diff($estudiantesSeleccionados, $estudiantesActuales);
                $estudiantes = Estudiantes::whereIn('matricula', $estudiantesAAsociar)->get();

                $valoresPredeterminados = [
                    'asistencia' => '', // Valor en blanco
                    'acreditado' => '', // Valor ""
                    'calificacion' => '0', // Valor "0"
                    'calificacion_letra' => '', // Valor en blanco
                    'retroalimentacion' => '', // Valor en blanco
                ];

                $clase->estudiantes()->attach($estudiantes, $valoresPredeterminados);

            DB::commit();
            return ApiResponses::success('Asociacion exitosa', 201);
        } catch(ModelNotFoundException $ex){
            DB::rollBack();
            return ApiResponses::error('Error: '.$ex->getMessage(),404);
        }catch (Exception $e) {
            DB::rollBack();
            return ApiResponses::error('Error: '.$e->getMessage(),500);
        }
    }

    public function calificarEstudiantes(Request $request) {
        DB::beginTransaction();
        try {
            $request->validate([
                'clave_clase' => 'required',
                'estudiantes' => 'array|required',
            ]);
    
            $clase = Clases::findOrFail($request->clave_clase);
            $estudiantesData = $request->estudiantes;

            $gradoCarrera = $clase->carrera->grado;
            $claveCarrera = $clase->clave_carrera;

            $carrera = Carreras::findOrFail($claveCarrera);
            $creditosCarrera = $carrera->creditos;




    
            // Verificar si la clase ya está calificada
            if ($clase->status_facilitador) {
                return ApiResponses::error('La clase ya está calificada', 400);
            }
    
            foreach ($estudiantesData as $data) {
                $estudiante = Estudiantes::findOrFail($data['matricula']);
                $pivotData = [
                    'asistencia' => $data['asistencia'],
                    'calificacion' => $data['calificacion'],
                    'calificacion_letra' => $data['calificacion_letra'],
                ];
    
                // Verificar si se proporcionó retroalimentación
                if (isset($data['retroalimentacion'])) {
                    $pivotData['retroalimentacion'] = $data['retroalimentacion'];
                }
    
                $clase->estudiantes()->updateExistingPivot($estudiante, $pivotData);
    
                // Actualizar el campo 'acreditado' basado en la calificación
                if($gradoCarrera === 'Licenciatura' || $gradoCarrera === 'Ingenieria' || $gradoCarrera === 'Tecnico Superior Universitario o Profesional Asociado'){
                    $acreditado = ($data['calificacion'] >= 60) ? 'Si' : 'No';
                    $clase->estudiantes()->updateExistingPivot($estudiante, [
                        'acreditado' => $acreditado,
                    ]);
                }else if($gradoCarrera === 'Maestria' || $gradoCarrera === 'Especialidad' || $gradoCarrera === 'Doctorado'){
                    $acreditado = ($data['calificacion'] >= 80) ? 'Si' : 'No';
                    $clase->estudiantes()->updateExistingPivot($estudiante, [
                        'acreditado' => $acreditado,
                    ]);
                }

                if ($acreditado === 'Si') {
                    $estudiante->update([
                        'creditos_acumulados' => $estudiante->creditos_acumulados + $clase->materia->creditos,
                    ]);
                }

                $acumulados=$estudiante->creditos_acumulados;
                if((($acumulados * 100) / $creditosCarrera) >= 70){

                    $id=$estudiante->id;

                    $user = User::findOrFail($id);

                    $correo = $user->email;
               

                    Mail::to( $correo)
                        ->send(new ContactoMailable);

                    $estudiante->update([
                        'servicio_estatus' => true,
                    ]);                            
                }

            }
    
            $clase->update(['status_facilitador' => true]);
    
            DB::commit();
            return ApiResponses::success('Registro exitoso', 201);
        } catch (ModelNotFoundException $ex) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $ex->getMessage(), 404);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function calificarEstudiantesPorEscolar(Request $request) {
        DB::beginTransaction();
        try {
            $request->validate([
                'clave_clase' => 'required',
                'estudiantes' => 'array|required',
            ]);
    
            $clase = Clases::findOrFail($request->clave_clase);
            $estudiantesData = $request->estudiantes;

            $gradoCarrera = $clase->carrera->grado;
    
            // Verificar si la clase ya está calificada
            if ($clase->status_escolar) {
                return ApiResponses::error('La clase ya está calificada', 400);
            }
    
            foreach ($estudiantesData as $data) {
                $estudiante = Estudiantes::findOrFail($data['matricula']);
                $pivotData = [
                    'asistencia' => $data['asistencia'],
                    'calificacion' => $data['calificacion'],
                    'calificacion_letra' => $data['calificacion_letra'],
                ];
    
                // Verificar si se proporcionó retroalimentación
                if (isset($data['retroalimentacion'])) {
                    $pivotData['retroalimentacion'] = $data['retroalimentacion'];
                }
    
                $clase->estudiantes()->updateExistingPivot($estudiante, $pivotData);
    
                // Actualizar el campo 'acreditado' basado en la calificación
                if($gradoCarrera === 'Licenciatura' || $gradoCarrera === 'Ingenieria' || $gradoCarrera === 'Tecnico Superior Universitario o Profesional Asociado'){
                    $acreditado = ($data['calificacion'] >= 60) ? 'Si' : 'No';
                    $clase->estudiantes()->updateExistingPivot($estudiante, [
                        'acreditado' => $acreditado,
                    ]);
                }else if($gradoCarrera === 'Maestria' || $gradoCarrera === 'Especialidad' || $gradoCarrera === 'Doctorado'){
                    $acreditado = ($data['calificacion'] >= 80) ? 'Si' : 'No';
                    $clase->estudiantes()->updateExistingPivot($estudiante, [
                        'acreditado' => $acreditado,
                    ]);
                }
                //$estudiante->update(['semestre' => strval(intval($estudiante->semestre) + 1)]);
                $estudiantesParaAumentoSemestre[] = $estudiante;
            }

            if ($acreditado === 'Si') {
                $estudiante->update([
                    'creditos_acumulados' => $estudiante->creditos_acumulados + $clase->materia->creditos,
                ]);
            }
    
            $clase->update(['status_escolar' => true]);

            $this->AumentoSemestre($request->clave_clase, $estudiantesParaAumentoSemestre);
            DB::commit();
            return ApiResponses::success('Registro exitoso', 201);
        } catch (ModelNotFoundException $ex) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $ex->getMessage(), 404);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function AumentoSemestre($claveClase, $estudiantes) {
        DB::beginTransaction();
        try {
    
            $clase = Clases::findOrFail($claveClase);
            $periodo = $clase->id_periodo;
    
            foreach ($estudiantes as $estudiante) {
                $clases = $estudiante->clases()->where('id_periodo', $periodo)->get();
    
                $flag = true;
                foreach ($clases as $clase) {
                    if (!$clase->status_escolar) {
                        $flag = false;
                        break;
                    }
                }

                if ($flag) {
                    $estudiante->semestre++;
                    $estudiante->save();
                }
            }
    
            DB::commit();
        } catch (ModelNotFoundException $ex) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $ex->getMessage(), 404);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function estudiantesPorCarreraYClase($claveCarrera, $claveClase, $claveMateria)
    {
        try {
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;

            $cuc = Cucs::where('clave_cuc', $claveCucEscolar)->first();
            if (!$cuc) {
                return ApiResponses::error('Error: No se encontró el CUC', 404);
            }

            $ultimosTresDigitosCuc = substr($cuc->clave_cuc, 7, 9);
            $grupos = Grupos::where('clave_grupo', 'like', $ultimosTresDigitosCuc . '%')
                ->where('clave_carrera', $claveCarrera)
                ->get();

            //$idPeriodo = Clases::findOrFail($claveClase)->id_periodo;

            $estudiantesFiltrados = $grupos->flatMap(function ($grupo) use ($claveMateria) {
                return $grupo->estudiantes()
                    ->where('estatus', 'Activo')
                    ->whereDoesntHave('clases', function ($query) use ($claveMateria) {
                        $query->where('clave_materia', $claveMateria)
                            ->where(function ($subquery) {
                                $subquery->where('acreditado', 'Si')
                                        ->orWhere('acreditado', '');
                            });
                    })
                    ->with('grupo')
                    ->get();
            });

            return ApiResponses::success('Encontrado', 200, $estudiantesFiltrados);

        } catch (ModelNotFoundException $ex) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $ex->getMessage(), 404);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function estudiantesDeClaseEnGeneral($claveClase)
    {
        try {
            $clase = Clases::findOrFail($claveClase);
            $estudiantes = Estudiantes::with('grupo')
            ->whereHas('clases', function ($query) use ($claveClase) {
                $query->where('clases.clave_clase', $claveClase);
            })
            ->get();

        return ApiResponses::success('Encontrado', 200, $estudiantes);

        } catch (ModelNotFoundException $ex) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $ex->getMessage(), 404);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function matriculaEstudiantesPorClase($claveClase){
        try {
            $clase = Clases::findOrFail($claveClase);
            $matriculas = $clase->estudiantes->pluck('matricula')->all(); 
            $matriculas = array_map('strval', $matriculas);
            return ApiResponses::success('Encontrado', 200, $matriculas);
        } catch(ModelNotFoundException $ex){
            return ApiResponses::error('Error: '.$ex->getMessage(),404);
        }catch (Exception $e) {
            return ApiResponses::error('Error: '.$e->getMessage(),500);
        }
    }

    public function estudiantesPorClase($claveClase){
        try {
            $clase = Clases::findOrFail($claveClase);
            $estudiantes = $clase->estudiantes()->withPivot(['calificacion', 'calificacion_letra', 'retroalimentacion', 'asistencia'])->get();
            $estudiantesTot = [];
            foreach ($estudiantes as $estudiante) {
                $datosEstudiante = [
                    'matricula' => $estudiante->matricula,
                    'nombre' => $estudiante->nombre,
                    'apellido_paterno' => $estudiante->apellido_paterno,
                    'apellido_materno' => $estudiante->apellido_materno,
                    'calificacion' => $estudiante->pivot->calificacion,
                    'calificacion_letra' => $estudiante->pivot->calificacion_letra,
                    'retroalimentacion' => $estudiante->pivot->retroalimentacion,
                    'asistencia' => $estudiante->pivot->asistencia,
                ];
    
                $estudiantesTot[] = $datosEstudiante;
            }
    
            return ApiResponses::success('Encontrado', 200, $estudiantesTot);
        } catch(ModelNotFoundException $ex){
            return ApiResponses::error('Error: '.$ex->getMessage(),404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: '.$e->getMessage(),500);
        }
    }

    public function clasesDeEstudiantes() {
        try {
            $usuarioEstudiante = auth()->user();
            $matriculaEstudiante = $usuarioEstudiante->estudiante->matricula;
        
            $currentDate = date('Y-m-d'); // Obtén la fecha actual en formato 'YYYY-MM-DD'

            $estudiante = Estudiantes::with('grupo:clave_grupo,nombre') // Cargar solo la columna 'nombre' de la relación 'grupo'
                ->select('matricula','nombre', 'apellido_paterno', 'apellido_materno', 'clave_grupo')
                ->where('matricula', $matriculaEstudiante)
                ->first();

            // $grupo =$estudiante->grupo->with('carrera')->get();
            $grupo = Grupos::with('carrera')
                ->where('clave_grupo', $estudiante->clave_grupo) // Filtrar por el grupo del estudiante logueado
                ->first();
        
            $clases = Clases::whereHas('estudiantes', function ($query) use ($matriculaEstudiante) {
                $query->where('estudiantes.matricula', $matriculaEstudiante);
            })
            ->join('materias', 'clases.clave_materia', '=', 'materias.clave_materia')
            ->join('facilitadores', 'clases.matricula', '=', 'facilitadores.matricula')
            ->join('clase_estudiantes', function ($join) use ($matriculaEstudiante) {
                $join->on('clases.clave_clase', '=', 'clase_estudiantes.clave_clase')
                    ->where('clase_estudiantes.matricula', $matriculaEstudiante); // Califica la columna con el nombre de la tabla
            })
            ->join('periodos', 'clases.id_periodo', '=', 'periodos.id_periodo')
            ->select('materias.nombre as nombre_materia','materias.creditos as creditos_materia','facilitadores.nombre as nombre_facilitador', 
            'facilitadores.apellido_paterno as apellidoP_facilitador','facilitadores.apellido_materno as apellidoM_facilitador',
            'clase_estudiantes.calificacion as calificacion_estudiante','clase_estudiantes.retroalimentacion as retroalimentacion_estudiante','clases.status_facilitador', 'clases.nombre as nombre_clase','clases.salon as salon_clase','clases.hora_inicio', 'clases.hora_final')
            ->where('periodos.fecha_inicio', '<=', $currentDate)
            ->where('periodos.fecha_final', '>=', $currentDate)
            ->get();
            $info = [
                'estudiante' => [
                    [
                    'matricula' => $estudiante->matricula,
                    'nombre' => $estudiante->nombre,
                    'apellido_paterno' => $estudiante->apellido_paterno,
                    'apellido_materno' => $estudiante->apellido_materno,
                    'grupo' => [$grupo],
                    //'carrera' => $carreraNombre,
                    ],
                ],
                'clases' => $clases,
            ];
        
            return ApiResponses::success('Clases del estudiante', 200, $info);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No se encontraron clases para el estudiante en el período especificado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }    
    
    public function clasesDeEstudiantesPorPeriodo($idPeriodo) {
        try {
            $usuarioEstudiante = auth()->user();
            $matriculaEstudiante = $usuarioEstudiante->estudiante->matricula;
    
            $clases = Clases::where('clases.id_periodo', $idPeriodo)
                ->whereHas('estudiantes', function ($query) use ($matriculaEstudiante) {
                    $query->where('estudiantes.matricula', $matriculaEstudiante); // Califica la columna con el nombre de la tabla
                })
                ->join('materias', 'clases.clave_materia', '=', 'materias.clave_materia')
                ->join('facilitadores', 'clases.matricula', '=', 'facilitadores.matricula')
                ->join('clase_estudiantes', function ($join) use ($matriculaEstudiante) {
                    $join->on('clases.clave_clase', '=', 'clase_estudiantes.clave_clase')
                        ->where('clase_estudiantes.matricula', $matriculaEstudiante); // Califica la columna con el nombre de la tabla
                })
                ->join('periodos', 'clases.id_periodo', '=', 'periodos.id_periodo')
                ->select(
                    'materias.nombre as nombre_materia',
                    'materias.creditos as creditos_materia', 
                    DB::raw("CONCAT(facilitadores.nombre, ' ', facilitadores.apellido_paterno, ' ', facilitadores.apellido_materno) as nombre_facilitador"), 
                    'clase_estudiantes.calificacion as calificacion_estudiante', 
                    'clases.status_facilitador', 
                    'clases.nombre as nombre_clase', 
                    'clases.hora_inicio', 
                    'clases.hora_final')
                ->get();
    
            return ApiResponses::success('Clases del estudiante', 200, $clases);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No se encontraron clases para el estudiante en el período especificado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function TotalClasesDeEstudiantes() {
        try {
            $usuarioEstudiante = auth()->user();
            $matriculaEstudiante = $usuarioEstudiante->estudiante->matricula;
    
            $clasesPorPeriodo = Clases::whereHas('estudiantes', function ($query) use ($matriculaEstudiante) {
                $query->where('estudiantes.matricula', $matriculaEstudiante);
            })
            ->join('materias', 'clases.clave_materia', '=', 'materias.clave_materia')
            ->join('clase_estudiantes', function ($join) use ($matriculaEstudiante) {
                $join->on('clases.clave_clase', '=', 'clase_estudiantes.clave_clase')
                    ->where('clase_estudiantes.matricula', $matriculaEstudiante);
            })
            ->join('periodos', 'clases.id_periodo', '=', 'periodos.id_periodo')
            ->select(
                'periodos.nombre as nombre_periodo',
                'materias.nombre as nombre_materia',
                'materias.creditos as creditos_materia',
                'clase_estudiantes.calificacion as calificacion_estudiante',
                'clase_estudiantes.acreditado as acreditado_estudiante'
            )
            ->groupBy('periodos.nombre', 'materias.nombre', 'materias.creditos', 'clase_estudiantes.calificacion','acreditado_estudiante')
            ->get();
        $clasesPorPeriodo = collect($clasesPorPeriodo);
        $resultadosAgrupados = $clasesPorPeriodo->groupBy('nombre_periodo')->toArray();
        $resultadoFinal = [];
        foreach ($resultadosAgrupados as $nombrePeriodo => $clases) {
            $clases = collect($clases);
            $resultadoFinal[$nombrePeriodo] = $clases->map(function ($clase) {
                unset($clase['nombre_periodo']);
                return $clase;
            })->toArray();
        }

        return ApiResponses::success('Clases del estudiante por periodo', 200, $resultadoFinal);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No se encontraron clases para el estudiante en el período especificado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function TotalClasesDeEstudiantesGeneral($matriculaEstudiante) {
        try {
            $clasesPorPeriodo = Clases::whereHas('estudiantes', function ($query) use ($matriculaEstudiante) {
                    $query->where('estudiantes.matricula', $matriculaEstudiante);
                })
                ->join('materias', 'clases.clave_materia', '=', 'materias.clave_materia')
                ->join('clase_estudiantes', function ($join) use ($matriculaEstudiante) {
                    $join->on('clases.clave_clase', '=', 'clase_estudiantes.clave_clase')
                        ->where('clase_estudiantes.matricula', $matriculaEstudiante);
                })
                ->join('periodos', 'clases.id_periodo', '=', 'periodos.id_periodo')
                ->select(
                    'periodos.nombre as nombre_periodo',
                    'materias.nombre as nombre_materia',
                    'materias.creditos as creditos_materia',
                    'clase_estudiantes.calificacion as calificacion_estudiante',
                    'clase_estudiantes.acreditado as acreditado_estudiante'
                )
                ->groupBy('periodos.nombre', 'materias.nombre', 'materias.creditos', 'clase_estudiantes.calificacion','acreditado_estudiante')
                ->get();
            $clasesPorPeriodo = collect($clasesPorPeriodo);
            $resultadosAgrupados = $clasesPorPeriodo->groupBy('nombre_periodo')->toArray();
            $resultadoFinal = [];
            foreach ($resultadosAgrupados as $nombrePeriodo => $clases) {
                $clases = collect($clases);
                $resultadoFinal[$nombrePeriodo] = $clases->map(function ($clase) {
                    unset($clase['nombre_periodo']);
                    return $clase;
                })->toArray();
            }
    
            return ApiResponses::success('Clases del estudiante por periodo', 200, $resultadoFinal);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No se encontraron clases para el estudiante en el período especificado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }       

    public function periodoDeEstudiantesPorClases() {
        try {
            $usuarioEstudiante = auth()->user();
            $matriculaEstudiante = $usuarioEstudiante->estudiante->matricula;
    
            // Obtén las clases relacionadas con la matrícula del estudiante
            $clasesEstudiante = DB::table('clase_estudiantes')
                ->select('clave_clase')
                ->where('matricula', $matriculaEstudiante)
                ->distinct()
                ->pluck('clave_clase');
    
            // Obtén los periodos relacionados con las clases del estudiante
            $periodos = DB::table('clases')
                ->join('periodos', 'clases.id_periodo', '=', 'periodos.id_periodo')
                ->select('periodos.*')
                ->whereIn('clases.clave_clase', $clasesEstudiante)
                ->distinct()
                ->get();
    
            return ApiResponses::success('Periodos del estudiante', 200, $periodos);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No se encontraron clases para el estudiante en el período especificado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }       

    public function eliminarAsociacionEstudiante(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'clave_clase'=> 'required',
                'matricula' => 'required'
            ]);
            $clase = Clases::findOrFail($request->clave_clase);

            if ($clase->status_facilitador) {
                return ApiResponses::error('No puedes elimar a este estudiante, porque la clase ya ha sido calificada', 400);
            }
            $matricula = $request->matricula;
            $clase->estudiantes()->detach($matricula);
            DB::commit();
            return ApiResponses::success('Estudiante Eliminado', 200);
        } catch (ModelNotFoundException $ex) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $ex->getMessage(), 404);
        }catch (Exception $e) {
            DB::rollBack();
            return ApiResponses::error('Error: ' . $e->getMessage(), 422);
        }
    }

    public function clasesDeEstudiantesPorGrupoPorPeriodo($claveGrupo, $clavePeriodo) {
        try {
            // Obtén los estudiantes del grupo especificado
            $estudiantesGrupo = Estudiantes::with('grupo:clave_grupo,nombre')
                ->select('matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'clave_grupo', 'semestre', 'regular','sexo')
                ->where('clave_grupo', $claveGrupo)
                ->get();
    
            // Obtén las clases y las claves únicas de las clases de los estudiantes del grupo en el periodo especificado
            $clases = [];
            $clavesClases = [];

            foreach ($estudiantesGrupo as $estudiante) {
                $clasesEstudiante = Clases::whereHas('estudiantes', function ($query) use ($estudiante, $clavePeriodo) {
                        $query->where('estudiantes.matricula', $estudiante->matricula);
                    })
                    ->join('materias', 'clases.clave_materia', '=', 'materias.clave_materia')
                    ->join('clase_estudiantes', function ($join) use ($estudiante) {
                        $join->on('clases.clave_clase', '=', 'clase_estudiantes.clave_clase')
                            ->where('clase_estudiantes.matricula', $estudiante->matricula);
                    })
                    ->join('periodos', 'clases.id_periodo', '=', 'periodos.id_periodo')
                    ->select('clases.clave_clase', 'materias.nombre as nombre_materia', 'materias.creditos as creditos_materia', 'clase_estudiantes.calificacion as calificacion_estudiante', 'clase_estudiantes.calificacion_letra as calificacion_estudiante_letra')
                    ->where('periodos.id_periodo', $clavePeriodo)
                    ->get();
    
                $clases[] = [
                    'estudiante' => [
                        'matricula' => $estudiante->matricula,
                        'nombre' => $estudiante->nombre,
                        'apellido_paterno' => $estudiante->apellido_paterno,
                        'apellido_materno' => $estudiante->apellido_materno,
                        'sexo' => $estudiante->sexo,
                        'semestre' => $estudiante->semestre,
                        'regular' => $estudiante->regular,
                        'grupo' => $claveGrupo,
                        
                    ],
                    'clases' => $clasesEstudiante,
                ];
    
                // Almacena las claves únicas de las clases
                foreach ($clasesEstudiante as $claseEstudiante) {
                    if (!in_array($claseEstudiante->clave_clase, $clavesClases)) {
                        $clavesClases[] = $claseEstudiante->clave_clase;
                    }
                }
            }

            $semestres = [];
            foreach ($estudiantesGrupo as $estudiante) {
                $semestres[] = $estudiante->semestre;
            }
            $semestreMasRepetido = collect($semestres)->countBy()->sortDesc()->keys()->first();
    
            // Obtén estudiantes de otras clases con las mismas claves
            $estudiantesOtrasClases = Estudiantes::whereHas('clases', function ($query) use ($clavesClases) {
                $query->whereIn('clases.clave_clase', $clavesClases);
            })
            ->where('estudiantes.clave_grupo', '<>', $claveGrupo)
            ->select('estudiantes.matricula', 'estudiantes.nombre', 'estudiantes.apellido_paterno', 'estudiantes.apellido_materno','estudiantes.sexo','estudiantes.semestre','estudiantes.regular','estudiantes.clave_grupo as grupo')
            ->with(['clases' => function ($query) use ($clavesClases) {
                $query->whereIn('clases.clave_clase', $clavesClases)
                    ->join('materias', 'clases.clave_materia', '=', 'materias.clave_materia')
                    ->join('clase_estudiantes as ce', function ($join) {
                        $join->on('clases.clave_clase', '=', 'ce.clave_clase');
                    })
                    ->select(
                        'clases.clave_clase',
                        'materias.nombre as nombre_materia',
                        'materias.creditos as creditos_materia',
                        DB::raw('MAX(ce.calificacion) as calificacion_estudiante'),
                        DB::raw('MAX(ce.calificacion_letra) as calificacion_estudiante_letra')
                    )
                    ->groupBy('clases.clave_clase');
            }])
            ->get();

            // Transforma el resultado según tus necesidades
            $estudiantesOtrasClasesTransformado = $estudiantesOtrasClases->map(function ($estudiante) {
            return [
                'estudiante' => [
                    'matricula' => $estudiante->matricula,
                    'nombre' => $estudiante->nombre,
                    'apellido_paterno' => $estudiante->apellido_paterno,
                    'apellido_materno' => $estudiante->apellido_materno,
                    'sexo' => $estudiante->sexo,
                    'semestre' => $estudiante->semestre,
                    'regular' => $estudiante->regular,
                    'grupo' => $estudiante->grupo,
                ],
                'clases' => $estudiante->clases,
            ];
            });

            $totalEstudiantesHombres = 0;
            $totalEstudiantesMujeres = 0;

            foreach ($clases as $clase) {
                $estudiante = $clase['estudiante'];
                if ($estudiante['sexo'] == 'H') {
                    $totalEstudiantesHombres++;
                } elseif ($estudiante['sexo'] == 'M') {
                    $totalEstudiantesMujeres++;
                }
            }

            foreach ($estudiantesOtrasClasesTransformado as $estudiante) {
                $otroEstudiante = $estudiante['estudiante']; 
                if ($otroEstudiante['sexo'] == 'H') {
                    $totalEstudiantesHombres++;
                } elseif ($otroEstudiante['sexo'] == 'M') {
                    $totalEstudiantesMujeres++;
                }
            }

            // Total de todos los estudiantes
            $totalEstudiantes = $totalEstudiantesHombres + $totalEstudiantesMujeres;
            
            $nombresMaterias = [];

            // Obtén los nombres de las materias de las clases
            foreach ($clases as $clase) {
                foreach ($clase['clases'] as $claseEstudiante) {
                    $nombreMateria = $claseEstudiante['nombre_materia'];
                    if (!in_array($nombreMateria, $nombresMaterias)) {
                        $nombresMaterias[] = $nombreMateria;
                    }
                }
            }
    
            foreach ($estudiantesOtrasClasesTransformado as $estudiante) {
                foreach ($estudiante['clases'] as $claseEstudiante) {
                    $nombreMateria = $claseEstudiante['nombre_materia'];
                    if (!in_array($nombreMateria, $nombresMaterias)) {
                        $nombresMaterias[] = $nombreMateria;
                    }
                }
            }

            $grupo = Grupos::find($claveGrupo);
            $periodo = Periodos::find($clavePeriodo);
            $semestreEnAsociacion = -1;
            if ($grupo && $periodo) {
                // Verificar si ya existe la asociación
                $asociacionExistente = $grupo->periodos()->where('periodos.id_periodo', $periodo->id_periodo)->first();
            
                if (!$asociacionExistente) {
                    $semestreAsociar = $semestreMasRepetido ?? 0;
                    // Si no existe, entonces realizar la asociación
                    $grupo->periodos()->attach($periodo->id_periodo, [
                        'aprobado_inicio' => false,
                        'aprobado_final' => false,
                        'semestre' => $semestreAsociar,
                    ]);
                } else {
                    // Obtener directamente el valor del semestre desde la tabla intermedia
                    $semestreEnAsociacion = (int)$asociacionExistente->pivot->semestre ?? -1;
                }
            }
            
            // Añade el resultado transformado a tu respuesta
            return ApiResponses::success('Clases de los estudiantes del grupo', 200, [
                'estudiantes_grupo' => $clases,
                'estudiantes_otras_clases' => $estudiantesOtrasClasesTransformado,
                'nombres_materias' => $nombresMaterias,
                'total_estudiantes_hombres' => $totalEstudiantesHombres,
                'total_estudiantes_mujeres' => $totalEstudiantesMujeres,
                'total_estudiantes' => $totalEstudiantes,
                'semestre' => $semestreEnAsociacion,
            ]);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No se encontraron clases para el grupo y periodo especificados', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }  
}
