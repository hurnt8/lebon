<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $ad->title }} - Espace Vendeur</title>
    <link rel="stylesheet" href="{{ asset('css/lebon.css') }}"/>
    <style>
        .ad-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }

        .ad-header-left h1 {
            font-family: 'DM Serif Display', serif;
            font-size: 28px;
            color: var(--text);
            margin-bottom: 6px;
            line-height: 1.25;
        }

        .ad-meta-line { font-size: 13px; color: var(--muted); }

        .ad-price { font-size: 32px; font-weight: 800; color: var(--orange); white-space: nowrap; }

        .show-grid { display: grid; grid-template-columns: 1fr 320px; gap: 24px; align-items: start; }
        @media (max-width: 900px) { .show-grid { grid-template-columns: 1fr; } }

        .show-col { display: flex; flex-direction: column; gap: 20px; }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 24px;
        }

        .card-title {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 16px;
        }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 10px;
        }

        .photo-grid img {
            width: 100%;
            aspect-ratio: 4/3;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid var(--border);
        }

        .spec-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        @media (max-width: 480px) { .spec-grid { grid-template-columns: 1fr; } }

        .spec-item { font-size: 13px; }
        .spec-label { color: var(--muted); margin-bottom: 2px; }
        .spec-value { font-weight: 700; color: var(--text); }

        .seller-header { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }

        .seller-avatar {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 800; font-size: 16px;
        }

        .seller-name { font-weight: 700; color: var(--text); }
        .seller-city { font-size: 12px; color: var(--muted); }

        .status-active { color: var(--green); font-weight: 700; }
        .status-paused { color: #f5a623; font-weight: 700; }

        .share-link-box {
            background: var(--orange-lt);
            border: 1px solid rgba(229,90,0,.25);
            border-radius: var(--radius-lg);
            padding: 16px 20px;
        }
        .share-link-title { font-size: 12px; font-weight: 800; color: var(--orange); margin-bottom: 10px; }
        .share-link-row { display: flex; gap: 8px; }
        .share-link-input {
            flex: 1;
            height: 38px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 0 12px;
            font-size: 13px;
            background: #fff;
            color: var(--text);
        }

        .action-btn {
            width: 100%;
            height: 42px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
            text-decoration: none;
            margin-bottom: 10px;
            border: 1.5px solid var(--border);
            background: var(--bg);
            color: var(--text);
        }
        .action-btn:hover { border-color: var(--orange); color: var(--orange); }
        .action-btn.pause-btn { border-color: #e55a00; background: #fff0e8; color: #e55a00; }
        .action-btn.reactivate-btn { border-color: #1a7a4a; background: #eaf5ef; color: #1a7a4a; }
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
                <strong>{{ $ad->title }}</strong>
            </div>
            <div class="user-menu">
                <div class="user-avatar" onclick="openLogoutModal()">
                    {{ strtoupper(substr(auth()->user()->name ?? 'V', 0, 1)) }}
                </div>
            </div>
        </div>

        <div class="content" style="padding:32px;">

            @if(session('success'))
                <div class="flash success">{{ session('success') }}</div>
            @endif

            <div class="ad-header">
                <div class="ad-header-left">
                    <h1>{{ $ad->title }}</h1>
                    <div class="ad-meta-line">
                        {{ $ad->city }}
                        @if($ad->postal_code) ({{ $ad->postal_code }}) @endif
                        @if($ad->department) · {{ $ad->department }} @endif
                        @if($ad->region) · {{ $ad->region }} @endif
                        · Publiée {{ $ad->published_at?->diffForHumans() }}
                    </div>
                </div>
                <div class="ad-price">{{ $ad->formatted_price }}</div>
            </div>

            @if(session('share_url'))
                <div class="share-link-box" style="margin-bottom:24px;">
                    <div class="share-link-title">Lien public de partage</div>
                    <div class="share-link-row">
                        <input type="text" readonly id="shareInput" class="share-link-input" value="{{ session('share_url') }}"/>
                        <button id="copyBtn" onclick="copyShare()" class="btn-primary" style="height:38px;padding:0 16px;font-size:12px;">Copier</button>
                    </div>
                </div>
            @endif

            <div class="show-grid">
                <div class="show-col">

                    <div class="card">
                        <div class="card-title">Photos</div>
                        @if($ad->photos->isNotEmpty())
                            <div class="photo-grid">
                                @foreach($ad->photos as $photo)
                                    <img src="{{ $photo->url }}" alt="{{ $ad->title }}">
                                @endforeach
                            </div>
                        @else
                            <p style="color:var(--muted);font-size:13px;">Aucune photo.</p>
                        @endif
                    </div>

                    @if($ad->camera)
                        <div class="card">
                            <div class="card-title">Caractéristiques</div>
                            <div class="spec-grid">
                                <div class="spec-item"><div class="spec-label">Marque</div><div class="spec-value">{{ $ad->camera->brand }}</div></div>
                                @if($ad->camera->type)
                                    <div class="spec-item"><div class="spec-label">Type</div><div class="spec-value">{{ $ad->camera->type }}</div></div>
                                @endif
                                @if($ad->camera->product)
                                    <div class="spec-item"><div class="spec-label">Produit</div><div class="spec-value">{{ $ad->camera->product }}</div></div>
                                @endif
                                @if($ad->camera->universe)
                                    <div class="spec-item"><div class="spec-label">Univers</div><div class="spec-value">{{ $ad->camera->universe }}</div></div>
                                @endif
                                @if($ad->camera->color)
                                    <div class="spec-item"><div class="spec-label">Couleur</div><div class="spec-value">{{ $ad->camera->color }}</div></div>
                                @endif
                                @if($ad->camera->condition)
                                    <div class="spec-item"><div class="spec-label">État</div><div class="spec-value">{{ $ad->camera->condition }}</div></div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($ad->description)
                        <div class="card">
                            <div class="card-title">Description</div>
                            <p style="font-size:14px;color:var(--text);line-height:1.6;">{{ $ad->description }}</p>
                        </div>
                    @endif

                </div>

                <div class="show-col">
                    <div class="card">
                        <div class="seller-header">
                            <div class="seller-avatar">{{ strtoupper(substr($ad->seller->pseudo ?? 'V', 0, 1)) }}</div>
                            <div>
                                <div class="seller-name">{{ $ad->seller->pseudo }}</div>
                                <div class="seller-city">{{ $ad->seller->city }}</div>
                            </div>
                        </div>
                        <div class="card-title" style="margin-bottom:6px;">Statut</div>
                        <div class="status-{{ $ad->status === 'paused' ? 'paused' : 'active' }}">
                            {{ $ad->status === 'active' ? 'Active' : ($ad->status === 'paused' ? 'En pause' : $ad->status) }}
                        </div>
                    </div>

                    <div class="card">
                        @if($ad->status !== 'sold')
                            <form method="POST" action="{{ route('ads.toggle-status', $ad) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="action-btn {{ $ad->status === 'active' ? 'pause-btn' : 'reactivate-btn' }}">
                                    @if($ad->status === 'active')
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                                        Mettre en pause
                                    @else
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                        Réactiver l'annonce
                                    @endif
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('camera.share', $ad) }}" class="action-btn">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                            </svg>
                            Obtenir le lien public
                        </a>

                        <a href="{{ route('camera.edit', $ad) }}" class="action-btn" style="border-color:var(--orange);color:var(--orange);">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                            Modifier l'annonce
                        </a>

                        <a href="{{ route('camera.index') }}" class="action-btn" style="margin-bottom:0;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                            Retour aux annonces
                        </a>
                    </div>

                    @if($ad->bankAccount)
                        <div class="card">
                            <div class="card-title">Compte bancaire</div>
                            <div class="spec-item" style="margin-bottom:10px;"><div class="spec-label">IBAN</div><div class="spec-value">{{ $ad->bankAccount->masked_iban }}</div></div>
                            <div class="spec-item" style="margin-bottom:10px;"><div class="spec-label">BIC</div><div class="spec-value">{{ $ad->bankAccount->bic }}</div></div>
                            <div class="spec-item"><div class="spec-label">Bénéficiaire</div><div class="spec-value">{{ $ad->bankAccount->account_holder_name }}</div></div>
                        </div>
                    @endif
                </div>
            </div>

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
