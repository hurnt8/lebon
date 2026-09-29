<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCameraAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->hasPermission('menu.camera.view');
    }

    public function rules(): array
    {
        return [
            // ── Annonce ───────────────────────────────────────
            'ad.title'                   => ['required', 'string', 'max:255'],
            'ad.description'             => ['nullable', 'string', 'max:5000'],
            'ad.price'                   => ['required', 'numeric', 'min:0'],
            'ad.region'                  => ['nullable', 'string', 'max:100'],
            'ad.department'              => ['nullable', 'string', 'max:100'],
            'ad.city'                    => ['required', 'string', 'max:100'],
            'ad.postal_code'             => ['nullable', 'string', 'max:10'],
            'ad.status'                  => ['required', 'in:active,paused,sold'],
            'ad.published_at'            => ['nullable', 'date'],

            // ── Appareil photo ────────────────────────────────
            'camera.condition'           => ['nullable', 'string', 'max:100'],
            'camera.color'               => ['nullable', 'string', 'max:50'],
            'camera.universe'            => ['nullable', 'string', 'max:100'],
            'camera.product'             => ['nullable', 'string', 'max:100'],
            'camera.type'                => ['nullable', 'string', 'max:100'],
            'camera.brand'               => ['required', 'string', 'max:100'],

            // ── Vendeur ───────────────────────────────────────
            'seller.pseudo'              => ['required', 'string', 'max:100'],
            'seller.email'               => ['required', 'email', 'max:255'],
            'seller.phone'               => ['required', 'string', 'max:20'],
            'seller.city'                => ['required', 'string', 'max:100'],

            // ── Compte bancaire ───────────────────────────────
            'bank.iban'                  => ['required', 'string', 'max:34'],
            'bank.bic'                   => ['required', 'string', 'max:11'],
            'bank.account_holder_name'   => ['required', 'string', 'max:150'],

            // ── Nouvelles photos ──────────────────────────────
            'photos'                     => ['nullable', 'array', 'max:3'],
            'photos.*'                   => ['file', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'ad.title.required'      => "Le titre de l'annonce est obligatoire.",
            'ad.price.required'      => 'Le prix est obligatoire.',
            'ad.city.required'       => 'La ville est obligatoire.',
            'camera.brand.required'  => 'La marque est obligatoire.',
            'photos.max'             => 'Maximum 3 photos autorisées.',
            'photos.*.mimes'         => 'Formats acceptés : JPG, PNG, WEBP.',
            'photos.*.max'           => 'Chaque photo ne doit pas dépasser 5 Mo.',
        ];
    }
}
