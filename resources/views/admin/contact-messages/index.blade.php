@extends('admin.layouts.app')

@section('title', __('admin/contact_messages.title'))

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('admin/contact_messages.manage_messages') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin/general.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin/contact_messages.contact_messages') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $statusCounts['all'] }}</h3>
                        <p>{{ __('admin/contact_messages.total_messages') }}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $statusCounts['new'] }}</h3>
                        <p>{{ __('admin/contact_messages.new_messages') }}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $statusCounts['read'] }}</h3>
                        <p>{{ __('admin/contact_messages.read_messages') }}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $statusCounts['replied'] }}</h3>
                        <p>{{ __('admin/contact_messages.replied_messages') }}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('admin/contact_messages.contact_messages') }}</h3>

                        <!-- Search and Filter Form -->
                        <div class="card-tools">
                            <form method="GET" class="form-inline">
                                <div class="input-group input-group-sm" style="width: 200px;">
                                    <input type="text" name="search" class="form-control float-right"
                                           placeholder="{{ __('admin/contact_messages.search_placeholder') }}"
                                           value="{{ request('search') }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-default">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <select name="status" class="form-control form-control-sm ml-2" onchange="this.form.submit()">
                                    <option value="">{{ __('admin/contact_messages.all_messages') }}</option>
                                    <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>
                                        {{ __('admin/contact_messages.new_messages') }}
                                    </option>
                                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>
                                        {{ __('admin/contact_messages.read_messages') }}
                                    </option>
                                    <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>
                                        {{ __('admin/contact_messages.replied_messages') }}
                                    </option>
                                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>
                                        {{ __('admin/contact_messages.archived_messages') }}
                                    </option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <!-- Bulk Actions -->
                    <div class="card-body pt-2">
                        @if($messages->count() > 0)
                            <form id="bulkActionForm" method="POST" action="{{ route('admin.contact-messages.bulk-action') }}">
                                @csrf
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <div class="btn-group" role="group">
                                            <select name="action" class="form-control form-control-sm d-inline-block w-auto">
                                                <option value="">{{ __('admin/contact_messages.select_action') }}</option>
                                                <option value="mark_read">{{ __('admin/contact_messages.bulk_mark_read') }}</option>
                                                <option value="mark_replied">{{ __('admin/contact_messages.bulk_mark_replied') }}</option>
                                                <option value="archive">{{ __('admin/contact_messages.bulk_archive') }}</option>
                                                <option value="delete">{{ __('admin/contact_messages.bulk_delete') }}</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-secondary ml-2" onclick="return confirmBulkAction()">
                                                {{ __('admin/contact_messages.apply_action') }}
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <small class="text-muted">{{ $messages->total() }} mesaj bulundu</small>
                                    </div>
                                </div>
                        @endif

                        <div class="table-responsive p-0">
                            @if($messages->count() > 0)
                                <table class="table table-hover text-nowrap">
                                    <thead>
                                        <tr>
                                            <th width="30">
                                                <div class="icheck-primary">
                                                    <input type="checkbox" id="checkAll">
                                                    <label for="checkAll"></label>
                                                </div>
                                            </th>
                                            <th>{{ __('admin/contact_messages.sender') }}</th>
                                            <th>{{ __('admin/contact_messages.subject_message') }}</th>
                                            <th>{{ __('admin/contact_messages.status') }}</th>
                                            <th>{{ __('admin/contact_messages.date') }}</th>
                                            <th>{{ __('admin/contact_messages.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($messages as $message)
                                            <tr class="{{ $message->isNew() ? 'table-primary' : '' }}">
                                                <td>
                                                    <div class="icheck-primary">
                                                        <input type="checkbox" name="message_ids[]" value="{{ $message->id }}" id="check{{ $message->id }}">
                                                        <label for="check{{ $message->id }}"></label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong>{{ $message->name }}</strong>
                                                    <br><small class="text-muted">{{ $message->email }}</small>
                                                    @if($message->phone)
                                                        <br><small class="text-muted">{{ $message->phone }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ $message->subject }}</strong>
                                                    <br><small class="text-muted">{{ Str::limit($message->message, 100) }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $message->status_badge_class }}">
                                                        {{ $message->status_text }}
                                                    </span>
                                                    @if($message->isNew())
                                                        <br><small class="text-primary font-weight-bold">YENİ</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $message->created_at->format('d.m.Y H:i') }}
                                                    @if($message->read_at)
                                                        <br><small class="text-muted">Okundu: {{ $message->read_at->format('d.m.Y H:i') }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('admin.contact-messages.show', $message) }}"
                                                           class="btn btn-info btn-sm"
                                                           title="{{ __('admin/contact_messages.view_message') }}">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="mailto:{{ $message->email }}?subject=Re: {{ $message->subject }}"
                                                           class="btn btn-success btn-sm"
                                                           title="{{ __('admin/contact_messages.reply_to_message') }}">
                                                            <i class="fas fa-reply"></i>
                                                        </a>
                                                        <form action="{{ route('admin.contact-messages.destroy', $message) }}"
                                                              method="POST"
                                                              class="d-inline"
                                                              onsubmit="return confirm('{{ __('admin/contact_messages.delete_confirm') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="btn btn-danger btn-sm"
                                                                    title="{{ __('admin/contact_messages.delete_message') }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                @if($messages->count() > 0)
                                    </form>
                                @endif
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                                    <h5>{{ __('admin/contact_messages.no_messages') }}</h5>
                                </div>
                            @endif
                        </div>

                        @if($messages->hasPages())
                            <div class="card-footer">
                                {{ $messages->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Check all functionality
    $('#checkAll').change(function() {
        $('input[name="message_ids[]"]').prop('checked', this.checked);
    });

    // Update check all when individual checkboxes change
    $('input[name="message_ids[]"]').change(function() {
        if ($('input[name="message_ids[]"]:checked').length == $('input[name="message_ids[]"]').length) {
            $('#checkAll').prop('checked', true);
        } else {
            $('#checkAll').prop('checked', false);
        }
    });
});

function confirmBulkAction() {
    var selectedIds = $('input[name="message_ids[]"]:checked');
    var action = $('select[name="action"]').val();

    if (selectedIds.length === 0) {
        alert('{{ __('admin/contact_messages.select_messages') }}');
        return false;
    }

    if (action === '') {
        alert('{{ __('admin/contact_messages.select_action') }}');
        return false;
    }

    if (action === 'delete') {
        return confirm('{{ __('admin/contact_messages.bulk_delete_confirm') }}');
    }

    return true;
}
</script>
@endpush
