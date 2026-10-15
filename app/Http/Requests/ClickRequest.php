<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Un lote de clicks.
 *
 * Solo se valida `clicks`: cualquier energía o crédito que mande el cliente
 * se ignora por completo, porque el servidor es la única autoridad sobre los
 * recursos (RNF-01).
 */
class ClickRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'clicks' => [
                'required',
                'integer',
                'min:1',
                'max:'.config('game.limits.clicks_per_request'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'clicks.required' => 'El lote no trae clicks.',
            'clicks.integer' => 'La cantidad de clicks debe ser un número entero.',
            'clicks.min' => 'El lote debe traer al menos un click.',
            'clicks.max' => 'El lote supera el máximo de clicks permitido.',
        ];
    }
}
