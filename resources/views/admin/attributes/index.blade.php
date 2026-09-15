@extends('admin.layouts.app')

@section('title', __('admin/attributes.attributes'))

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ __('admin/attributes.attributes') }}</h1>
        <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ __('admin/attributes.add_attribute') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($attributes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>{{ __('admin/attributes.name') }}</th>
                                <th>{{ __('admin/attributes.display_type_search') }}</th>
                                <th>{{ __('admin/attributes.display_type_user_panel') }}</th>
                                <th>{{ __('admin/attributes.is_required') }}</th>
                                <th>{{ __('admin/attributes.is_filterable') }}</th>
                                <th>{{ __('admin/attributes.attribute_values') }}</th>
                                <th>{{ __('admin/general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attributes as $attribute)
                                <tr>
                                    <td>{{ $attribute->id }}</td>
                                    <td>
                                        <strong>{{ $attribute->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $attribute->display_type_search }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $attribute->display_type_user_panel }}</span>
                                    </td>
                                    <td>
                                        @if($attribute->is_required)
                                            <span class="badge badge-danger">{{ __('admin/general.yes') }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ __('admin/general.no') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attribute->is_filterable)
                                            <span class="badge badge-success">{{ __('admin/general.yes') }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ __('admin/general.no') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attribute->values && $attribute->values->count() > 0)
                                            <span class="badge badge-primary">{{ $attribute->values->count() }} {{ __('admin/attributes.value') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.attributes.edit', $attribute) }}"
                                               class="btn btn-outline-primary" title="{{ __('admin/general.edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.attributes.destroy', $attribute) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('{{ __('admin/attributes.confirm_delete') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="{{ __('admin/general.delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-sliders-h text-muted" style="font-size: 48px;"></i>
                    <h5 class="mt-3">{{ __('admin/attributes.no_attributes') }}</h5>
                    <p class="text-muted">{{ __('admin/attributes.no_attributes') }}</p>
                    <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('admin/attributes.add_attribute') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
