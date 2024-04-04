<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FaseCinco extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'fase_cincos';

    protected $primaryKey = 'id_faseCinco';
  
    protected $fillable = ['carta_terminacion','estatus','comentario','id_servicio'];

    protected $casts = [
        'id_faseCinco'=> 'string'
      ];
    
      public function servicio()
      {
          return $this->belongsTo(Servicio::class, 'id_servicio');
      }
      
}
