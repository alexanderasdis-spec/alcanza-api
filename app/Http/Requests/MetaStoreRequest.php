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
            'perfil_id' => 'required|integer|exists:perfiles,id',
            'producto_id' => 'required|string|max:40',
            'nombre_producto' => 'required|string|max:255',
            'imagen' => 'nullable|url|max:255',
            'precio' => 'required|numeric|gt:0',
            'moneda' => 'required|in:COP,USD,EUR',
            'descuento' => 'nullable|integer|min:0|max:100',
            'fecha_estimada' => 'nullable|date|after_or_equal:today',
        ];
    }
}
