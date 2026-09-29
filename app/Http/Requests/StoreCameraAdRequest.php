<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreCameraAdRequest extends FormRequest
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
            // ── Vendeur ───────────────────────────────────────
            'seller.pseudo'              => ['required', 'string', 'max:100'],
            'seller.email'               => ['required', 'email', 'max:255'],
            'seller.phone'               => ['required', 'string', 'max:20'],
            'seller.city'                => ['required', 'string', 'max:100'],

            // ── Compte bancaire ───────────────────────────────
            'bank.iban'                  => ['required', 'string', 'max:34'],
            'bank.bic'                   => ['required', 'string', 'max:11'],
            'bank.account_holder_name'   => ['required', 'string', 'max:150'],

            // ── Annonce ───────────────────────────────────────
            'ad.title'                   => ['required', 'string', 'max:255'],
            'ad.description'             => ['nullable', 'string', 'max:5000'],
            'ad.price'                   => ['required', 'numeric', 'min:0'],
            'ad.region'                  => ['nullable', 'string', 'max:100'],
            'ad.department'              => ['nullable', 'string', 'max:100'],
            'ad.city'                    => ['required', 'string', 'max:100'],
            'ad.postal_code'             => ['nullable', 'string', 'max:10'],

            // ── Appareil photo ────────────────────────────────
            'camera.condition'           => ['nullable', 'string', 'max:100'],
            'camera.color'               => ['nullable', 'string', 'max:50'],
            'camera.universe'            => ['nullable', 'string', 'max:100'],
            'camera.product'             => ['nullable', 'string', 'max:100'],
            'camera.type'                => ['nullable', 'string', 'max:100'],
            'camera.brand'               => ['required', 'string', 'max:100'],

            // ── Photos (3 maximum) ─────────────────────────────
            'photos'                     => ['required', 'array', 'min:1', 'max:3'],
            'photos.*'                   => ['file', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'seller.pseudo.required'          => 'Le pseudo du vendeur est obligatoire.',
            'seller.email.required'           => "L'email du vendeur est obligatoire.",
            'seller.email.email'              => "L'email du vendeur est invalide.",
            'seller.phone.required'           => 'Le téléphone est obligatoire.',
            'seller.city.required'            => 'La ville du vendeur est obligatoire.',
            'bank.iban.required'              => "L'IBAN est obligatoire.",
            'bank.bic.required'               => 'Le BIC est obligatoire.',
            'bank.account_holder_name.required' => 'Le bénéficiaire est obligatoire.',
            'ad.title.required'               => "Le titre de l'annonce est obligatoire.",
            'ad.price.required'               => 'Le prix est obligatoire.',
            'ad.price.min'                    => 'Le prix doit être positif.',
            'ad.city.required'                => 'La ville est obligatoire.',
            'camera.brand.required'           => 'La marque est obligatoire.',
            'photos.required'                 => 'Au moins une photo est obligatoire.',
            'photos.min'                      => 'Au moins une photo est obligatoire.',
            'photos.max'                      => 'Maximum 3 photos autorisées.',
            'photos.*.mimes'                  => 'Formats acceptés : JPG, PNG, WEBP.',
            'photos.*.max'                    => 'Chaque photo ne doit pas dépasser 5 Mo.',
        ];
    }
}
