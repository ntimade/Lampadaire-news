@extends('admin.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ __('admin.Advertisement') }}</h1>
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h4>Toutes les publicités</h4>
                <div class="card-header-action">
                    <a href="{{ route('admin.advertisement.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('admin.Create new') }}
                    </a>
                </div>
            </div>

            <div class="card-body">
                @foreach ($placements as $key => $label)
                    @php
                        $items = $advertisements->where('placement', $key);
                    @endphp
                    <h5 class="mt-2 mb-3">{{ $label }} <span class="badge badge-light">{{ $items->count() }}</span></h5>

                    <div class="table-responsive mb-4">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Titre</th>
                                    <th>Description</th>
                                    <th>Lien</th>
                                    <th>Ordre</th>
                                    <th>{{ __('admin.Status') }}</th>
                                    <th>{{ __('admin.Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td><img src="{{ asset($item->image) }}" alt="" style="width:70px;height:45px;object-fit:cover;border-radius:4px;"></td>
                                        <td>{{ $item->title }}</td>
                                        <td>{{ \Str::limit($item->description, 40) }}</td>
                                        <td><a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer">{{ \Str::limit($item->url, 30) }}</a></td>
                                        <td>{{ $item->sort_order }}</td>
                                        <td>
                                            @if ($item->status == 1)
                                                <span class="badge badge-success">{{ __('admin.Active') }}</span>
                                            @else
                                                <span class="badge badge-danger">{{ __('admin.Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.advertisement.edit', $item->id) }}" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                                            <a href="{{ route('admin.advertisement.destroy', $item->id) }}" class="btn btn-danger delete-item"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Aucune publicité pour cet emplacement</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
