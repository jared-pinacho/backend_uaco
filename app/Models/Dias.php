<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dias extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'dias';
    protected $primaryKey = 'id_dia';
    protected $fillable =['nombre'];

    public function clases(){
        return $this->belongsToMany(Clases::class,'clase_dias','id_dia','clave_clase');
        //primer clave del modelo actual, y segunda clave con el otro modelo
    }
}
