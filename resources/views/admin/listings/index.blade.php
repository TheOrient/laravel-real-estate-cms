@extends('admin.layouts.app')

@section('title', __('admin/listings.title'))

@section('content_header')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __('admin/listings.manage_listings') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin/general.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('admin/listings.title') }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Search and Filter Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin/listings.search_listings') }}</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.listings.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control"
                                       placeholder="{{ __('admin/listings.search_placeholder') }}"
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="category_id" class="form-control">
                                    <option value="">{{ __('admin/listings.all_categories') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="show_trashed" class="form-control">
                                    <option value="0" {{ !$showTrashed ? 'selected' : '' }}>{{ __('admin/listings.active_listings') }}</option>
                                    <option value="1" {{ $showTrashed ? 'selected' : '' }}>{{ __('admin/listings.trashed_listings') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="approval_status" class="form-control">
                                    <option value="">{{ __('admin/general.all') }}</option>
                                    <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>
                                        {{ __('admin/listings.approved') }}
                                    </option>
                                    <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>
                                        {{ __('admin/listings.pending_approval') }}
                                    </option>
                                    <option value="not_approved" {{ request('approval_status') == 'not_approved' ? 'selected' : '' }}>
                                        {{ __('admin/listings.not_approved') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="active_status" class="form-control">
                                    <option value="">{{ __('admin/general.all') }}</option>
                                    <option value="active" {{ request('active_status') == 'active' ? 'selected' : '' }}>
                                        {{ __('admin/listings.active') }}
                                    </option>
                                    <option value="inactive" {{ request('active_status') == 'inactive' ? 'selected' : '' }}>
                                        {{ __('admin/listings.inactive') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary">{{ __('admin/listings.filter') }}</button>
                            </div>
                        </div>
                        @if(request()->hasAny(['search', 'category_id', 'approval_status', 'active_status']))
                            <div class="row mt-2">
                                <div class="col-12">
                                    <a href="{{ route('admin.listings.index') }}" class="btn btn-secondary btn-sm">
                                        {{ __('admin/listings.clear_filters') }}
                                    </a>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        @if(request()->query('approval_status') == 'approved')
                            {{ __('admin/listings.approved_listings') }}
                        @elseif(request()->query('approval_status') == 'pending')
                            {{ __('admin/listings.pending_listings') }}
                        @elseif(request()->query('active_status') == 'active')
                            {{ __('admin/listings.active_listings') }}
                        @elseif(request()->query('active_status') == 'inactive')
                            {{ __('admin/listings.inactive_listings') }}
                        @else
                            {{ __('admin/listings.all_listings') }}
                        @endif
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>{{ __('admin/listings.id') }}</th>
                                <th>{{ __('admin/listings.title_field') }}</th>
                                <th>{{ __('admin/listings.user') }}</th>
                                <th>{{ __('admin/listings.category') }}</th>
                                <th>{{ __('admin/listings.price') }}</th>
                                <th>{{ __('admin/listings.location') }}</th>
                                <th>{{ __('admin/listings.featured') }}</th>
                                <th>{{ __('admin/listings.approval_status') }}</th>
                                <th>{{ __('admin/listings.active_status') }}</th>
                                <th>{{ __('admin/listings.created_at') }}</th>
                                <th>{{ __('admin/listings.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listings as $listing)
                                <tr>
                                    <td>{{ $listing->id }}</td>
                                    <td>{{ $listing->title }}</td>
                                    <td>{{ $listing->user?->isAgent() ? $listing->user->name : 'Real Estate CMS Demo' }}</td>
                                    <td>{{ $listing->category->name ?? 'N/A' }}</td>
                                    <td>{{ number_format($listing->price, 2) }} ₺</td>
                                    <td>{{ $listing->city->name ?? 'N/A' }}</td>
                                    <td>
                                        @if ($listing->is_featured)
                                            <span class="badge badge-warning">{{ __('admin/listings.featured') }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ __('admin/listings.not_featured') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($listing->is_approved)
                                            <span class="badge badge-success">{{ __('admin/listings.approved') }}</span>
                                        @elseif ($listing->is_sent_to_approver)
                                            <span class="badge badge-warning">{{ __('admin/listings.pending_approval') }}</span>
                                            <br><small class="text-muted">{{ $listing->sent_to_approver_at?->diffForHumans() }}</small>
                                        @else
                                            <span class="badge badge-secondary">{{ __('admin/listings.not_approved') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($listing->is_active)
                                            <span class="badge badge-success">{{ __('admin/listings.active') }}</span>
                                        @else
                                            <span class="badge badge-danger">{{ __('admin/listings.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $listing->created_at->format('d.m.Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.listings.show', $listing->id) }}" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if (!$showTrashed)
                                            <!-- Featured Toggle Button -->
                                            <form action="{{ route('admin.listings.toggle-featured', $listing->id) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $listing->is_featured ? 'btn-warning' : 'btn-outline-warning' }}"
                                                        onclick="return confirm('{{ __('admin/listings.confirm_toggle_featured') }}')"
                                                        title="{{ $listing->is_featured ? __('admin/listings.unmark_featured') : __('admin/listings.mark_featured') }}">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                            </form>

                                            <!-- PDF Flyer (Task #12) -->
                                            <a href="{{ route('admin.listings.flyer', $listing->id) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-secondary"
                                               title="{{ __('admin/flyer.download') }}">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>

                                            <!-- Social share (Task #10) -->
                                            <form action="{{ route('admin.listings.social-share', $listing->id) }}"
                                                  method="POST" class="d-inline-block">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary"
                                                        title="{{ __('admin/social.share_now') }}">
                                                    <i class="fas fa-share-alt"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Approval Buttons -->
                                        @if (!$showTrashed)
                                            @if (!$listing->is_approved)
                                                <!-- Approve Button -->
                                                <form action="{{ route('admin.listings.approve', $listing->id) }}" method="POST" class="d-inline-block">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('{{ __('admin/listings.confirm_approve') }}')"
                                                            title="{{ __('admin/listings.approve') }}">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Reject Button (available for all listings) -->
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="showRejectModal({{ $listing->id }}, '{{ addslashes($listing->title) }}')"
                                                    title="{{ __('admin/listings.reject') }}">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif

                                            <!-- Active/Inactive Buttons -->
                                            @if (!$listing->is_active)
                                                <form action="{{ route('admin.listings.activate', $listing->id) }}" method="POST" class="d-inline-block">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('{{ __('admin/listings.confirm_activate') }}')"
                                                            title="{{ __('admin/listings.activate') }}">
                                                        <i class="fas fa-power-off"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.listings.deactivate', $listing->id) }}" method="POST" class="d-inline-block">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('admin/listings.confirm_deactivate') }}')"
                                                            title="{{ __('admin/listings.deactivate') }}">
                                                        <i class="fas fa-power-off"></i>
                                                    </button>
                                                </form>
                                            @endif

                                        @if (!$showTrashed)
                                            <!-- Soft Delete Button -->
                                            <form action="{{ route('admin.listings.destroy', $listing->id) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Bu ilanı çöpe taşımak istediğinize emin misiniz?')"
                                                        title="Çöpe Taşı">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @else
                                            <!-- Restore Button -->
                                            <form action="{{ route('admin.listings.restore', $listing->id) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Bu ilanı geri yüklemek istediğinize emin misiniz?')"
                                                        title="Geri Yükle">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>

                                            <!-- Permanent Delete Button -->
                                            <form action="{{ route('admin.listings.force-destroy', $listing->id) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bu ilanı kalıcı olarak silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')"
                                                        title="Kalıcı Olarak Sil">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center">{{ __('admin/listings.no_listings') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $listings->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">{{ __('admin/listings.reject_modal_title') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>{{ __('admin/listings.title_field') }}:</strong> <span id="rejectListingTitle"></span></p>
                    <div class="form-group">
                        <label for="rejection_reason">{{ __('admin/listings.rejection_reason') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4"
                                  placeholder="{{ __('admin/listings.rejection_reason_placeholder') }}" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('admin/general.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('admin/listings.reject') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function showRejectModal(listingId, listingTitle) {
            // Set the form action to the reject route
            $('#rejectForm').attr('action', '{{ url("admin/listings") }}/' + listingId + '/reject');

            // Set the listing title in the modal
            $('#rejectListingTitle').text(listingTitle);

            // Clear the textarea
            $('#rejection_reason').val('');

            // Show the modal
            $('#rejectModal').modal('show');
        }
    </script>
@endpush
