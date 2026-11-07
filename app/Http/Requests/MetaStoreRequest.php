<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida la creación de una meta (convertir un antojo en meta).
 */
class MetaStoreRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // El perfil no se envía: lo toma el servicio del usuario que inició sesión.
            'producto_id' => 'required|string|max:40',
            'nombre_producto' => 'required|string|max:255',
            'imagen' => 'nullable|url|max:255',
            'precio' => 'required|numeric|gt:0',
            'moneda' => 'required|in:COP,USD,EUR',
            'descuento' => 'nullable|integer|min:0|max:100',
            'fecha_estimada' => 'nullable|date|after_or_equal:today',
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
            'producto_id.required' => 'Falta el producto de la meta.',
            'nombre_producto.required' => 'Falta el nombre del producto.',
            'imagen.url' => 'La imagen debe ser una dirección web válida.',
            'precio.required' => 'Falta el precio.',
            'precio.gt' => 'El precio debe ser mayor que cero.',
            'moneda.required' => 'Falta la moneda.',
            'moneda.in' => 'La moneda debe ser COP, USD o EUR.',
            'descuento.min' => 'El descuento no puede ser negativo.',
            'descuento.max' => 'El descuento no puede pasar de 100 %.',
            'fecha_estimada.after_or_equal' => 'La fecha estimada no puede estar en el pasado.',
        ];
    }
}
