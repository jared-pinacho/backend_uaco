<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nacionalidades extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'nacionalidades';
    protected $primaryKey = 'id_nacionalidad';
    protected $fillable =['nombre'];

    public function estudiantes(){
        return $this->hasMany(Estudiantes::class,'id_nacionalidad','id_nacionalidad');
    }

    public function consejeros(){
        return $this->hasMany(Consejeros::class,'id_nacionalidad','id_nacionalidad');
    }

    public function facilitadores(){
        return $this->hasMany(Facilitadores::class,'id_nacionalidad','id_nacionalidad');
    }
    
    public function escolares(){
        return $this->hasMany(Escolares::class,'id_nacionalidad','id_nacionalidad');
    }
}
