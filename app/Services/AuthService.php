<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * Lógica de las cuentas: registro, inicio y cierre de sesión, y recuperación de contraseña.
 *
 * Usa tokens de Laravel Sanctum: al registrarse o iniciar sesión se crea un token,
 * React lo guarda y lo envía en cada petición con el encabezado "Authorization: Bearer <token>".
 */
class AuthService
{
    /** Nombre con el que se guardan los tokens de la app en la tabla personal_access_tokens. */
    private const NOMBRE_TOKEN = 'alcanza-web';

    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepository
    ) {}

    /**
     * Crea la cuenta y deja la sesión iniciada.
     *
     * @param  array{nombre: string, email: string, password: string}  $datos  Datos ya validados.
     * @return array{usuario: array, token: string}
     */
    public function registrar(array $datos): array
    {
        $usuario = $this->usuarioRepository->create([
            'name' => $datos['nombre'],
            'email' => $datos['email'],
            'password' => $datos['password'],
        ]);

        return $this->sesion($usuario);
    }

    /**
     * Comprueba el correo y la contraseña y crea un token nuevo.
     *
     * @throws ValidationException Si el correo o la contraseña no coinciden (respuesta 422).
     */
    public function iniciarSesion(string $email, string $password): array
    {
        $usuario = $this->usuarioRepository->porEmail($email);

        // Mismo mensaje para "no existe" y "contraseña incorrecta": así no se revela qué correos están registrados.
        if ($usuario === null || ! Hash::check($password, $usuario->password)) {
            throw ValidationException::withMessages(['email' => 'El correo o la contraseña no son correctos.']);
        }

        return $this->sesion($usuario);
    }

    /**
     * Cierra la sesión borrando el token con el que se hizo la petición.
     */
    public function cerrarSesion(User $usuario): void
    {
        $usuario->currentAccessToken()->delete();
    }

    /**
     * Cambia la contraseña de un usuario con sesión iniciada y cierra sus otras sesiones
     * (borra todos los tokens menos el que está usando).
     */
    public function cambiarContrasena(User $usuario, string $nueva): void
    {
        $usuario->forceFill(['password' => $nueva])->save();
        $usuario->tokens()->where('id', '!=', $usuario->currentAccessToken()->id)->delete();
    }

    /**
     * Envía el correo con el enlace para cambiar la contraseña.
     * No dice si el correo existe o no, para no revelar qué cuentas hay.
     */
    public function enviarEnlace(string $email): void
    {
        Password::sendResetLink(['email' => $email]);
    }

    /**
     * Cambia la contraseña usando el token que llegó por correo.
     * Al cambiarla se cierran todas las sesiones abiertas de esa cuenta.
     *
     * @param  array{token: string, email: string, password: string, password_confirmation: string}  $datos
     *
     * @throws ValidationException Si el enlace no es válido o ya venció (respuesta 422).
     */
    public function restablecer(array $datos): void
    {
        $estado = Password::reset($datos, function (User $usuario, string $password) {
            $usuario->forceFill(['password' => $password])->save();
            $usuario->tokens()->delete();
        });

        if ($estado !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => 'El enlace no es válido o ya venció. Pide uno nuevo.',
            ]);
        }
    }

    /**
     * Datos públicos del usuario (sin contraseña).
     *
     * @return array{id: int, nombre: string, email: string}
     */
    public function datosPublicos(User $usuario): array
    {
        return ['id' => $usuario->id, 'nombre' => $usuario->name, 'email' => $usuario->email];
    }

    /**
     * Crea un token para el usuario y arma la respuesta de sesión.
     *
     * @return array{usuario: array, token: string}
     */
    private function sesion(User $usuario): array
    {
        return [
            'usuario' => $this->datosPublicos($usuario),
            'token' => $usuario->createToken(self::NOMBRE_TOKEN)->plainTextToken,
        ];
    }
}
