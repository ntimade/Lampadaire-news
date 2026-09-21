@extends('admin.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Veille éditoriale</h1>
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h4>Articles collectés depuis les flux RSS</h4>
                <div class="card-header-action">
                    <form action="{{ route('admin.veille.collect-now') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sync"></i> Relancer la veille maintenant
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body">
                <p class="text-muted">
                    Ces articles viennent de sources externes (RSS) et ne sont <strong>jamais publiés automatiquement</strong>.
                    Convertissez-en un en brouillon pour le réécrire et le publier normalement, ou écartez-le s'il ne convient pas.
                    La veille tourne aussi automatiquement toutes les 30 minutes.
                </p>

                <ul class="nav nav-pills mb-3">
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'new' ? 'active' : '' }}" href="{{ route('admin.veille.index', ['status' => 'new']) }}">
                            À relire <span class="badge badge-light">{{ $counts['new'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'converted' ? 'active' : '' }}" href="{{ route('admin.veille.index', ['status' => 'converted']) }}">
                            Convertis <span class="badge badge-light">{{ $counts['converted'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'dismissed' ? 'active' : '' }}" href="{{ route('admin.veille.index', ['status' => 'dismissed']) }}">
                            Écartés <span class="badge badge-light">{{ $counts['dismissed'] }}</span>
                        </a>
                    </li>
                </ul>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Rubrique</th>
                                <th>Titre</th>
                                <th>Résumé</th>
                                <th>Source</th>
                                <th>Publié le</th>
                                @if ($status === 'new')
                                    <th>Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($collectedArticles as $item)
                                <tr>
                                    <td>
                                        @if ($item->image)
                                            <img src="{{ asset($item->image) }}" alt="" style="width:70px;height:45px;object-fit:cover;border-radius:4px;">
                                        @else
                                            <span class="badge badge-light">aucune</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->category->name ?? '—' }}</td>
                                    <td>{{ $item->title }}</td>
                                    <td>{{ \Str::limit($item->summary, 90) }}</td>
                                    <td><a href="{{ $item->source_url }}" target="_blank" rel="noopener noreferrer">{{ $item->source_name }}</a></td>
                                    <td>{{ $item->published_at?->format('d/m/Y H:i') }}</td>
                                    @if ($status === 'new')
                                        <td class="text-nowrap">
                                            <form action="{{ route('admin.veille.convert', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-pen"></i> Convertir en brouillon
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.veille.dismiss', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Rien ici pour le moment.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $collectedArticles->links() }}
            </div>
        </div>
    </section>
@endsection
