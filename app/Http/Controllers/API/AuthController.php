<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponses;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

use Laravel\Passport\Bridge\AccessToken;

class AuthController extends Controller
{
    //
    // public function registrar(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'name' => 'required',
    //             'email' => 'required|unique:users',
    //             'password' => 'required',
    //         ]);
    //         $pass = Hash::make($request->password);
    //         $user = new User();
    //         $user->name = $request->name;
    //         $user->email = $request->email;
    //         $user->password = $pass;
    //         $user->save();
    //         $accessToken = $user->createToken('authToken')->accessToken;
    //         return ApiResponses::success('Registro exitoso', 201, [$user, ['accessToken' => $accessToken]]);
    //     } catch (\Exception $ex) {
    //         return ApiResponses::error('Error: ' . $ex->getMessage(), 500);
    //     }
    // }

    public function login(Request $request)
    {
        try {
            $loginData = $request->validate([
                'email' => 'required',
                'password' => 'required',
            ]);
            if (!Auth::attempt($loginData)) {
                return ApiResponses::success('Usuario No registrado', 401);
            }
            // $usuario = auth()->user();
            $usuario = Auth::user();
            $nombre = null;

            if ($usuario->consejero) {
                $nombre = $usuario->consejero->nombre;
            } elseif ($usuario->facilitador) {
                $nombre = $usuario->facilitador->nombre;
            } elseif ($usuario->coordinador) {
                $nombre = $usuario->coordinador->nombre;
            } elseif ($usuario->administrativo) {
                $nombre = $usuario->administrativo->nombre;
            }elseif ($usuario->escolar) {
                $nombre = $usuario->escolar->nombre;
            }elseif ($usuario->estudiante) {
                $nombre = $usuario->estudiante->nombre;
            }
            $rol = $usuario->rol->nombre;
            $accessToken = $usuario->createToken('authToken')->plainTextToken;
            // $refreshToken = $usuario->createToken('refreshToken')->refreshToken;


            return ApiResponses::success("Ingreso exitoso", 200, [
                'token' => $accessToken,

                'nombre' => $nombre,
                'rol' => $rol,

            ]);
        } catch (Exception $ex) {
            return ApiResponses::error('Error: ' . $ex->getMessage(), 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $token = auth()->user()->tokens()->delete();
            return ApiResponses::success('Sesion Cerrada', 200);
        } catch (Exception $error) {
            return ApiResponses::error('Ocurrio un error' . $error->getMessage(), 401);
        }
    }

    public function olvidoContrasena(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email']);

            $status = Password::sendResetLink($request->only('email'));
            
            if ($status === Password::RESET_LINK_SENT) {
                return ApiResponses::success('Correo de restablecimiento de contraseña enviado', 200);
            } else {
                return ApiResponses::error('Ocurrió un error al enviar el correo de restablecimiento de contraseña', 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Si el usuario no se encuentra en la base de datos.
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        } catch (Exception $error) {
            return ApiResponses::error('Ocurrio un error' . $error->getMessage(), 401);
        }

       
    }
    
    public function resetPassword(Request $request)
    {
        try{  $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
            'token' => 'required',
        ]);
        

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );
        $res="";
        if ($status == Password::PASSWORD_RESET) {
            
            $res='El cambio de contrasena fue exitoso';
            return view('Respuesta',['res'=>$res]);
            // return response()->json(['message' => 'Contraseña restablecida con éxito'], 200);
        } else if($status===Password::INVALID_TOKEN) {
            $res='El token es invalido';
            return view('Respuesta',['res'=>$res]);
        }else if($status===Password::INVALID_USER) {
            $res='El correo es invalido';
            return view('Respuesta',['res'=>$res]);
        }

        }catch(Exception $e){
            return response()->json(['message' => 'Ocurrió un error al restablecer la contraseña'], 500);
        }
      
    }
}
