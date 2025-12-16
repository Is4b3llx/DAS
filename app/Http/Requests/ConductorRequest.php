<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConductorRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string',
            'apellido' => 'required|string',
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:' . now()->subYears(18)->format('Y-m-d')],
            'ci' => 'required|string',
            'celular' => 'required|string',
            'id_licencia' => 'required',
        ];
    }
}
