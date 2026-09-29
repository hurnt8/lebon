<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Annonces Appareils photo - Espace Vendeur</title>
    <link rel="stylesheet" href="{{ asset('css/lebon.css') }}"/>
    <style>
        .ads-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .ad-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
        }

        .ad-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }

        .ad-image {
            position: relative;
            aspect-ratio: 16/10;
            background: linear-gradient(135deg, #f0efea, #e8e7e2);
            overflow: hidden;
        }

        .ad-image img { width: 100%; height: 100%; object-fit: cover; }

        .ad-badge {
            position: absolute;
            top: 12px; left: 12px;
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .5px;
            background: rgba(26,122,74,.9);
            color: #fff;
        }

        .ad-delete-btn {
            position: absolute;
            top: 10px; right: 10px;
            width: 30px; height: 30px;
            border-radius: 50%;
            border: none;
            background: rgba(0,0,0,.55);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 2;
            transition: background .15s;
        }

        .ad-delete-btn:hover { background: var(--red); }
        .ad-delete-btn svg { width: 15px; height: 15px; }

        .ad-content { padding: 16px; }

        .ad-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .ad-price { font-size: 20px; font-weight: 800; color: var(--orange); margin-bottom: 12px; }

        .ad-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }

        .ad-meta-item {
            font-size: 11px;
            padding: 4px 10px;
            background: var(--bg);
            border-radius: 6px;
            color: var(--text-light);
        }

        .ad-footer {
            display: flex;
            justify-content: space-between;
            padding-top: 12px;
            border-top: 1px solid var(--border);
            font-size: 11px;
            color: var(--muted);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
        }

        .empty-icon {
            width: 80px; height: 80px;
            background: var(--orange-lt);
            border-radius: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .empty-icon svg { width: 36px; height: 36px; stroke: var(--orange); stroke-width: 1.5; }

        .empty-title { font-size: 22px; font-weight: 700; color: var(--text); margin-bottom: 8px; }
        .empty-text { font-size: 14px; color: var(--muted); margin-bottom: 24px; }

        .ad-skeleton {
            height: 320px;
            border-radius: var(--radius-lg);
            background: linear-gradient(90deg, #eeece6 25%, #f5f4f0 37%, #eeece6 63%);
            background-size: 400% 100%;
            animation: skeleton-loading 1.4s ease infinite;
        }

        @keyframes skeleton-loading {
            0% { background-position: 100% 50%; }
            100% { background-position: 0 50%; }
        }
    </style>
</head>
<body>
<div class="app">

    @include('partials.sidebar')

    <div class="main">
        <div class="topbar">
            <div class="breadcrumb">
                <strong>Annonces Appareils photo</strong>
            </div>
            <div class="user-menu">
                <a href="{{ route('camera.create') }}" class="btn-primary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16M4 12h16"/></svg>
                    Nouvelle annonce
                </a>
                <div class="user-avatar" onclick="openLogoutModal()">
                    {{ strtoupper(substr(auth()->user()->name ?? 'V', 0, 1)) }}
                </div>
            </div>
        </div>

        <div class="content">

            @if(session('success'))
                <div class="flash success">{{ session('success') }}</div>
            @endif

            <div style="margin-bottom:24px;">
                <h1 class="section-title">Annonces Appareils photo</h1>
                <p class="section-subtitle">Espace dédié à la vente d'appareils photo — <span id="cameraTotalCount">{{ $ads->total() }}</span> annonce(s)</p>
            </div>

            <div class="ads-grid" id="adsGrid" aria-live="polite">
                <div class="ad-skeleton"></div>
                <div class="ad-skeleton"></div>
                <div class="ad-skeleton"></div>
            </div>

            <div class="empty-state" id="emptyState" style="display:none;">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                        <circle cx="12" cy="13" r="4"/>
                    </svg>
                </div>
                <div class="empty-title">Aucune annonce appareil photo pour le moment</div>
                <div class="empty-text">Publiez votre première annonce d'appareil photo</div>
                <a href="{{ route('camera.create') }}" class="btn-primary">Créer une annonce</a>
            </div>

            <div class="empty-state" id="errorState" style="display:none;">
                <div class="empty-icon" style="background:var(--red-light);">
                    <svg fill="none" stroke="var(--red)" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <div class="empty-title">Impossible de charger vos annonces</div>
                <div class="empty-text">Une erreur est survenue lors du chargement. Veuillez réessayer.</div>
                <button type="button" class="btn-primary" id="retryBtn" style="border:none;">Réessayer</button>
            </div>

            <div class="pagination" id="paginationContainer" style="margin-top:24px;"></div>

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
<script src="{{ asset('js/axios.min.js') }}"></script>
<script>
    // ========== Chargement des annonces Appareils photo via axios ==========
    const CAMERA_DATA_URL = '{{ route('camera.index.data') }}';

    const adsGrid = document.getElementById('adsGrid');
    const emptyState = document.getElementById('emptyState');
    const errorState = document.getElementById('errorState');
    const paginationContainer = document.getElementById('paginationContainer');
    const cameraTotalCount = document.getElementById('cameraTotalCount');

    let currentPage = 1;

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function cameraCardHTML(item) {
        const photoHtml = item.photo_url
            ? `<img src="${item.photo_url}" alt="${escapeHtml(item.title)}" loading="lazy">`
            : `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                    <svg width="32" height="32" fill="none" stroke="var(--muted)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                </div>`;

        const cameraHtml = item.camera ? `
            <div class="ad-meta">
                ${item.camera.brand ? `<span class="ad-meta-item">${escapeHtml(item.camera.brand)}</span>` : ''}
                ${item.camera.type ? `<span class="ad-meta-item">${escapeHtml(item.camera.type)}</span>` : ''}
                ${item.camera.condition ? `<span class="ad-meta-item">${escapeHtml(item.camera.condition)}</span>` : ''}
            </div>` : '';

        return `
            <a href="${item.show_url}" class="ad-card">
                <div class="ad-image">
                    ${photoHtml}
                    <div class="ad-badge">${item.status === 'active' ? 'Active' : escapeHtml(item.status)}</div>
                    <button type="button" class="ad-delete-btn" data-delete-id="${item.id}" title="Supprimer l'annonce">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                    </button>
                </div>
                <div class="ad-content">
                    <div class="ad-title">${escapeHtml(item.title)}</div>
                    <div class="ad-price">${escapeHtml(item.price)}</div>
                    ${cameraHtml}
                    <div class="ad-footer">
                        <span>${escapeHtml(item.city)}</span>
                        <span>${escapeHtml(item.published_at)}</span>
                    </div>
                </div>
            </a>`;
    }

    async function deleteAd(id) {
        if (!confirm('Supprimer définitivement cette annonce ? Cette action est irréversible.')) {
            return;
        }

        try {
            await axios.delete(`/d6t1z/${id}`, {
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            });
            loadAds(currentPage);
        } catch (error) {
            console.error('Erreur lors de la suppression de l\'annonce :', error);
            alert('Impossible de supprimer cette annonce. Veuillez réessayer.');
        }
    }

    adsGrid.addEventListener('click', (event) => {
        const btn = event.target.closest('.ad-delete-btn');
        if (!btn) return;
        event.preventDefault();
        event.stopPropagation();
        deleteAd(btn.dataset.deleteId);
    });

    function renderAds(items) {
        if (!items.length) {
            adsGrid.style.display = 'none';
            paginationContainer.innerHTML = '';
            emptyState.style.display = '';
            return;
        }

        emptyState.style.display = 'none';
        adsGrid.style.display = '';
        adsGrid.innerHTML = items.map(cameraCardHTML).join('');
    }

    function renderPagination(pagination) {
        if (pagination.last_page <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let html = '';
        for (let page = 1; page <= pagination.last_page; page++) {
            html += `<button type="button" class="page-link ${page === pagination.current_page ? 'active' : ''}" data-page="${page}">${page}</button>`;
        }
        paginationContainer.innerHTML = html;

        paginationContainer.querySelectorAll('.page-link').forEach(btn => {
            btn.addEventListener('click', () => {
                loadAds(parseInt(btn.dataset.page, 10));
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    }

    async function loadAds(page = 1) {
        errorState.style.display = 'none';
        emptyState.style.display = 'none';
        adsGrid.style.display = '';
        adsGrid.innerHTML = '<div class="ad-skeleton"></div><div class="ad-skeleton"></div><div class="ad-skeleton"></div>';

        try {
            const { data } = await axios.get(CAMERA_DATA_URL, { params: { page } });
            currentPage = data.pagination.current_page;
            if (cameraTotalCount) cameraTotalCount.textContent = data.pagination.total;
            renderAds(data.items);
            renderPagination(data.pagination);
        } catch (error) {
            console.error('Erreur de chargement des annonces Appareils photo :', error);
            adsGrid.style.display = 'none';
            paginationContainer.innerHTML = '';
            errorState.style.display = '';
        }
    }

    document.getElementById('retryBtn').addEventListener('click', () => loadAds(currentPage));

    loadAds();
</script>
</body>
</html>
