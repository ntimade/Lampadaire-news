@extends('admin.layouts.master')

@section('content')
<section class="section">

    <div class="dash-welcome">
        <div>
            <h1 class="dash-welcome-title">{{ __('admin.Dashboard') }}</h1>
            <p class="dash-welcome-sub">
                {{ \Carbon\Carbon::now()->locale(app()->getLocale())->translatedFormat('l j F Y') }}
            </p>
        </div>
        <div class="dash-welcome-actions">
            <a href="{{ route('admin.news.create') }}" class="btn-dash-primary"><i class="fas fa-plus mr-1"></i> Nouvel article</a>
        </div>
    </div>

    <div class="dash-quick-links">
        <a href="{{ route('admin.news.create') }}" class="dash-quick-link">
            <span class="dash-quick-icon bg-primary"><i class="fas fa-pen"></i></span>
            <span>Rédiger un article</span>
        </a>
        <a href="{{ route('admin.pending.news') }}" class="dash-quick-link">
            <span class="dash-quick-icon bg-warning"><i class="fas fa-hourglass-half"></i></span>
            <span>Articles en attente</span>
        </a>
        <a href="{{ route('admin.category.index') }}" class="dash-quick-link">
            <span class="dash-quick-icon bg-success"><i class="fas fa-tags"></i></span>
            <span>Gérer les catégories</span>
        </a>
        <a href="{{ route('admin.contact-message.index') }}" class="dash-quick-link">
            <span class="dash-quick-icon bg-info"><i class="fas fa-envelope"></i></span>
            <span>Messages de contact</span>
        </a>
        <a href="{{ route('admin.setting.index') }}" class="dash-quick-link">
            <span class="dash-quick-icon bg-secondary"><i class="fas fa-cog"></i></span>
            <span>Paramètres du site</span>
        </a>
    </div>

    <div class="dash-section-label">Contenu éditorial</div>
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>{{ __('admin.Total News') }}</h4>
                    </div>
                    <div class="card-body">
                        {{ $publishedNews }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <a href="{{ route('admin.pending.news') }}" class="text-decoration-none">
                <div class="card card-statistic-1 {{ $pendingNews > 0 ? 'card-statistic-alert' : '' }}">
                    <div class="card-icon bg-danger">
                        <i class="far fa-newspaper"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ __('admin.Pending News') }}</h4>
                        </div>
                        <div class="card-body">
                            {{ $pendingNews }}
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning">
                    <i class="far fa-file"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>{{ __('admin.Total Categories') }}</h4>
                    </div>
                    <div class="card-body">
                        {{ $Categories }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Vues cumulées</h4>
                    </div>
                    <div class="card-body">
                        {{ convertToKFormat((int) $totalViews) }}
                    </div>
                </div>
            </div>
        </div>
        @if (canAccess(['news all-access']))
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <a href="{{ route('admin.veille.index') }}" class="text-decoration-none">
                    <div class="card card-statistic-1 {{ $collectedNewCount > 0 ? 'card-statistic-alert' : '' }}">
                        <div class="card-icon bg-info">
                            <i class="fas fa-rss"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Veille — à relire</h4>
                            </div>
                            <div class="card-body">
                                {{ $collectedNewCount }}
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-lg-6 col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Articles en attente de validation</h4>
                    <a href="{{ route('admin.pending.news') }}" class="text-primary small">Tout voir</a>
                </div>
                <div class="card-body p-0">
                    @forelse ($pendingNewsList as $item)
                        <div class="dash-list-row">
                            <img src="{{ asset($item->image) }}" alt="" class="dash-list-thumb">
                            <div class="dash-list-body">
                                <a href="{{ route('admin.news.edit', $item->id) }}" class="dash-list-title">{{ $item->title }}</a>
                                <div class="dash-list-meta">
                                    <span class="badge badge-light">{{ $item->category->name ?? '—' }}</span>
                                    <span>{{ $item->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <span class="badge badge-warning">En attente</span>
                        </div>
                    @empty
                        <div class="dash-empty">
                            <i class="fas fa-check-circle"></i>
                            <p>Aucun article en attente de validation.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Derniers articles</h4>
                    <a href="{{ route('admin.news.index') }}" class="text-primary small">Tout voir</a>
                </div>
                <div class="card-body p-0">
                    @forelse ($recentNewsList as $item)
                        <div class="dash-list-row">
                            <img src="{{ asset($item->image) }}" alt="" class="dash-list-thumb">
                            <div class="dash-list-body">
                                <a href="{{ route('admin.news.edit', $item->id) }}" class="dash-list-title">{{ $item->title }}</a>
                                <div class="dash-list-meta">
                                    <span class="badge badge-light">{{ $item->category->name ?? '—' }}</span>
                                    <span>{{ $item->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            @if ($item->status == 1 && $item->is_approved == 1)
                                <span class="badge badge-success">Publié</span>
                            @else
                                <span class="badge badge-secondary">Brouillon</span>
                            @endif
                        </div>
                    @empty
                        <div class="dash-empty">
                            <i class="fas fa-pen"></i>
                            <p>Aucun article pour l'instant &mdash; <a href="{{ route('admin.news.create') }}">créez le premier</a>.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="dash-section-label">Système</div>
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1 card-statistic-sm">
                <div class="card-icon bg-info"><i class="fas fa-language"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>{{ __('admin.Total Languages') }}</h4></div>
                    <div class="card-body">{{ $languages }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1 card-statistic-sm">
                <div class="card-icon bg-primary"><i class="fas fa-user-shield"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>{{ __('admin.Total Roles') }}</h4></div>
                    <div class="card-body">{{ $roles }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1 card-statistic-sm">
                <div class="card-icon bg-secondary"><i class="fas fa-hashtag"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>{{ __('admin.Total Socials') }}</h4></div>
                    <div class="card-body">{{ $socials }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1 card-statistic-sm">
                <div class="card-icon bg-warning"><i class="fas fa-envelope-open"></i></div>
                <div class="card-wrap">
                    <div class="card-header"><h4>{{ __('admin.Total Subscribers') }}</h4></div>
                    <div class="card-body">{{ $subscribers }}</div>
                </div>
            </div>
        </div>
    </div>

</section>

<style>
    .dash-welcome {
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
        background: linear-gradient(135deg, #2b1400 0%, #6e2f00 45%, #f0740b 100%);
        border-radius: 14px; padding: 26px 28px; margin-bottom: 22px; color: #fff;
    }
    .dash-welcome-title { font-size: 1.4rem; font-weight: 800; margin-bottom: 4px; }
    .dash-welcome-sub { margin-bottom: 0; opacity: 0.85; font-size: 0.9rem; }
    .btn-dash-primary {
        background: #fff; color: #f0740b; font-weight: 700; padding: 10px 18px;
        border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center;
        transition: transform .15s;
    }
    .btn-dash-primary:hover { transform: translateY(-1px); color: #f0740b; text-decoration: none; }

    .dash-quick-links { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 26px; }
    .dash-quick-link {
        display: flex; align-items: center; gap: 10px; background: #fff; border: 1px solid #eef0f4;
        border-radius: 10px; padding: 12px 16px; text-decoration: none; color: #333; font-size: 0.88rem;
        font-weight: 600; box-shadow: 0 2px 6px rgba(0,0,0,0.03); transition: box-shadow .15s, transform .15s;
    }
    .dash-quick-link:hover { box-shadow: 0 6px 16px rgba(0,0,0,0.08); transform: translateY(-1px); color: #333; text-decoration: none; }
    .dash-quick-icon {
        width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.85rem;
    }

    .dash-section-label {
        text-transform: uppercase; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px;
        color: #9aa0ac; margin: 8px 0 12px;
    }

    .card-statistic-sm .card-body { font-size: 1.4rem; }
    .card-statistic-alert { border-left: 3px solid #dc3545; }

    .dash-list-row { display: flex; align-items: center; gap: 12px; padding: 12px 18px; border-bottom: 1px solid #f1f1f4; }
    .dash-list-row:last-child { border-bottom: none; }
    .dash-list-thumb { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; background: #f1f1f4; flex-shrink: 0; }
    .dash-list-body { flex: 1; min-width: 0; }
    .dash-list-title {
        display: block; font-weight: 700; font-size: 0.88rem; color: #333; text-decoration: none;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .dash-list-title:hover { color: #f0740b; }
    .dash-list-meta { font-size: 0.76rem; color: #9aa0ac; display: flex; gap: 8px; align-items: center; margin-top: 2px; }

    .dash-empty { text-align: center; padding: 36px 20px; color: #b0b4bd; }
    .dash-empty i { font-size: 1.8rem; margin-bottom: 8px; display: block; }
    .dash-empty p { margin-bottom: 0; font-size: 0.88rem; }
</style>
@endsection
