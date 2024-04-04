<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FaseCuatro extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'fase_cuatros';

    protected $primaryKey = 'id_faseCuatro';
  
    protected $fillable = ['reporte_tres','estatus','comentario','id_servicio'];

    protected $casts = [
        'id_faseCuatro'=> 'string'
      ];
    
      public function servicio()
      {
          return $this->belongsTo(Servicio::class, 'id_servicio');
      }
      
}
