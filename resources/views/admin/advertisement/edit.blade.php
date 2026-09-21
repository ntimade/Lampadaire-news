@extends('admin.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ __('admin.Advertisement') }}</h1>
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h4>Modifier la publicité</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.advertisement.update', $advertisement->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Emplacement</label>
                        <select name="placement" class="form-control select2">
                            @foreach ($placements as $key => $label)
                                <option value="{{ $key }}" {{ old('placement', $advertisement->placement) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('placement')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Titre <small class="text-muted">(usage interne, non affiché)</small></label>
                        <input name="title" type="text" class="form-control" value="{{ old('title', $advertisement->title) }}">
                        @error('title')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Description <small class="text-muted">(affichée sous la publicité)</small></label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $advertisement->description) }}</textarea>
                        @error('description')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('admin.Image') }}</label>
                        <div id="image-preview" class="image-preview" style="background-image:url('{{ asset($advertisement->image) }}');background-size:cover;background-position:center center;">
                            <label for="image-upload" id="image-label">{{ __('admin.Choose File') }}</label>
                            <input type="file" name="image" id="image-upload">
                        </div>
                        @error('image')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Lien externe <small class="text-muted">(https://...)</small></label>
                        <input name="url" type="text" class="form-control" value="{{ old('url', $advertisement->url) }}" placeholder="https://exemple.com">
                        @error('url')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Ordre d'affichage</label>
                        <input name="sort_order" type="number" min="0" class="form-control" value="{{ old('sort_order', $advertisement->sort_order) }}">
                    </div>

                    <div class="form-group">
                        <div class="control-label">{{ __('admin.Status') }}</div>
                        <label class="custom-switch mt-2">
                            <input value="1" type="checkbox" name="status" class="custom-switch-input" {{ $advertisement->status == 1 ? 'checked' : '' }}>
                            <span class="custom-switch-indicator"></span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">{{ __('admin.Update') }}</button>
                </form>
            </div>
        </div>
    </section>
@endsection
