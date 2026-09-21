@extends('admin.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Urgent</h1>
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h4>Rédiger un message Urgent</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.urgent-news.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="text">Texte</label>
                        <input name="text" type="text" class="form-control" id="text" maxlength="255"
                            placeholder="Ex : Le gouvernement annonce..." value="{{ old('text') }}">
                        @error('text')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="link">Lien (optionnel)</label>
                        <input name="link" type="text" class="form-control" id="link"
                            placeholder="https://... (laisser vide pour un simple texte sans lien)" value="{{ old('link') }}">
                        @error('link')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="control-label">Mettre en ligne</div>
                        <label class="custom-switch mt-2">
                            <input value="1" type="checkbox" name="is_published" class="custom-switch-input">
                            <span class="custom-switch-indicator"></span>
                        </label>
                        <small class="text-muted d-block">Coché, ce message défile immédiatement dans la bande Urgent du site. Décoché, il reste en brouillon.</small>
                    </div>

                    <button type="submit" class="btn btn-primary">{{ __('admin.Create') }}</button>
                </form>
            </div>
        </div>
    </section>
@endsection
