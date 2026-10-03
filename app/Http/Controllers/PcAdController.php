<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePcAdRequest;
use App\Http\Requests\UpdatePcAdRequest;
use App\Models\Ad;
use App\Models\AdFeature;
use App\Models\AdPhoto;
use App\Models\Computer;
use App\Models\Seller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PcAdController extends Controller
{
    private function authorizePcAccess(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user->hasPermission('menu.pc.view'), 403);
    }

    private function authorizePcAd(Ad $ad): void
    {
        $this->authorizePcAccess();
        abort_unless(Auth::user()->can('manage', $ad), 403);
    }

    // ── Liste des annonces PC ─────────────────────────────────

    public function index(): View
    {
        $this->authorizePcAccess();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // "Annonces PC" : toujours uniquement les annonces du vendeur connecté,
        // y compris pour un admin. $ads sert seulement au badge de la sidebar,
        // le contenu réel est chargé côté client via axios (voir indexData()).
        $totalAds = Ad::category('pc')
            ->whereHas('seller', fn($q) => $q->where('user_id', $user->id))
            ->count();

        $ads = new \Illuminate\Pagination\LengthAwarePaginator(collect(), $totalAds, 12, 1);

        return view('pc.index', compact('ads'));
    }

    // ── Données JSON des annonces PC (chargées via axios) ────────

    public function indexData(Request $request): JsonResponse
    {
        $this->authorizePcAccess();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Toujours filtré sur le seller_id du vendeur connecté, jamais toutes les annonces.
        $query = Ad::with(['computer', 'photos'])
            ->category('pc')
            ->whereHas('seller', fn($q) => $q->where('user_id', $user->id))
            ->latest();

        $perPage = 12;
        $currentPage = max(1, (int) $request->query('page', 1));
        $paginated = $query->paginate($perPage, ['*'], 'page', $currentPage);

        $items = $paginated->getCollection()->map(function (Ad $ad) {
            return [
                'id'           => $ad->id,
                'status'       => $ad->status,
                'title'        => $ad->title,
                'price'        => $ad->formatted_price,
                'city'         => $ad->city,
                'published_at' => $ad->published_at?->diffForHumans(),
                'photo_url'    => optional($ad->photos->first())->url,
                'computer'     => $ad->computer ? [
                    'cpu'     => $ad->computer->cpu,
                    'ram'     => $ad->computer->ram_gb . ' Go RAM',
                    'storage' => $ad->computer->formatted_storage,
                ] : null,
                'show_url'     => route('pc.show', $ad),
            ];
        })->values();

        return response()->json([
            'success'    => true,
            'items'      => $items,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    // ── Formulaire de création ────────────────────────────────

    public function create(): View
    {
        $this->authorizePcAccess();

        return view('pc.create');
    }

    // ── Publication de l'annonce PC ───────────────────────────

    public function store(StorePcAdRequest $request): RedirectResponse
    {
        $this->authorizePcAccess();

        DB::beginTransaction();

        /** @var Ad|null $ad */
        $ad = null;

        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // 1. Vendeur
            $sellerData = $request->input('seller');

            $seller = Seller::updateOrCreate(
                ['user_id' => $user->id, 'email' => $sellerData['email']],
                [
                    'pseudo' => $sellerData['pseudo'],
                    'phone'  => $sellerData['phone'],
                    'city'   => $sellerData['city'],
                ]
            );

            // 2. Compte bancaire
            $bankData = $request->input('bank');
            $cleanedIban = preg_replace('/\s+/', '', (string) $bankData['iban']);

            $existingBank = $seller->bankAccounts()->where('iban', $cleanedIban)->first();

            if (!$existingBank) {
                $seller->bankAccounts()->create([
                    'iban'                => $cleanedIban,
                    'bic'                 => strtoupper((string) $bankData['bic']),
                    'bank_name'           => $bankData['bank_name'] ?? null,
                    'account_holder_name' => $bankData['account_holder_name'],
                    'is_default'          => true,
                ]);
            }

            // 3. Annonce
            $adData = $request->input('ad');

            $ad = Ad::create([
                'seller_id'    => $seller->id,
                'category'     => 'pc',
                'title'        => $adData['title'],
                'description'  => $adData['description'] ?? null,
                'price'        => $adData['price'],
                'city'         => $adData['city'],
                'postal_code'  => $adData['postal_code'] ?? null,
                'status'       => 'active',
                'published_at' => $adData['published_at'] ?? now(),
                'share_token'  => Str::random(10),
            ]);

            // 4. Ordinateur
            $computerData = $request->input('computer');
            Computer::create(array_merge($computerData, ['ad_id' => $ad->id]));

            // 5. Équipements
            if ($request->filled('features')) {
                AdFeature::syncForAd($ad->id, $request->input('features'));
            }

            // 6. Photos
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $file) {
                    $filename  = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    // "annonces" et non "ads" : ce dernier mot dans l'URL est
                    // bloqué par les bloqueurs de publicités (ERR_BLOCKED_BY_CLIENT).
                    $directory = 'annonces/' . $ad->id . '/photos';
                    $path      = $file->storeAs($directory, $filename, 'public');

                    AdPhoto::create([
                        'ad_id'         => $ad->id,
                        'disk'          => 'public',
                        'path'          => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type'     => $file->getMimeType(),
                        'size'          => $file->getSize(),
                        'order'         => $index,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('pc.show', $ad)
                ->with('success', 'Votre annonce PC a été publiée avec succès !');

        } catch (\Throwable $e) {
            DB::rollBack();

            if ($ad instanceof Ad) {
                AdPhoto::deleteAllForAd($ad->id);
                $ad->delete();
            }

            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    // ── Détail d'une annonce PC ────────────────────────────────

    public function show(Ad $ad): View
    {
        $this->authorizePcAd($ad);
        $ad->load(['seller', 'computer', 'photos', 'features']);

        return view('pc.show', compact('ad'));
    }

    // ── Générer le lien public ─────────────────────────────────

    public function share(Ad $ad): RedirectResponse
    {
        $this->authorizePcAd($ad);

        if (!$ad->share_token) {
            $ad->update(['share_token' => Str::random(10)]);
        }

        $publicUrl = route('ads.public.appareil', ['c' => $ad->share_token]);

        return redirect()->route('pc.show', $ad)->with('share_url', $publicUrl);
    }

    // ── Formulaire de modification ────────────────────────────

    public function edit(Ad $ad): View
    {
        $this->authorizePcAd($ad);
        $ad->load(['computer', 'photos', 'features', 'seller.bankAccounts']);

        return view('pc.edit', compact('ad'));
    }

    // ── Enregistrement des modifications ──────────────────────

    public function update(UpdatePcAdRequest $request, Ad $ad): RedirectResponse
    {
        $this->authorizePcAd($ad);

        DB::beginTransaction();

        try {
            $adData = $request->input('ad', []);

            $ad->update([
                'title'        => $adData['title'],
                'description'  => $adData['description'] ?? null,
                'price'        => $adData['price'],
                'city'         => $adData['city'],
                'postal_code'  => $adData['postal_code'] ?? null,
                'status'       => $adData['status'] ?? $ad->status,
                'published_at' => $adData['published_at'] ?? $ad->published_at,
            ]);

            if ($ad->computer) {
                $ad->computer->update($request->input('computer', []));
            }

            if ($ad->seller) {
                $ad->seller->update($request->input('seller', []));
            }

            $bankData = $request->input('bank', []);
            if (!empty($bankData['iban']) && $ad->seller) {
                $cleanedIban = preg_replace('/\s+/', '', (string) ($bankData['iban'] ?? ''));
                $bankAccount = $ad->seller->bankAccounts()->where('is_default', true)->first()
                    ?? $ad->seller->bankAccounts()->first();

                if ($bankAccount) {
                    $bankAccount->update([
                        'iban'                 => $cleanedIban,
                        'bic'                  => strtoupper((string) ($bankData['bic'] ?? '')),
                        'bank_name'            => $bankData['bank_name'] ?? null,
                        'account_holder_name'  => $bankData['account_holder_name'] ?? null,
                        'transfer_reference'    => $bankData['transfer_reference'] ?? null,
                    ]);
                } else {
                    $ad->seller->bankAccounts()->create([
                        'iban'                 => $cleanedIban,
                        'bic'                  => strtoupper((string) ($bankData['bic'] ?? '')),
                        'bank_name'            => $bankData['bank_name'] ?? null,
                        'account_holder_name'  => $bankData['account_holder_name'] ?? null,
                        'transfer_reference'    => $bankData['transfer_reference'] ?? null,
                        'is_default'           => true,
                    ]);
                }
            }

            AdFeature::syncForAd($ad->id, $request->input('features', []));

            if ($request->hasFile('photos')) {
                $existingCount = $ad->photos()->count();

                foreach ($request->file('photos') as $index => $file) {
                    if ($existingCount + $index >= 12) {
                        break;
                    }

                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('annonces/' . $ad->id . '/photos', $filename, 'public');

                    AdPhoto::create([
                        'ad_id'         => $ad->id,
                        'disk'          => 'public',
                        'path'          => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type'     => $file->getMimeType(),
                        'size'          => $file->getSize(),
                        'order'         => $existingCount + $index,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('pc.show', $ad)
                ->with('success', 'Annonce PC mise à jour avec succès.');
        } catch (\Throwable $e) {
            DB::rollBack();

            \Illuminate\Support\Facades\Log::error('PcAdController::update failed', [
                'ad_id'   => $ad->id,
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }
}
