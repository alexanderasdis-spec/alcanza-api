<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida el perfil rápido. Es una sola petición para crear y para actualizar,
 * porque POST /api/perfil hace las dos cosas.
 */
class PerfilRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * Son las mismas reglas que valida el formulario de React (utils/validaciones.js).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|min:2|max:40',
            'ahorro_mensual' => 'required|numeric|gt:0|max:100000000',
        ];
    }

    /**
     * Mensajes de error en español (el frontend los muestra junto a cada campo).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'Escribe tu nombre.',
            'nombre.min' => 'El nombre debe tener al menos 2 letras.',
            'nombre.max' => 'El nombre no puede pasar de 40 caracteres.',
            'ahorro_mensual.required' => 'Escribe cuánto ahorras al mes.',
            'ahorro_mensual.numeric' => 'El ahorro debe ser un número.',
            'ahorro_mensual.gt' => 'El ahorro debe ser mayor que cero.',
            'ahorro_mensual.max' => 'Ese monto parece demasiado alto, revísalo.',
        ];
    }
}
