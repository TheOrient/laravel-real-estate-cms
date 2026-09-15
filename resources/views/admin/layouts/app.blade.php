<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Brand-aware tab title — settings first, config fallback, generic last. --}}
    <title>@yield('title') · {{ trim((string) get_setting('site_title', config('app.name', 'CMS'))) ?: 'CMS' }} {{ __('admin/general.admin_panel') }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('assets/adminlte.3.2.0/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('assets/adminlte.3.2.0/dist/css/adminlte.min.css') }}">

    <!-- Mobile-friendly pagination styles -->
    <style>
        /* Mobile pagination - hide page numbers, show only prev/next */
        @media (max-width: 768px) {
            .pagination {
                font-size: 0.875rem;
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            /* Hide page numbers on mobile, keep only prev/next */
            .pagination .page-item:not(.disabled):not(.active):not(:first-child):not(:last-child) {
                display: none;
            }

            /* Show active page */
            .pagination .page-item.active {
                display: inline-block;
            }

            /* Make buttons smaller on mobile */
            .pagination .page-link {
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
            }
        }
    </style>
    <style>
          /* Ensure alerts display properly */
          .alert {
            position: relative !important;
            padding: 0.75rem 1.25rem !important;
            margin-bottom: 1rem !important;
            border: 1px solid transparent !important;
            border-radius: 0.25rem !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
    </style>
    @stack('styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">{{ __('admin/general.dashboard') }}</a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('home') }}" class="nav-link"
                        target="_blank">{{ __('admin/general.visit_site') }}</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                {{-- Admin language switcher — same locale machinery as
                     the frontend, scoped to the admin layout so editors
                     can review translations without leaving the panel. --}}
                @php
                    $adminLanguages = \App\Helpers\LanguageHelper::generateLanguageLinks();
                @endphp
                @if($adminLanguages->count() > 1)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
                            <i class="fas fa-globe mr-1"></i>
                            {{ strtoupper(app()->getLocale()) }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            @foreach($adminLanguages as $lng)
                                <a class="dropdown-item {{ app()->getLocale() === $lng->code ? 'active' : '' }}"
                                   href="{{ $lng->url }}">
                                    {{ $lng->title }} <small class="text-muted">({{ strtoupper($lng->code) }})</small>
                                </a>
                            @endforeach
                        </div>
                    </li>
                @endif

                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <i class="far fa-user mr-2"></i>
                        <span>{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <!-- User image -->
                        <li class="user-header bg-primary">
                            <img src="{{ Auth::user()->avatar_large_url }}"
                                class="img-circle elevation-2" alt="{{ Auth::user()->name }}">
                            <p>
                                {{ Auth::user()->name }}
                                <small>{{ __('admin/general.member_since') }}
                                    {{ Auth::user()->created_at->format('M. Y') }}</small>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <a href="{{ route('user.profile') }}" class="btn btn-default btn-flat">{{ __('admin/general.profile') }}</a>
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit"
                                    class="btn btn-default btn-flat float-right">{{ __('admin/general.sign_out') }}</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            {{-- Brand Logo — brand-aware. Pulls site_title from settings
                 so the admin matches the public-facing site instead of
                 a generic APP_NAME env value. Initials are derived from
                 the same title and the logo color uses brand green. --}}
            @php
                $adminBrandTitle = trim((string) get_setting('site_title', config('app.name', 'CMS'))) ?: 'CMS';
                $adminBrandInitial = strtoupper(mb_substr($adminBrandTitle, 0, 2));
                $adminLogoPath = get_setting('site_logo');
                $adminLogoPath = $adminLogoPath ? ltrim(\Illuminate\Support\Str::after($adminLogoPath, 'public/'), '/') : null;
            @endphp
            <a href="{{ route('admin.dashboard') }}" class="brand-link">
                @if($adminLogoPath)
                    <img src="{{ asset($adminLogoPath) }}"
                         alt="{{ $adminBrandTitle }}"
                         class="brand-image img-circle elevation-3" style="opacity: .95; object-fit: cover; background: #fff;">
                @else
                    <span class="brand-image img-circle elevation-3 d-inline-flex align-items-center justify-content-center font-weight-bold text-white"
                          style="width: 34px; height: 34px; margin-top: -2px; background: #1E6F5C;">{{ $adminBrandInitial }}</span>
                @endif
                <span class="brand-text font-weight-light">{{ $adminBrandTitle }}</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="{{ route('admin.dashboard') }}"
                                class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>{{ __('admin/general.menu.dashboard') }}</p>
                            </a>
                        </li>


                        <!-- Categories -->
                        <li class="nav-item {{ request()->routeIs('admin.categories.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-folder"></i>
                                <p>
                                    {{ __('admin/general.menu.categories') }}
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.categories.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.categories.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('admin/general.menu.all_categories') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.categories.create') }}"
                                        class="nav-link {{ request()->routeIs('admin.categories.create') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('admin/general.menu.add_category') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Languages -->
                        <li class="nav-item">
                            <a href="{{ route('admin.languages.index') }}"
                               class="nav-link {{ request()->routeIs('admin.languages.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-language"></i>
                                <p>{{ __('admin/languages.languages') }}</p>
                            </a>
                        </li>

                        <!-- Listings -->
                        <li class="nav-item {{ request()->routeIs('admin.listings.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('admin.listings.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tag"></i>
                                <p>
                                    {{ __('admin/general.menu.listings') }}
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.listings.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.listings.index') && !request()->query('approval_status') && !request()->query('active_status') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('admin/listings.all_listings') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.listings.index') }}?approval_status=approved"
                                        class="nav-link {{ request()->routeIs('admin.listings.index') && request()->query('approval_status') == 'approved' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('admin/listings.approved_listings') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.listings.index') }}?approval_status=pending"
                                        class="nav-link {{ request()->routeIs('admin.listings.index') && request()->query('approval_status') == 'pending' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('admin/listings.pending_listings') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.listings.index') }}?active_status=active"
                                        class="nav-link {{ request()->routeIs('admin.listings.index') && request()->query('active_status') == 'active' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('admin/listings.active_listings') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.listings.index') }}?active_status=inactive"
                                        class="nav-link {{ request()->routeIs('admin.listings.index') && request()->query('active_status') == 'inactive' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('admin/listings.inactive_listings') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Attributes -->
                        <li
                            class="nav-item {{ request()->routeIs('admin.attributes.*') || request()->routeIs('admin.category.attributes') ? 'menu-open' : '' }}">
                            <a href="#"
                                class="nav-link {{ request()->routeIs('admin.attributes.*') || request()->routeIs('admin.category.attributes') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-sliders-h"></i>
                                <p>
                                    {{ __('admin/general.menu.attributes') }}
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.attributes.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.attributes.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('admin/general.menu.all_attributes') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.attributes.create') }}"
                                        class="nav-link {{ request()->routeIs('admin.attributes.create') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Özellik Ekle</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.category.attributes') }}"
                                        class="nav-link {{ request()->routeIs('admin.category.attributes') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('admin/general.menu.category_attributes') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Users -->
                        <li class="nav-item {{ request()->routeIs('admin.users.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>
                                    {{ __('admin/general.menu.users') }}
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.users.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('admin/general.menu.all_users') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.users.create') }}"
                                        class="nav-link {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('admin/users.create_agent') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>




                        <!-- Locations -->
                        <li class="nav-item {{ request()->routeIs('admin.locations.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-map-marker-alt"></i>
                                <p>
                                    Konum Yönetimi
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.locations.cities.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.locations.cities.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Şehirler</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.locations.districts.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.locations.districts.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>İlçeler</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.locations.neighborhoods.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.locations.neighborhoods.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Mahalleler</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Pages -->
                        <li class="nav-item">
                            <a href="{{ route('admin.pages.index') }}"
                                class="nav-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>{{ __('admin/pages.pages') }}</p>
                            </a>
                        </li>

                        <!-- Blog -->
                        <li class="nav-item {{ request()->routeIs('admin.blogs.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-newspaper"></i>
                                <p>
                                    {{ __('admin/blog.blog') }}
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.blogs.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.blogs.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('admin/blog.all_posts') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.blogs.create') }}"
                                        class="nav-link {{ request()->routeIs('admin.blogs.create') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('admin/blog.new_post') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- FAQ -->
                        <li class="nav-item {{ request()->routeIs('admin.faq*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('admin.faq*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-question-circle"></i>
                                <p>
                                    FAQ Yönetimi
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.faqs.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Tüm FAQ'lar</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.faqs.create') }}"
                                        class="nav-link {{ request()->routeIs('admin.faqs.create') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Yeni FAQ</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.faq-categories.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.faq-categories.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>FAQ Kategorileri</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Contact Messages -->
                        <li class="nav-item {{ request()->routeIs('admin.contact-messages.*') ? 'menu-open' : '' }}">
                            <a href="#"
                                class="nav-link {{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-envelope"></i>
                                <p>
                                    {{ __('admin/contact_messages.contact_messages') }}
                                    @php
                                        $newMessagesCount = \App\Models\ContactMessage::where('status', 'new')->count();
                                    @endphp
                                    @if($newMessagesCount > 0)
                                        <span class="badge badge-danger right">{{ $newMessagesCount }}</span>
                                    @endif
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.contact-messages.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.contact-messages.index') && !request()->query('status') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('admin/contact_messages.all_messages') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.contact-messages.index') }}?status=new"
                                        class="nav-link {{ request()->routeIs('admin.contact-messages.index') && request()->query('status') == 'new' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>
                                            {{ __('admin/contact_messages.new_messages') }}
                                            @if($newMessagesCount > 0)
                                                <span class="badge badge-danger right">{{ $newMessagesCount }}</span>
                                            @endif
                                        </p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.contact-messages.index') }}?status=read"
                                        class="nav-link {{ request()->routeIs('admin.contact-messages.index') && request()->query('status') == 'read' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('admin/contact_messages.read_messages') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.contact-messages.index') }}?status=replied"
                                        class="nav-link {{ request()->routeIs('admin.contact-messages.index') && request()->query('status') == 'replied' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('admin/contact_messages.replied_messages') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.contact-messages.index') }}?status=archived"
                                        class="nav-link {{ request()->routeIs('admin.contact-messages.index') && request()->query('status') == 'archived' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('admin/contact_messages.archived_messages') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Settings -->
                        <li class="nav-item">
                            <a href="{{ route('admin.settings.index') }}"
                                class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-cog"></i>
                                <p>Site Ayarları</p>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            @yield('content_header')

            <!-- Flash Messages -->
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-check"></i> {{ __('admin/general.success') }}</h5>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-ban"></i> {{ __('admin/general.error') }}</h5>
                        {{ session('error') }}
                    </div>
                @endif

                <!-- validation errors -->
                @if(session('errors'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-ban"></i> {{ __('admin/general.error') }}</h5>
                        @foreach(session('errors')->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif


            </div>

            <!-- Main content -->
            <section class="content">
                @yield('content')
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <!-- Main Footer -->
        <footer class="main-footer">
            <strong>{{ __('admin/general.copyright') }} &copy; {{ date('Y') }} <a
                    href="{{ route('home') }}">{{ $adminBrandTitle }}</a>.</strong>
            {{ __('admin/general.all_rights_reserved') }}
            <div class="float-right d-none d-sm-inline-block">
                <b>{{ __('admin/general.version') }}</b> 1.0.0
            </div>
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="{{ asset('assets/adminlte.3.2.0/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('assets/adminlte.3.2.0/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('assets/adminlte.3.2.0/dist/js/adminlte.min.js') }}"></script>

    @stack('scripts')

    <!-- Admin shell interactions -->
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Simple sidebar toggle - bypass AdminLTE's complex logic
            $('[data-widget="pushmenu"]').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var body = $('body');

                // Toggle sidebar state
                if (body.hasClass('sidebar-collapse')) {
                    body.removeClass('sidebar-collapse');
                    body.addClass('sidebar-open');
                } else {
                    body.removeClass('sidebar-open');
                    body.addClass('sidebar-collapse');
                }

                return false;
            });

        });
    </script>
</body>

</html>
