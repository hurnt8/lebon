<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Modifier — {{ $ad->title }}</title>
    <link rel="stylesheet" href="{{ asset('css/lebon.css') }}"/>
    <style>
        .form-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 32px;
            max-width: 760px;
            margin-bottom: 24px;
        }

        .form-section-title {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 640px) { .grid-2 { grid-template-columns: 1fr; } }

        .form-group { margin-bottom: 18px; }
        .col-2 { grid-column: span 2; }
        @media (max-width: 640px) { .col-2 { grid-column: span 1; } }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: .3px;
            margin-bottom: 8px;
        }

        .form-label .req { color: var(--orange); margin-left: 2px; }

        .form-control, select.form-control {
            width: 100%;
            height: 46px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 0 16px;
            font-size: 14px;
            color: var(--text);
            background: var(--bg);
            outline: none;
            transition: all .15s;
            box-sizing: border-box;
        }

        textarea.form-control { height: auto; padding: 12px 16px; resize: vertical; }

        .form-control:focus { border-color: var(--orange); box-shadow: 0 0 0 3px rgba(229,90,0,.1); }
        .form-control.is-error { border-color: var(--red); }

        .field-error { font-size: 12px; color: var(--red); margin-top: 5px; }

        .photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }

        .photo-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            aspect-ratio: 4/3;
            background: var(--bg);
            border: 1.5px solid var(--border);
        }

        .photo-item img { width: 100%; height: 100%; object-fit: cover; display: block; }

        .photo-del {
            position: absolute; top: 6px; right: 6px;
            width: 26px; height: 26px; border-radius: 50%;
            background: rgba(0,0,0,.6);
            border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
        }
        .photo-del svg { width: 13px; height: 13px; stroke: #fff; stroke-width: 2.5; fill: none; }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }

        .btn-submit {
            flex: 1;
            height: 48px;
            background: var(--orange);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-submit:hover { background: var(--orange-dark); }

        .btn-cancel {
            height: 48px;
            padding: 0 24px;
            background: var(--bg);
            color: var(--text);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
<div class="app">

    @include('partials.sidebar')

    <div class="main">
        <div class="topbar">
            <div class="breadcrumb">
                <a href="{{ route('pc.show', $ad) }}" style="color:var(--muted);text-decoration:none;">{{ $ad->title }}</a>
                <span>›</span>
                <strong>Modifier</strong>
            </div>
            <div class="user-menu">
                <div class="user-avatar" onclick="openLogoutModal()">
                    {{ strtoupper(substr(auth()->user()->name ?? 'V', 0, 1)) }}
                </div>
            </div>
        </div>

        <div class="content">

            @if(session('error'))
                <div class="flash error">{{ session('error') }}</div>
            @endif

            <div style="margin-bottom:24px;">
                <h1 class="section-title">Modifier l'annonce PC</h1>
            </div>

            <form method="POST" action="{{ route('pc.update', $ad) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Annonce --}}
                <div class="form-card">
                    <div class="form-section-title">Annonce</div>
                    <div class="grid-2">
                        <div class="form-group col-2">
                            <label class="form-label">Titre <span class="req">*</span></label>
                            <input type="text" name="ad[title]" value="{{ old('ad.title', $ad->title) }}" class="form-control @error('ad.title') is-error @enderror">
                            @error('ad.title')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-2">
                            <label class="form-label">Description</label>
                            <textarea name="ad[description]" rows="4" class="form-control">{{ old('ad.description', $ad->description) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Prix (€) <span class="req">*</span></label>
                            <input type="number" step="1" name="ad[price]" value="{{ old('ad.price', (float) $ad->price) }}" class="form-control @error('ad.price') is-error @enderror">
                            @error('ad.price')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Statut</label>
                            <select name="ad[status]" class="form-control">
                                <option value="active" @selected(old('ad.status', $ad->status) === 'active')>Active</option>
                                <option value="paused" @selected(old('ad.status', $ad->status) === 'paused')>En pause</option>
                                <option value="sold" @selected(old('ad.status', $ad->status) === 'sold')>Vendue</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ville <span class="req">*</span></label>
                            <input type="text" name="ad[city]" value="{{ old('ad.city', $ad->city) }}" class="form-control @error('ad.city') is-error @enderror">
                            @error('ad.city')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Code postal</label>
                            <input type="text" name="ad[postal_code]" value="{{ old('ad.postal_code', $ad->postal_code) }}" class="form-control">
                        </div>
                    </div>
                </div>

                {{-- Ordinateur --}}
                <div class="form-card">
                    <div class="form-section-title">Caractéristiques</div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Marque <span class="req">*</span></label>
                            <input type="text" name="computer[brand]" value="{{ old('computer.brand', $ad->computer->brand ?? '') }}" class="form-control @error('computer.brand') is-error @enderror">
                            @error('computer.brand')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Modèle <span class="req">*</span></label>
                            <input type="text" name="computer[model]" value="{{ old('computer.model', $ad->computer->model ?? '') }}" class="form-control @error('computer.model') is-error @enderror">
                            @error('computer.model')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Processeur <span class="req">*</span></label>
                            <input type="text" name="computer[cpu]" value="{{ old('computer.cpu', $ad->computer->cpu ?? '') }}" class="form-control @error('computer.cpu') is-error @enderror">
                            @error('computer.cpu')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">RAM (Go) <span class="req">*</span></label>
                            <input type="number" name="computer[ram_gb]" value="{{ old('computer.ram_gb', $ad->computer->ram_gb ?? '') }}" class="form-control @error('computer.ram_gb') is-error @enderror">
                            @error('computer.ram_gb')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Type de stockage <span class="req">*</span></label>
                            <select name="computer[storage_type]" class="form-control @error('computer.storage_type') is-error @enderror">
                                <option value="ssd" @selected(old('computer.storage_type', $ad->computer->storage_type ?? '') === 'ssd')>SSD</option>
                                <option value="hdd" @selected(old('computer.storage_type', $ad->computer->storage_type ?? '') === 'hdd')>HDD</option>
                            </select>
                            @error('computer.storage_type')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Capacité (Go) <span class="req">*</span></label>
                            <input type="number" name="computer[storage_gb]" value="{{ old('computer.storage_gb', $ad->computer->storage_gb ?? '') }}" class="form-control @error('computer.storage_gb') is-error @enderror">
                            @error('computer.storage_gb')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Carte graphique</label>
                            <input type="text" name="computer[gpu]" value="{{ old('computer.gpu', $ad->computer->gpu ?? '') }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Taille écran (pouces)</label>
                            <input type="number" step="0.1" name="computer[screen_size]" value="{{ old('computer.screen_size', $ad->computer->screen_size ?? '') }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Système d'exploitation</label>
                            <input type="text" name="computer[os]" value="{{ old('computer.os', $ad->computer->os ?? '') }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">État</label>
                            <input type="text" name="computer[condition]" value="{{ old('computer.condition', $ad->computer->condition ?? '') }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Couleur</label>
                            <input type="text" name="computer[color]" value="{{ old('computer.color', $ad->computer->color ?? '') }}" class="form-control">
                        </div>
                    </div>
                </div>

                {{-- Équipements --}}
                <div class="form-card">
                    <div class="form-section-title">Équipements</div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:10px;">
                        @php $currentFeatures = old('features', $ad->features->pluck('label')->toArray()); @endphp
                        @foreach(['Wifi', 'Bluetooth', 'Écran tactile', 'Rétroéclairage clavier', 'Lecteur d\'empreintes', 'Webcam', 'Port USB-C', 'Garantie constructeur'] as $feature)
                            <label style="display:flex;align-items:center;gap:8px;border:1.5px solid var(--border);border-radius:10px;padding:10px 12px;font-size:13px;cursor:pointer;">
                                <input type="checkbox" name="features[]" value="{{ $feature }}" @checked(in_array($feature, $currentFeatures))>
                                {{ $feature }}
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Photos --}}
                <div class="form-card">
                    <div class="form-section-title">Photos ({{ $ad->photos->count() }} / 12)</div>

                    @if($ad->photos->isNotEmpty())
                        <div class="photos-grid" id="photosGrid">
                            @foreach($ad->photos as $photo)
                                <div class="photo-item" id="photo-{{ $photo->id }}">
                                    <img src="{{ $photo->url }}" alt="{{ $ad->title }}">
                                    <button type="button" class="photo-del" onclick="deletePhoto({{ $photo->id }}, this)" title="Supprimer">
                                        <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($ad->photos->count() < 12)
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Ajouter des photos</label>
                            <input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" class="form-control @error('photos') is-error @enderror" style="padding:10px 16px;height:auto;">
                            @error('photos')<div class="field-error">{{ $message }}</div>@enderror
                            @error('photos.*')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                    @endif
                </div>

                <div class="form-actions">
                    <a href="{{ route('pc.show', $ad) }}" class="btn-cancel">Annuler</a>
                    <button type="submit" class="btn-submit">Enregistrer les modifications</button>
                </div>

            </form>

        </div>
    </div>
</div>

<div class="modal" id="logoutModal">
    <div class="modal-content">
        <div class="modal-icon danger">
            <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
        </div>
        <div class="modal-title">Déconnexion</div>
        <div class="modal-text">Êtes-vous sûr de vouloir vous déconnecter ?</div>
        <div class="modal-buttons">
            <button class="modal-btn modal-btn-cancel" onclick="closeLogoutModal()">Annuler</button>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="modal-btn modal-btn-confirm">Se déconnecter</button>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('js/lebon.js') }}"></script>
<script>
const csrf = '{{ csrf_token() }}';

async function deletePhoto(photoId, btn) {
    if (!confirm('Supprimer cette photo ?')) return;
    btn.disabled = true;
    try {
        const resp = await fetch('{{ route("ads.photos.destroy", [$ad, ":photo"]) }}'.replace(':photo', photoId), {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        const data = await resp.json();
        if (data.success) {
            const el = document.getElementById('photo-' + photoId);
            el.remove();
        }
    } catch (e) {
        console.error(e);
        btn.disabled = false;
    }
}
</script>
</body>
</html>
