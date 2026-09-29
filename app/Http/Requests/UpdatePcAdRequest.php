<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePcAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->hasPermission('menu.pc.view');
    }

    public function rules(): array
    {
        return [
            // ── Annonce ───────────────────────────────────────
            'ad.title'                   => ['required', 'string', 'max:255'],
            'ad.description'             => ['nullable', 'string', 'max:5000'],
            'ad.price'                   => ['required', 'numeric', 'min:0'],
            'ad.city'                    => ['required', 'string', 'max:100'],
            'ad.postal_code'             => ['nullable', 'string', 'max:10'],
            'ad.status'                  => ['required', 'in:active,paused,sold'],

            // ── Ordinateur ────────────────────────────────────
            'computer.brand'             => ['required', 'string', 'max:100'],
            'computer.model'             => ['required', 'string', 'max:100'],
            'computer.cpu'               => ['required', 'string', 'max:100'],
            'computer.ram_gb'            => ['required', 'integer', 'min:1', 'max:512'],
            'computer.storage_type'      => ['required', 'string', 'in:ssd,hdd'],
            'computer.storage_gb'        => ['required', 'integer', 'min:1', 'max:20000'],
            'computer.gpu'               => ['nullable', 'string', 'max:100'],
            'computer.screen_size'       => ['nullable', 'numeric', 'min:0', 'max:99'],
            'computer.os'                => ['nullable', 'string', 'max:100'],
            'computer.condition'         => ['nullable', 'string', 'max:100'],
            'computer.color'             => ['nullable', 'string', 'max:50'],

            // ── Équipements ───────────────────────────────────
            'features'                   => ['nullable', 'array'],
            'features.*'                 => ['string', 'max:100'],

            // ── Nouvelles photos ──────────────────────────────
            'photos'                     => ['nullable', 'array', 'max:12'],
            'photos.*'                   => ['file', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'ad.title.required'              => "Le titre de l'annonce est obligatoire.",
            'ad.price.required'              => 'Le prix est obligatoire.',
            'ad.city.required'               => "La ville est obligatoire.",
            'computer.brand.required'        => 'La marque est obligatoire.',
            'computer.model.required'        => 'Le modèle est obligatoire.',
            'computer.cpu.required'          => 'Le processeur est obligatoire.',
            'computer.ram_gb.required'       => 'La mémoire vive est obligatoire.',
            'computer.storage_type.required' => 'Le type de stockage est obligatoire.',
            'computer.storage_gb.required'   => 'La capacité de stockage est obligatoire.',
            'photos.*.mimes'                 => 'Formats acceptés : JPG, PNG, WEBP.',
            'photos.*.max'                   => 'Chaque photo ne doit pas dépasser 5 Mo.',
        ];
    }
}
