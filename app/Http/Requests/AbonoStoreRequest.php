<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida el registro de un abono en una meta.
 */
class AbonoStoreRequest extends FormRequest
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
     * La fecha es opcional (si no llega, se usa hoy) y no puede estar en el futuro.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'monto' => 'required|numeric|gt:0|max:100000000',
            'fecha' => 'nullable|date|before_or_equal:today',
        ];
    }

    /**
     * Mensajes de error en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'monto.required' => 'Escribe cuánto vas a abonar.',
            'monto.numeric' => 'El abono debe ser un número.',
            'monto.gt' => 'El abono debe ser mayor que cero.',
            'monto.max' => 'Ese monto parece demasiado alto, revísalo.',
            'fecha.date' => 'La fecha no es válida.',
            'fecha.before_or_equal' => 'El abono no puede tener una fecha futura.',
        ];
    }
}
