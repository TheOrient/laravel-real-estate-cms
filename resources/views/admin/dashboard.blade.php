@extends('admin.layouts.app')

@section('title', __('admin/dashboard.title'))

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('admin/general.dashboard') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin/dashboard.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin/dashboard.dashboard') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('admin/dashboard.total_consultants') }}</span>
                        <span class="info-box-number">{{ number_format($totalUsers) }}</span>
                        <span class="info-box-text text-sm">
                            <span class="text-success">
                                <i class="fas fa-arrow-up"></i> {{ $newUsers }}
                            </span>
                            {{ __('admin/dashboard.new_in_last_7_days') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-tag"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('admin/dashboard.total_listings') }}</span>
                        <span class="info-box-number">{{ number_format($totalListings) }}</span>
                        <span class="info-box-text text-sm">
                            <span class="text-success">
                                <i class="fas fa-arrow-up"></i> {{ $newListings }}
                            </span>
                            {{ __('admin/dashboard.new_in_last_7_days') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-folder"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('admin/dashboard.categories') }}</span>
                        <span class="info-box-number">{{ number_format($totalCategories) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-eye"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('admin/dashboard.active_listings') }}</span>
                        <span class="info-box-number">{{ number_format($listingsByStatus['active'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second row of info boxes -->
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-newspaper"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('admin/dashboard.published_blogs') }}</span>
                        <span class="info-box-number">{{ number_format($totalBlogs) }}</span>
                        <a href="{{ route('admin.blogs.index') }}" class="info-box-more">{{ __('admin/dashboard.manage_content') }}</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-file-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('admin/dashboard.active_pages') }}</span>
                        <span class="info-box-number">{{ number_format($totalPages) }}</span>
                        <a href="{{ route('admin.pages.index') }}" class="info-box-more">{{ __('admin/dashboard.manage_content') }}</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-envelope"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('admin/dashboard.unread_messages') }}</span>
                        <span class="info-box-number">{{ number_format($unreadMessages) }}</span>
                        <a href="{{ route('admin.contact-messages.index') }}" class="info-box-more">{{ __('admin/dashboard.view_messages') }}</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-star"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('admin/dashboard.featured_listings') }}</span>
                        <span class="info-box-number">{{ number_format($featuredListings) }}</span>
                        <a href="{{ route('admin.listings.index') }}" class="info-box-more">{{ __('admin/dashboard.manage_portfolio') }}</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <!-- RECENT LISTINGS -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('admin/dashboard.recent_listings') }}</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table m-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('admin/dashboard.id') }}</th>
                                        <th>{{ __('admin/dashboard.listing_title') }}</th>
                                        <th>{{ __('admin/dashboard.category') }}</th>
                                        <th>{{ __('admin/dashboard.user') }}</th>
                                        <th>{{ __('admin/dashboard.status') }}</th>
                                        <th>{{ __('admin/dashboard.created') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentListings as $listing)
                                    <tr>
                                        <td>{{ $listing->id }}</td>
                                        <td>
                                            <a href="{{ route('listings.show', $listing->slug) }}" target="_blank">
                                                {{ Str::limit($listing->title, 40) }}
                                            </a>
                                        </td>
                                        <td>{{ $listing->category->name }}</td>
                                        <td>{{ $listing->user?->isAgent() ? $listing->user->name : 'Real Estate CMS Demo' }}</td>
                                        <td>
                                            @if($listing->status == 'active')
                                                <span class="badge badge-success">{{ __('admin/dashboard.status_active') }}</span>
                                            @elseif($listing->status == 'pending')
                                                <span class="badge badge-warning">{{ __('admin/dashboard.status_pending') }}</span>
                                            @elseif($listing->status == 'sold')
                                                <span class="badge badge-info">{{ __('admin/dashboard.status_sold') }}</span>
                                            @elseif($listing->status == 'expired')
                                                <span class="badge badge-danger">{{ __('admin/dashboard.status_expired') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $listing->created_at->diffForHumans() }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer clearfix">
                        <a href="{{ route('admin.listings.index') }}" class="btn btn-sm btn-primary float-right">{{ __('admin/dashboard.view_all_listings') }}</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- TOP CATEGORIES -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('admin/dashboard.top_categories') }}</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="products-list product-list-in-card pl-2 pr-2">
                            @foreach($topCategories as $category)
                            <li class="item">
                                <div class="product-info">
                                    <a href="{{ route('categories.show', $category->slug) }}" class="product-title" target="_blank">
                                        {{ $category->name }}
                                        <span class="badge badge-info float-right">{{ number_format($category->listings_count) }}</span>
                                    </a>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-footer text-center">
                        <a href="{{ route('admin.categories.index') }}" class="uppercase">{{ __('admin/dashboard.view_all_categories') }}</a>
                    </div>
                </div>

                <!-- RECENT CONTACT MESSAGES -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('admin/dashboard.recent_messages') }}</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($recentMessages->count() > 0)
                        <ul class="products-list product-list-in-card pl-2 pr-2">
                            @foreach($recentMessages as $message)
                            <li class="item">
                                <div class="product-info">
                                    <a href="{{ route('admin.contact-messages.show', $message) }}" class="product-title">
                                        {{ Str::limit($message->name, 28) }}
                                        @if(is_null($message->read_at))
                                            <span class="badge badge-primary float-right">{{ __('admin/dashboard.message_new') }}</span>
                                        @endif
                                    </a>
                                    <span class="product-description">
                                        {{ Str::limit($message->subject ?: $message->message, 48) }} · {{ $message->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <div class="text-center p-4">
                            <i class="fas fa-envelope-open-text fa-2x text-muted mb-2"></i>
                            <p class="text-muted">{{ __('admin/dashboard.no_messages_yet') }}</p>
                        </div>
                        @endif
                    </div>
                    <div class="card-footer text-center">
                        <a href="{{ route('admin.contact-messages.index') }}" class="uppercase">{{ __('admin/dashboard.view_all_messages') }}</a>
                    </div>
                </div>

                <!-- LISTINGS BY STATUS -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('admin/dashboard.listings_by_status') }}</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-responsive">
                            <div class="chart" id="listings-status-chart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
@endpush

@push('scripts')
<!-- Chart JS -->
<script src="{{ asset('assets/adminlte.3.2.0/plugins/chart.js/Chart.min.js') }}"></script>

<script>
    // Listings Status Chart
    document.addEventListener('DOMContentLoaded', function() {
        const statuses = {
            'active': '{{ $listingsByStatus["active"] ?? 0 }}',
            'pending': '{{ $listingsByStatus["pending"] ?? 0 }}',
            'sold': '{{ $listingsByStatus["sold"] ?? 0 }}',
            'expired': '{{ $listingsByStatus["expired"] ?? 0 }}'
        };

        new Chart(document.getElementById('listings-status-chart'), {
            type: 'pie',
            data: {
                labels: [
                    '{{ __('admin/dashboard.status_active') }}',
                    '{{ __('admin/dashboard.status_pending') }}',
                    '{{ __('admin/dashboard.status_sold') }}',
                    '{{ __('admin/dashboard.status_expired') }}'
                ],
                datasets: [{
                    data: [
                        statuses.active,
                        statuses.pending,
                        statuses.sold,
                        statuses.expired
                    ],
                    backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
</script>
@endpush
