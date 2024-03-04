<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PueblosIndigenas extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'pueblos_indigenas';
    protected $primaryKey = 'id_puebloindigena';
    protected $fillable =['nombre'];

    public function estudiantes(){
        return $this->hasMany(Estudiantes::class,'id_puebloindigena','id_puebloindigena');
    }
}
