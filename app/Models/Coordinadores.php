<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coordinadores extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'coordinadores';
    protected $primaryKey = 'matricula';
    protected $fillable = ['nombre','apellido_paterno','apellido_materno','curp','rfc','nivel_educativo','id'];
    
    protected $casts = [
        'matricula' => 'string', 
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id','id');
    }
}
