<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'id_rol'
    ];

    public function rol()
    {
        return $this->belongsTo(Roles::class, 'id_rol', 'id_rol');
    }
    public function consejero()
    {
        return $this->hasOne(Consejeros::class, 'id');
    }

    public function escolar()
    {
        return $this->hasOne(Escolares::class, 'id');
    }

    public function estudiante()
    {
        return $this->hasOne(Estudiantes::class, 'id');
    }

    public function facilitador()
    {
        return $this->hasOne(Facilitadores::class, 'id','id');
    }

    public function administrativo()
    {
        return $this->hasOne(Administrativos::class, 'id','id');
    }

    public function coordinador()
    {
        return $this->hasOne(Coordinadores::class, 'id','id');
    }


    // public function consejero()
    // {
    //     return $this->belongsTo(Consejeros::class, 'id', 'id');
    // }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
