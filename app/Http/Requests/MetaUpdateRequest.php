<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida la actualización de una meta.
 * El perfil y el producto no se pueden cambiar: para otro antojo se crea otra meta.
 * El estado tampoco: lo calcula el servicio a partir de los abonos.
 */
class MetaUpdateRequest extends FormRequest
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
     * "sometimes" permite mandar solo los campos que se quieren cambiar.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre_producto' => 'sometimes|required|string|max:255',
            'imagen' => 'sometimes|nullable|url|max:255',
            'precio' => 'sometimes|required|numeric|gt:0',
            'moneda' => 'sometimes|required|in:COP,USD,EUR',
            'descuento' => 'sometimes|nullable|integer|min:0|max:100',
            'fecha_estimada' => 'sometimes|nullable|date',
        ];
    }
}
