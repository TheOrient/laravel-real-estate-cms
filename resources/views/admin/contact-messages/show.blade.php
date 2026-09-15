@extends('admin.layouts.app')

@section('title', __('admin/contact_messages.message_details'))

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('admin/contact_messages.message_details') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin/general.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.contact-messages.index') }}">{{ __('admin/contact_messages.contact_messages') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin/contact_messages.message_details') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Message Details -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            {{ __('admin/contact_messages.message_content') }}
                            <span class="badge {{ $contactMessage->status_badge_class }} ml-2">
                                {{ $contactMessage->status_text }}
                            </span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <!-- Subject -->
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('admin/contact_messages.subject') }}:</label>
                            <p class="form-control-plaintext">{{ $contactMessage->subject }}</p>
                        </div>

                        <!-- Message Content -->
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('admin/contact_messages.message') }}:</label>
                            <div class="border rounded p-3 bg-light">
                                {!! nl2br(e($contactMessage->message)) !!}
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="mailto:{{ $contactMessage->email }}?subject=Re: {{ $contactMessage->subject }}"
                                   class="btn btn-success">
                                    <i class="fas fa-reply mr-1"></i>
                                    {{ __('admin/contact_messages.reply') }}
                                </a>

                                <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i>
                                    {{ __('admin/contact_messages.back') }}
                                </a>
                            </div>

                            <div>
                                <form action="{{ route('admin.contact-messages.destroy', $contactMessage) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('{{ __('admin/contact_messages.delete_confirm') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash mr-1"></i>
                                        {{ __('admin/contact_messages.delete_message') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Sender Information -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('admin/contact_messages.sender_info') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('admin/contact_messages.name') }}:</label>
                            <p class="form-control-plaintext">{{ $contactMessage->name }}</p>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('admin/contact_messages.email') }}:</label>
                            <p class="form-control-plaintext">
                                <a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a>
                            </p>
                        </div>

                        @if($contactMessage->phone)
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('admin/contact_messages.phone') }}:</label>
                            <p class="form-control-plaintext">
                                <a href="tel:{{ $contactMessage->phone }}">{{ $contactMessage->phone }}</a>
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Message Metadata -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('admin/contact_messages.message_metadata') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('admin/contact_messages.created_at') }}:</label>
                            <p class="form-control-plaintext">{{ $contactMessage->created_at->format('d.m.Y H:i:s') }}</p>
                        </div>

                        @if($contactMessage->read_at)
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('admin/contact_messages.read_at') }}:</label>
                            <p class="form-control-plaintext">{{ $contactMessage->read_at->format('d.m.Y H:i:s') }}</p>
                        </div>
                        @endif

                        @if($contactMessage->ip_address)
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('admin/contact_messages.ip_address') }}:</label>
                            <p class="form-control-plaintext">
                                <code>{{ $contactMessage->ip_address }}</code>
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Status Management -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('admin/contact_messages.update_status') }}</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.contact-messages.update', $contactMessage) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="status">{{ __('admin/contact_messages.status') }}:</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="new" {{ $contactMessage->status == 'new' ? 'selected' : '' }}>
                                        {{ __('admin/contact_messages.status_new') }}
                                    </option>
                                    <option value="read" {{ $contactMessage->status == 'read' ? 'selected' : '' }}>
                                        {{ __('admin/contact_messages.status_read') }}
                                    </option>
                                    <option value="replied" {{ $contactMessage->status == 'replied' ? 'selected' : '' }}>
                                        {{ __('admin/contact_messages.status_replied') }}
                                    </option>
                                    <option value="archived" {{ $contactMessage->status == 'archived' ? 'selected' : '' }}>
                                        {{ __('admin/contact_messages.status_archived') }}
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="admin_notes">{{ __('admin/contact_messages.admin_notes') }}:</label>
                                <textarea name="admin_notes"
                                          id="admin_notes"
                                          class="form-control"
                                          rows="4"
                                          placeholder="{{ __('admin/contact_messages.admin_notes_placeholder') }}">{{ $contactMessage->admin_notes }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-1"></i>
                                {{ __('admin/contact_messages.save') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
