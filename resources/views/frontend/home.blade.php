@extends('frontend.layouts.master')

@section('content')
    <!-- Hero news Secion-->
    @include('frontend.home-components.hero-slider')

    <!-- End Hero news Section-->

    <div class="container">
        <div class="newspaper-download-banner">
            <div class="newspaper-download-text">
                <i class="fa fa-newspaper"></i>
                <div>
                    <strong>Le journal du jour</strong>
                    <p>Retrouvez l'édition complète en PDF, mise en page comme un vrai journal.</p>
                </div>
            </div>
            <a href="{{ route('newspaper.download') }}" class="newspaper-download-btn">
                <i class="fa fa-download"></i> Télécharger le journal
            </a>
        </div>
    </div>

    <style>
        .newspaper-download-banner {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;
            background: #fff3e6; border: 1px solid #f7d9b8; border-radius: 10px;
            padding: 16px 20px; margin: 20px 0;
        }
        .newspaper-download-text { display: flex; align-items: center; gap: 14px; }
        .newspaper-download-text i { font-size: 1.8rem; color: var(--colorPrimary); }
        .newspaper-download-text strong { font-size: 1rem; color: #1a1a1a; }
        .newspaper-download-text p { margin: 2px 0 0; font-size: 0.85rem; color: #6c757d; }
        .newspaper-download-btn {
            background: var(--colorPrimary); color: #fff; font-weight: 700; padding: 10px 20px;
            border-radius: 8px; text-decoration: none; white-space: nowrap; transition: transform .15s;
        }
        .newspaper-download-btn:hover { color: #fff; text-decoration: none; transform: translateY(-1px); }
    </style>

    <!-- Popular news category -->
    @include('frontend.home-components.main-news')
    <!-- End Popular news category -->
@endsection
