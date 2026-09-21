@extends('admin.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Urgent</h1>
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h4>Bande défilante "Urgent"</h4>
                <div class="card-header-action">
                    <a href="{{ route('admin.urgent-news.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('admin.Create new') }}
                    </a>
                </div>
            </div>

            <div class="card-body">
                <p class="text-muted">
                    Seuls les messages dont "Mettre en ligne" est coché défilent dans la bande Urgent du site.
                    Décoché, un message reste en brouillon ici sans jamais apparaître publiquement.
                </p>

                <div class="table-responsive">
                    <table class="table table-striped" id="table-urgent-news">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th>Texte</th>
                                <th>Lien</th>
                                <th>Rédigé par</th>
                                <th>Mettre en ligne</th>
                                <th>{{ __('admin.Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($urgentNews as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->text }}</td>
                                    <td>{{ $item->link ?? '—' }}</td>
                                    <td>{{ $item->admin->name ?? '—' }}</td>
                                    <td>
                                        <label class="custom-switch mt-2">
                                            <input {{ $item->is_published == 1 ? 'checked' : '' }}
                                                data-id="{{ $item->id }}"
                                                value="1" type="checkbox" class="custom-switch-input toggle-urgent-status">
                                            <span class="custom-switch-indicator"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.urgent-news.edit', $item->id) }}"
                                            class="btn btn-primary"><i class="fas fa-edit"></i></a>
                                        <a href="{{ route('admin.urgent-news.destroy', $item->id) }}"
                                            class="btn btn-danger delete-item"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $("#table-urgent-news").dataTable({
            "columnDefs": [{
                "sortable": false,
                "targets": [4, 5]
            }]
        });

        $(document).ready(function() {
            $('.toggle-urgent-status').on('click', function() {
                let id = $(this).data('id');
                let status = $(this).prop('checked') ? 1 : 0;

                $.ajax({
                    method: 'GET',
                    url: "{{ route('admin.urgent-news.toggle') }}",
                    data: { id: id, status: status },
                    success: function(data) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000
                        });
                        Toast.fire({ icon: 'success', title: data.message });
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });
        });
    </script>
@endpush
