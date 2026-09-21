<!DOCTYPE html>
<html lang="">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>@hasSection('title') @yield('title') @else {{ $settings['site_seo_title'] }} @endif </title>
    <meta name="description" content="@hasSection('meta_description') @yield('meta_description') @else {{ $settings['site_seo_description'] }} @endif " />
    <meta name="keywords" content="{{ $settings['site_seo_keywords'] }}" />

    <meta name="og:title" content="@yield('meta_og_title')" />
    <meta name="og:description" content="@yield('meta_og_description')" />
    <meta name="og:image" content="@hasSection('meta_og_image') @yield('meta_og_image') @else {{ asset($settings['site_logo']) }} @endif" />
    <meta name="twitter:title" content="@yield('meta_tw_title')" />
    <meta name="twitter:description" content="@yield('meta_tw_description')" />
    <meta name="twitter:image" content="@yield('meta_tw_image')" />

    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if (!empty($settings['site_favicon']))
        <link rel="icon" href="{{ asset($settings['site_favicon']) }}" type="image/png">
    @else
        <link rel="icon" href="{{ asset('frontend/assets/images/favicon.svg') }}" type="image/svg+xml">
    @endif
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">
    <link href="{{ asset_v('frontend/assets/css/styles.css') }}" rel="stylesheet">
    <link href="{{ asset_v('frontend/assets/css/custom.css') }}" rel="stylesheet">
    <style>
        :root {
            --colorPrimary: {{ $settings['site_color'] }};
        }
        .site-brand-lockup { display: inline-flex; align-items: center; gap: 10px; text-decoration: none; }
        .site-brand-lockup:hover { text-decoration: none; }
        .site-brand-lockup-icon {
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .site-brand-lockup-icon svg { width: 56px; height: 56px; }
        .site-brand-lockup-icon img { height: 78px; width: auto; display: block; }
        .site-brand-lockup-name { font-weight: 800; font-size: 1.25rem; color: #1a1a1a; white-space: nowrap; }
    </style>
</head>

<body>

    <!-- Global Variables -->
    @php
        $socialLinks = \App\Models\SocialLink::where('status', 1)->get();
        $footerInfo = \App\Models\FooterInfo::where('language', getLangauge())->first();
        $footerGridOne = \App\Models\FooterGridOne::where(['status' => 1, 'language' => getLangauge()])->get();
        $footerGridTwo = \App\Models\FooterGridTwo::where(['status' => 1, 'language' => getLangauge()])->get();
        $footerGridThree = \App\Models\FooterGridThree::where(['status' => 1, 'language' => getLangauge()])->get();
        $footerGridOneTitle = \App\Models\FooterTitle::where(['key' => 'grid_one_title', 'language' => getLangauge()])->first();
        $footerGridTwoTitle = \App\Models\FooterTitle::where(['key' => 'grid_two_title', 'language' => getLangauge()])->first();
        $footerGridThreeTitle = \App\Models\FooterTitle::where(['key' => 'grid_three_title', 'language' => getLangauge()])->first();
    @endphp

    <!-- Header news -->
    @include('frontend.layouts.header')
    <!-- End Header news -->

    <div class="page-body-with-sticky-alert">
        @yield('content')

        <div class="breaking-bar-sticky-wrap">
            @include('frontend.home-components.breaking-news')
        </div>
    </div>

    <!-- Footer Section -->
    @include('frontend.layouts.footer')
    <!-- End Footer Section -->

    <style>
        /* Fixed, not sticky: on pages with a taller sidebar column (news
           listing, article detail) than the main column, `position: sticky`
           starts "sticking" to the viewport bottom well before the reader
           has scrolled past the sidebar — visually cutting across whatever
           card/photo happens to be there at the time. `position: fixed`
           always sits at the same spot regardless of column heights, and
           the matching padding-bottom on the wrapper keeps it from ever
           covering the last bit of real content or the footer. */
        .page-body-with-sticky-alert {
            position: relative;
            padding-bottom: 40px;
        }
        .breaking-bar-sticky-wrap {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            /* Bootstrap's own .sticky-top utility (used on the article-page
               and news-listing sidebars) hardcodes z-index: 1020 — comfortably
               above the 60 this bar had before, so whenever a sticky sidebar's
               content reached screen-bottom it painted its own text right
               through the bar instead of staying underneath it. This must
               beat every such sidebar without exception. */
            z-index: 2000;
        }
    </style>


    <a href="javascript:" id="return-to-top"><i class="fa fa-chevron-up"></i></a>

    <script type="text/javascript" src="{{ asset_v('frontend/assets/js/index.bundle.js') }}"></script>
    @include('sweetalert::alert')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        })


        // Add csrf token in ajax request
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            /** change language **/
            $('#site-language').on('change', function() {
                let languageCode = $(this).val();
                $.ajax({
                    method: 'GET',
                    url: "{{ route('language') }}",
                    data: {
                        language_code: languageCode
                    },
                    success: function(data) {
                        if (data.status === 'success') {
                            window.location.href = "{{ url('/') }}";
                        }
                    },
                    error: function(data) {
                        console.error(data);
                    }
                })
            })

            /** Subscribe Newsletter**/
            $('.newsletter-form').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    method: 'POST',
                    url: "{{ route('subscribe-newsletter') }}",
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $('.newsletter-button').text('loading...');
                        $('.newsletter-button').attr('disabled', true);
                    },
                    success: function(data) {
                        if (data.status === 'success') {
                            Toast.fire({
                                icon: 'success',
                                title: data.message
                            })
                            $('.newsletter-form')[0].reset();
                            $('.newsletter-button').text('sign up');

                            $('.newsletter-button').attr('disabled', false);
                        }
                    },
                    error: function(data) {
                        $('.newsletter-button').text('sign up');
                        $('.newsletter-button').attr('disabled', false);

                        if (data.status === 422) {
                            let errors = data.responseJSON.errors;
                            $.each(errors, function(index, value) {
                                Toast.fire({
                                    icon: 'error',
                                    title: value[0]
                                })
                            })
                        }
                    }
                })
            })
        })
    </script>

    <script>
        function initAdPeekCarousels() {
            $('.ad-peek-carousel').each(function () {
                var $carousel = $(this);
                if ($carousel.hasClass('slick-initialized')) {
                    return;
                }
                var slideCount = $carousel.children().length;

                $carousel.slick({
                    centerMode: false,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    variableWidth: false,
                    arrows: slideCount > 1,
                    dots: slideCount > 1,
                    autoplay: slideCount > 1,
                    autoplaySpeed: 5000,
                    infinite: slideCount > 1,
                    adaptiveHeight: true,
                    prevArrow: "<button type='button' class='slick-prev pull-left'><i class='fa fa-angle-left' aria-hidden='true'></i></button>",
                    nextArrow: "<button type='button' class='slick-next pull-right'><i class='fa fa-angle-right' aria-hidden='true'></i></button>",
                });
            });
        }

        // Wait for window "load" (not just DOM ready) so images/columns have their
        // final width before Slick measures the container — otherwise centerMode's
        // slide offset is computed against a stale width and the "centered" slide
        // ends up rendered outside its own container.
        $(window).on('load', initAdPeekCarousels);
        setTimeout(function () {
            $('.ad-peek-carousel').each(function () {
                if ($(this).hasClass('slick-initialized')) {
                    $(this).slick('setPosition');
                }
            });
        }, 800);

        function initHeroFeatureCarousel() {
            $('.hero-editorial__feature-carousel').each(function () {
                var $carousel = $(this);
                if ($carousel.hasClass('slick-initialized')) {
                    return;
                }
                var slideCount = $carousel.children().length;

                $carousel.slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    fade: true,
                    arrows: slideCount > 1,
                    dots: slideCount > 1,
                    autoplay: slideCount > 1,
                    autoplaySpeed: 6000,
                    infinite: slideCount > 1,
                    adaptiveHeight: false,
                    prevArrow: "<button type='button' class='slick-prev'><i class='fa fa-angle-left' aria-hidden='true'></i></button>",
                    nextArrow: "<button type='button' class='slick-next'><i class='fa fa-angle-right' aria-hidden='true'></i></button>",
                });
            });
        }
        $(window).on('load', initHeroFeatureCarousel);
        setTimeout(function () {
            $('.hero-editorial__feature-carousel').each(function () {
                if ($(this).hasClass('slick-initialized')) {
                    $(this).slick('setPosition');
                }
            });
        }, 800);
    </script>

    @stack('content')

</body>

</html>
