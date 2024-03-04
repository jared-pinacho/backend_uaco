<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Roles extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'roles';
    protected $primaryKey = 'id_rol';
    protected $fillable=['nombre'];

    public function usuarios(){
        return $this->hasMany(User::class,'id_rol','id_rol');
    }
}
