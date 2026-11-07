<?php

namespace App\Http\Controllers;

use App\Http\Requests\CambiarContrasenaRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\OlvideContrasenaRequest;
use App\Http\Requests\RegistroRequest;
use App\Http\Requests\RestablecerContrasenaRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

/**
 * Endpoints de las cuentas: registro, inicio y cierre de sesión, usuario actual
 * y recuperación de contraseña.
 */
class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Crea una cuenta y devuelve el usuario con su token - METHOD POST
     */
    public function registro(RegistroRequest $request)
    {
        return response()->json($this->authService->registrar($request->validated()), 201);
    }

    /**
     * Inicia sesión y devuelve el usuario con un token nuevo - METHOD POST
     */
    public function login(LoginRequest $request)
    {
        $sesion = $this->authService->iniciarSesion($request->validated('email'), $request->validated('password'));

        return response()->json($sesion, 200);
    }

    /**
     * Devuelve el usuario dueño del token - METHOD GET
     */
    public function usuario(Request $request)
    {
        return response()->json($this->authService->datosPublicos($request->user()), 200);
    }

    /**
     * Cierra la sesión (invalida el token actual) - METHOD POST
     */
    public function logout(Request $request)
    {
        $this->authService->cerrarSesion($request->user());

        return response()->json(null, 204);
    }

    /**
     * Cambia la contraseña con la sesión iniciada - METHOD PUT
     * Las demás sesiones abiertas se cierran; la actual sigue funcionando.
     */
    public function cambiarContrasena(CambiarContrasenaRequest $request)
    {
        $this->authService->cambiarContrasena($request->user(), $request->validated('password'));

        return response()->json(['message' => 'Tu contraseña se cambió.'], 200);
    }

    /**
     * Envía el correo para cambiar la contraseña - METHOD POST
     * Siempre responde lo mismo, exista o no la cuenta.
     */
    public function olvideContrasena(OlvideContrasenaRequest $request)
    {
        $this->authService->enviarEnlace($request->validated('email'));

        return response()->json([
            'message' => 'Si el correo está registrado, te enviamos un enlace para cambiar la contraseña.',
        ], 200);
    }

    /**
     * Cambia la contraseña con el token del correo - METHOD POST
     */
    public function restablecerContrasena(RestablecerContrasenaRequest $request)
    {
        $this->authService->restablecer($request->validated());

        return response()->json(['message' => 'Tu contraseña se cambió. Ya puedes iniciar sesión.'], 200);
    }
}
