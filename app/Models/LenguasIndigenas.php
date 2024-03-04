<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LenguasIndigenas extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'lenguas_indigenas';
    protected $primaryKey = 'id_lenguaindigena';
    protected $fillable =['nombre'];

    public function estudiantes(){
        return $this->hasMany(Estudiantes::class,'id_lenguaindigena','id_lenguaindigena');
    }
}
