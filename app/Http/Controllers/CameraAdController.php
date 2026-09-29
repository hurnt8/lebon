<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCameraAdRequest;
use App\Http\Requests\UpdateCameraAdRequest;
use App\Models\Ad;
use App\Models\AdPhoto;
use App\Models\Camera;
use App\Models\Seller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CameraAdController extends Controller
{
    private function authorizeCameraAccess(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user->hasPermission('menu.camera.view'), 403);
    }

    private function authorizeCameraAd(Ad $ad): void
    {
        $this->authorizeCameraAccess();
        abort_unless(Auth::user()->can('manage', $ad), 403);
    }

    // ── Liste des annonces appareils photo ────────────────────

    public function index(): View
    {
        $this->authorizeCameraAccess();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $totalAds = Ad::category('camera')
            ->whereHas('seller', fn($q) => $q->where('user_id', $user->id))
            ->count();

        $ads = new \Illuminate\Pagination\LengthAwarePaginator(collect(), $totalAds, 12, 1);

        return view('camera.index', compact('ads'));
    }

    // ── Données JSON des annonces appareils photo (axios) ─────

    public function indexData(Request $request): JsonResponse
    {
        $this->authorizeCameraAccess();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Ad::with(['camera', 'photos'])
            ->category('camera')
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
                'camera'       => $ad->camera ? [
                    'brand'     => $ad->camera->brand,
                    'type'      => $ad->camera->type,
                    'condition' => $ad->camera->condition,
                ] : null,
                'show_url'     => route('camera.show', $ad),
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
        $this->authorizeCameraAccess();

        return view('camera.create');
    }

    // ── Publication de l'annonce appareil photo ───────────────

    public function store(StoreCameraAdRequest $request): RedirectResponse
    {
        $this->authorizeCameraAccess();

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

            // 2. Annonce
            $adData = $request->input('ad');

            $ad = Ad::create([
                'seller_id'    => $seller->id,
                'category'     => 'camera',
                'title'        => $adData['title'],
                'description'  => $adData['description'] ?? null,
                'price'        => $adData['price'],
                'city'         => $adData['city'],
                'region'       => $adData['region'] ?? null,
                'department'   => $adData['department'] ?? null,
                'postal_code'  => $adData['postal_code'] ?? null,
                'status'       => 'active',
                'published_at' => $adData['published_at'] ?? now(),
                'share_token'  => Str::random(10),
            ]);

            // 3. Compte bancaire (un compte dédié par annonce)
            $bankData = $request->input('bank');
            $cleanedIban = preg_replace('/\s+/', '', (string) $bankData['iban']);

            $ad->bankAccount()->create([
                'seller_id'            => $seller->id,
                'iban'                 => $cleanedIban,
                'bic'                  => strtoupper((string) $bankData['bic']),
                'account_holder_name'  => $bankData['account_holder_name'],
                'is_default'           => false,
            ]);

            // 4. Appareil photo
            $cameraData = $request->input('camera');
            Camera::create(array_merge($cameraData, ['ad_id' => $ad->id]));

            // 5. Photos (3 maximum)
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
                ->route('camera.show', $ad)
                ->with('success', 'Votre annonce appareil photo a été publiée avec succès !');

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

    // ── Détail d'une annonce appareil photo ───────────────────

    public function show(Ad $ad): View
    {
        $this->authorizeCameraAd($ad);
        $ad->load(['seller', 'camera', 'photos', 'bankAccount']);

        return view('camera.show', compact('ad'));
    }

    // ── Générer le lien public ─────────────────────────────────

    public function share(Ad $ad): RedirectResponse
    {
        $this->authorizeCameraAd($ad);

        if (!$ad->share_token) {
            $ad->update(['share_token' => Str::random(10)]);
        }

        $publicUrl = route('ads.public.appareil', ['c' => $ad->share_token]);

        return redirect()->route('camera.show', $ad)->with('share_url', $publicUrl);
    }

    // ── Formulaire de modification ────────────────────────────

    public function edit(Ad $ad): View
    {
        $this->authorizeCameraAd($ad);
        $ad->load(['camera', 'photos', 'seller', 'bankAccount']);

        return view('camera.edit', compact('ad'));
    }

    // ── Enregistrement des modifications ──────────────────────

    public function update(UpdateCameraAdRequest $request, Ad $ad): RedirectResponse
    {
        $this->authorizeCameraAd($ad);

        DB::beginTransaction();

        try {
            $adData = $request->input('ad', []);

            $ad->update([
                'title'        => $adData['title'],
                'description'  => $adData['description'] ?? null,
                'price'        => $adData['price'],
                'city'         => $adData['city'],
                'region'       => $adData['region'] ?? null,
                'department'   => $adData['department'] ?? null,
                'postal_code'  => $adData['postal_code'] ?? null,
                'status'       => $adData['status'] ?? $ad->status,
                'published_at' => $adData['published_at'] ?? $ad->published_at,
            ]);

            if ($ad->camera) {
                $ad->camera->update($request->input('camera', []));
            }

            if ($ad->seller) {
                $ad->seller->update($request->input('seller', []));
            }

            $bankData = $request->input('bank', []);
            if (!empty($bankData['iban'])) {
                $cleanedIban = preg_replace('/\s+/', '', (string) ($bankData['iban'] ?? ''));

                if ($ad->bankAccount) {
                    $ad->bankAccount->update([
                        'iban'                => $cleanedIban,
                        'bic'                  => strtoupper((string) ($bankData['bic'] ?? '')),
                        'account_holder_name'  => $bankData['account_holder_name'] ?? null,
                    ]);
                } else {
                    $ad->bankAccount()->create([
                        'seller_id'            => $ad->seller_id,
                        'iban'                 => $cleanedIban,
                        'bic'                  => strtoupper((string) ($bankData['bic'] ?? '')),
                        'account_holder_name'  => $bankData['account_holder_name'] ?? null,
                        'is_default'           => false,
                    ]);
                }
            }

            if ($request->hasFile('photos')) {
                $existingCount = $ad->photos()->count();

                foreach ($request->file('photos') as $index => $file) {
                    if ($existingCount + $index >= 3) {
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
                ->route('camera.show', $ad)
                ->with('success', 'Annonce appareil photo mise à jour avec succès.');
        } catch (\Throwable $e) {
            DB::rollBack();

            \Illuminate\Support\Facades\Log::error('CameraAdController::update failed', [
                'ad_id'   => $ad->id,
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }
}
