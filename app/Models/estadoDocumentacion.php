<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class estadoDocumentacion extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'estado_documentacions';
    protected $primaryKey = 'id_documento';
    protected $fillable = ['certificado_terminacion_estudios','acta_examen','titulo_electronico','liberacion_servicio_social','matricula'];

    public function estudiante(){
        return $this->belongsTo(Estudiantes::class,'matricula','matricula');
    }
}
