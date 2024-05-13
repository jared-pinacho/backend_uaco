<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FaseUno extends Model
{
    use HasFactory;
    use SoftDeletes;

  protected $table = 'fase_unos';

  protected $primaryKey = 'id_faseUno';

  protected $fillable = ['carta_presentacion','carta_aceptacion','estatus_envio','com_pres','com_acep','id_servicio','pres_estado','acep_estado'];

  protected $casts = [
    'id_faseUno'=> 'string'
  ];

  public function servicio()
{
    return $this->belongsTo(Servicio::class, 'id_servicio');
}

}
