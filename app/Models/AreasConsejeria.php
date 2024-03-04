<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AreasConsejeria extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'areas_consejerias';
    protected $primaryKey = 'id_areaconsejero';
    protected $fillable =['nombre'];

    public function consejeros(){
        return $this->belongsToMany(Consejeros::class,'tipo_consejeros','id_areaconsejero','matricula');
        //primer clave del modelo actual, y segunda clave con el otro modelo
    }
}
