<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Nouvelle annonce Appareil photo - Espace Vendeur</title>
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

        .form-hint { font-size: 12px; color: var(--muted); margin-top: 6px; }

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
                <a href="{{ route('camera.index') }}" style="color:var(--muted);text-decoration:none;">Annonces Appareils photo</a>
                <span>›</span>
                <strong>Nouvelle annonce</strong>
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
                <h1 class="section-title">Nouvelle annonce Appareil photo</h1>
                <p class="section-subtitle">Renseignez les informations de votre appareil photo.</p>
            </div>

            <form method="POST" action="{{ route('camera.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Caractéristiques appareil photo --}}
                <div class="form-card">
                    <div class="form-section-title">Caractéristiques</div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">État</label>
                            <input type="text" name="camera[condition]" value="{{ old('camera.condition') }}" class="form-control" placeholder="Très bon état">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Couleur</label>
                            <input type="text" name="camera[color]" value="{{ old('camera.color') }}" class="form-control" placeholder="Noir">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Univers</label>
                            <input type="text" name="camera[universe]" value="{{ old('camera.universe') }}" class="form-control" placeholder="Appareil photo">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Produit</label>
                            <input type="text" name="camera[product]" value="{{ old('camera.product') }}" class="form-control" placeholder="Appareil photos">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Type</label>
                            <input type="text" name="camera[type]" value="{{ old('camera.type') }}" class="form-control" placeholder="Appareil photo hybride">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Marque <span class="req">*</span></label>
                            <input type="text" name="camera[brand]" value="{{ old('camera.brand') }}" class="form-control @error('camera.brand') is-error @enderror" placeholder="Sony">
                            @error('camera.brand')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Annonce --}}
                <div class="form-card">
                    <div class="form-section-title">Annonce</div>
                    <div class="grid-2">
                        <div class="form-group col-2">
                            <label class="form-label">Titre Annonce <span class="req">*</span></label>
                            <input type="text" name="ad[title]" value="{{ old('ad.title') }}" class="form-control @error('ad.title') is-error @enderror" placeholder="Sony Alpha 7 IV (A7 IV)">
                            @error('ad.title')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-2">
                            <label class="form-label">Description</label>
                            <textarea name="ad[description]" rows="4" class="form-control" placeholder="Je vends mon Sony Alpha....">{{ old('ad.description') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Prix (€) <span class="req">*</span></label>
                            <input type="number" step="1" name="ad[price]" value="{{ old('ad.price') }}" class="form-control @error('ad.price') is-error @enderror" placeholder="1500">
                            @error('ad.price')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date de publication</label>
                            <input type="text" class="form-control" value="{{ now()->translatedFormat('d/m/Y à H:i') }}" disabled>
                            <div class="form-hint">Définie automatiquement à la publication</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Région</label>
                            <input type="text" name="ad[region]" value="{{ old('ad.region') }}" class="form-control" placeholder="Ile-de-France">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Départements</label>
                            <input type="text" name="ad[department]" value="{{ old('ad.department') }}" class="form-control" placeholder="Val-de-Marne">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ville <span class="req">*</span></label>
                            <input type="text" name="ad[city]" value="{{ old('ad.city') }}" class="form-control @error('ad.city') is-error @enderror" placeholder="Thiais">
                            @error('ad.city')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Code postal</label>
                            <input type="text" name="ad[postal_code]" value="{{ old('ad.postal_code') }}" class="form-control" placeholder="94320">
                        </div>
                    </div>
                </div>

                {{-- Photos --}}
                <div class="form-card">
                    <div class="form-section-title">Photos</div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Photos (jusqu'à 3 maximum) <span class="req">*</span></label>
                        <input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" class="form-control @error('photos') is-error @enderror" style="padding:10px 16px;height:auto;">
                        <div class="form-hint">Cliquez pour télécharger une image — Ajouter jusqu'à 3 photos maximum</div>
                        @error('photos')<div class="field-error">{{ $message }}</div>@enderror
                        @error('photos.*')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Vendeur --}}
                <div class="form-card">
                    <div class="form-section-title">Vendeur</div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Pseudo <span class="req">*</span></label>
                            <input type="text" name="seller[pseudo]" value="{{ old('seller.pseudo', auth()->user()->name ?? '') }}" class="form-control @error('seller.pseudo') is-error @enderror" placeholder="Anthony">
                            @error('seller.pseudo')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email <span class="req">*</span></label>
                            <input type="email" name="seller[email]" value="{{ old('seller.email', auth()->user()->email ?? '') }}" class="form-control @error('seller.email') is-error @enderror" placeholder="anthony@email.com">
                            @error('seller.email')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Téléphone <span class="req">*</span></label>
                            <input type="tel" name="seller[phone]" value="{{ old('seller.phone') }}" class="form-control @error('seller.phone') is-error @enderror" placeholder="06 12 34 56 78">
                            @error('seller.phone')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ville vendeur <span class="req">*</span></label>
                            <input type="text" name="seller[city]" value="{{ old('seller.city') }}" class="form-control @error('seller.city') is-error @enderror" placeholder="Thiais">
                            @error('seller.city')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Compte bancaire --}}
                <div class="form-card">
                    <div class="form-section-title">Compte bancaire</div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">IBAN <span class="req">*</span></label>
                            <input type="text" name="bank[iban]" value="{{ old('bank.iban') }}" class="form-control @error('bank.iban') is-error @enderror" placeholder="FR01010101010101010101010">
                            @error('bank.iban')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">BIC <span class="req">*</span></label>
                            <input type="text" name="bank[bic]" value="{{ old('bank.bic') }}" class="form-control @error('bank.bic') is-error @enderror" placeholder="MLBCETRDG">
                            @error('bank.bic')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-2">
                            <label class="form-label">Bénéficiaire <span class="req">*</span></label>
                            <input type="text" name="bank[account_holder_name]" value="{{ old('bank.account_holder_name') }}" class="form-control @error('bank.account_holder_name') is-error @enderror" placeholder="LEBONCOIN">
                            @error('bank.account_holder_name')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-2">
                            <label class="form-label">Référence à indiquer</label>
                            <input type="text" class="form-control" value="Annonce appareil photo" disabled>
                            <div class="form-hint">Référence à indiquer par l'acheteur lors du virement</div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('camera.index') }}" class="btn-cancel">Annuler</a>
                    <button type="submit" class="btn-submit">Publier l'annonce</button>
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
</body>
</html>
