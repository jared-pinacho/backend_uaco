<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponses;
use App\Models\Carreras;
use App\Models\Cucs;
use App\Models\Grupos;
use App\Models\Periodos;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class GruposController extends Controller
{
    /**
     * Display a listing of the resource. 
     */
    public function index()
    {
        //$grupo = Grupos::withTrashed()->get();
        //$grupo = Grupos::onlyTrashed()->get();
        //$clavecar = $request->claveca;
        //$grupo = Grupos::where('clave_carrera', $clavecar)->get();
        //return $grupo;
        try{
            $grupo = Grupos::with('carrera')->get();
        return ApiResponses::success('Lista de Consejeros',200,$grupo);
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
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;
            $claveCarrera = $request->input('clave_carrera');
            $cuc = Cucs::findOrFail($claveCucEscolar);

            $ultimosTresDigitosCuc = substr($cuc->clave_cuc, 7, 9);

            // Busca los grupos cuya clave_grupo comience con los tres ultimos dígitos del CUC
            $grupos = Grupos::withTrashed()->where('clave_grupo', 'like', $ultimosTresDigitosCuc . '%');

            $ultimos3CaracteresCUC = substr($claveCucEscolar, -3);
            $ultimos3CaracteresCarrera = substr($claveCarrera, -3);
            $year = date('y');

            // Incluye el identificador de la carrera en la búsqueda de grupos
            $grupos = $grupos->where('clave_carrera', $claveCarrera);

            $ultimoGrupo = $grupos->whereYear('created_at', date('Y'))->latest()->first();
            
            if ($ultimoGrupo) {
                // Extrae los últimos 2 dígitos del grupo existente y agrega uno
                $autoincremento = str_pad((int)substr($ultimoGrupo->clave_grupo, -2) + 1, 2, '0', STR_PAD_LEFT);
            } else {
                $autoincremento = '01';
            }

            $claveGrupo = $ultimos3CaracteresCUC . $ultimos3CaracteresCarrera . $year . '-' . $autoincremento;

            $request->validate([
                'nombre' => 'required|unique:grupos,nombre',
                'clave_carrera' => 'required',
            ], [
                'nombre.unique' => 'El nombre del grupo ya está en uso.',
            ]);

            $grupo = new Grupos();
            $grupo->nombre = $request->input('nombre');
            $grupo->clave_carrera = $request->clave_carrera;
            $grupo->clave_grupo = $claveGrupo;

            $grupo->save();

            return ApiResponses::success('Registro exitoso', 201, $grupo);
        } catch (ValidationException $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        } catch (\Exception $ex) {
            return ApiResponses::error('Error: ' . $ex->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        try{
            $usuarioEscolar = auth()->user();
            $claveCucEscolar = $usuarioEscolar->escolar->clave_cuc;
            $cuc = Cucs::where('clave_cuc', $claveCucEscolar)->first();
            $grupo = Grupos::findOrFail($id);

            $ultiomosTresDigitosCuc = substr($cuc->clave_cuc, 7, 9);
            if (strpos($grupo->clave_grupo, $ultiomosTresDigitosCuc) === 0) {
                return ApiResponses::success('Grupo encontrado', 200, $grupo);
            }
            return ApiResponses::error('El grupo no pertenece a ese cuc', 404); 
            
        }catch(ModelNotFoundException $e){
            return ApiResponses::error('Error: '.$e->getMessage(),404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idGrupo)
    {
        //
        try {
            $request->validate([
                'nombre' => 'required',
                'clave_carrera' => 'required',
            ]);
            $grupo=Grupos::findOrFail($idGrupo);
            $grupo->nombre = $request->input('nombre');
            $grupo->clave_carrera = $request->clave_carrera;
            $grupo->update();
            return ApiResponses::success('Actualizado',201);
            //return $cuc;

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
        //
        try{
            $grupo = Grupos::findOrFail($id);
                $grupo->delete();
                return ApiResponses::success('Grupo Eliminado',201);
        }catch(ModelNotFoundException $e){
            return ApiResponses::error('Error: '.$e->getMessage(),404);
        }
    }

    public function estudiantesPorGrupo(Request $request, $grupoId)
    {
        try {
            $grupo = Grupos::findOrFail($grupoId);
            $estudiantes = $grupo->estudiantes()->with('usuario.rol','grupo.carrera' ,'direccion.colonia.cp',
            'direccion.colonia.municipio.estado','tiposangre','lenguaindigena','puebloindigena','nacionalidad', 'estado')->get();

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

            return ApiResponses::success('Consejero del cuc', 200, $estudiantesTransformados);
        } catch (ModelNotFoundException $e) {
            return ApiResponses::error('No encontrado', 404);
        }
    }    

    public function actualizarestadoinicio($claveGrupo, $clavePeriodo) {
        try {
            // Obtener el modelo de Grupo
            $grupo = Grupos::find($claveGrupo);
    
            // Actualizar el valor de 'aprobado_inicio' a true en la tabla muchos a muchos
            $grupo->periodos()->updateExistingPivot($clavePeriodo, ['aprobado_inicio' => true]);
    
            // Devolver respuesta de éxito
            return ApiResponses::success('Estado de inicio actualizado correctamente', 200);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Grupo o periodo no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function actualizarestadofinal($claveGrupo, $clavePeriodo) {
        try {
            // Obtener el modelo de Grupo
            $grupo = Grupos::find($claveGrupo);
    
            // Actualizar el valor de 'aprobado_inicio' a true en la tabla muchos a muchos
            $grupo->periodos()->updateExistingPivot($clavePeriodo, ['aprobado_final' => true]);
    
            // Devolver respuesta de éxito
            return ApiResponses::success('Estado de inicio actualizado correctamente', 200);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Grupo o periodo no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function aprobadoInicioYFinal($claveGrupo, $clavePeriodo) {
        try {
            $grupo = Grupos::with(['periodos' => function ($query) use ($clavePeriodo) {
                $query->where('estado_reporte_grupos.id_periodo', $clavePeriodo)->select(['estado_reporte_grupos.aprobado_inicio', 'estado_reporte_grupos.aprobado_final']);
            }])->where('clave_grupo', $claveGrupo)->first();
        
            if (!$grupo) {
                return response()->json(['message' => 'Grupo no encontrado'], 404);
            }
        
            $datosAsociados = $grupo->periodos->first();
        
            if (!$datosAsociados) {
                return response()->json(['message' => 'No hay datos asociados para este grupo y periodo'], 404);
            }

            // Puedes devolver los valores como desees, en este caso, los estoy devolviendo en formato JSON
            return ApiResponses::success('aprobados', 200, [
                'datos' => $datosAsociados,
            ]);

        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Grupo o periodo no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function gruposPorCarreraPorCuc(Request $request, $claveCarrera, $claveCuc)
    {
        try {
            $carrera = Carreras::where('clave_carrera', $claveCarrera)->firstOrFail();
            $cuc = Cucs::where('clave_cuc', $claveCuc)->firstOrFail();
            $grupos = $carrera->grupos;
            $gruposCumplenCondicion = [];
            foreach ($grupos as $grupo) {
                $claveGrupo = $grupo->clave_grupo;
                // Verificar si los dos primeros caracteres coinciden con $claveCuc
                if ((int)substr($claveGrupo, 0, 2) === (int)substr($claveCuc, 7, 9)) {
                    $gruposCumplenCondicion[] = $grupo;
                }
            }
            return ApiResponses::success('Grupos de cuc y programa', 200, $gruposCumplenCondicion);
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('No encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }

    public function gruposPorCarreraPorCucPorPeriodo($claveCuc, $claveCarrera, $clavePeriodo) {
        try {
            $carrera = Carreras::where('clave_carrera', $claveCarrera)->firstOrFail();
            $cuc = Cucs::where('clave_cuc', $claveCuc)->firstOrFail();
            $grupos = $carrera->grupos;
            $gruposCumplenCondicion = [];
            foreach ($grupos as $grupo) {
                $claveGrupo = $grupo->clave_grupo;
                // Verificar si los dos primeros caracteres coinciden con $claveCuc
                if ((int)substr($claveGrupo, 0, 2) === (int)substr($claveCuc, 7, 9)) {
                    $grupo = Grupos::with(['periodos' => function ($query) use ($clavePeriodo) {
                        $query->where('estado_reporte_grupos.id_periodo', $clavePeriodo)
                            ->select(['estado_reporte_grupos.aprobado_inicio', 'estado_reporte_grupos.aprobado_final']);
                    }, 'carrera'])->where('clave_grupo', $claveGrupo)->first();
    
                    $datosAsociados = $grupo->periodos->first();
                    if ($datosAsociados && ($datosAsociados->aprobado_inicio || $datosAsociados->aprobado_final)) {
                        $gruposCumplenCondicion[] = $grupo->toArray();
                    }
                }
            }
            return ApiResponses::success('Grupos de cuc y programa', 200, ["grupos" => $gruposCumplenCondicion]);
    
        } catch (ModelNotFoundException $ex) {
            return ApiResponses::error('Grupo o periodo no encontrado', 404);
        } catch (Exception $e) {
            return ApiResponses::error('Error: ' . $e->getMessage(), 500);
        }
    }      
}
