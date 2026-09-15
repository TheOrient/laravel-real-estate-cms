<!-- User Information -->
@if(isset($user) && $user->id === 1 && auth()->id() !== 1)
    <div class="alert alert-warning">
        <i class="fas fa-shield-alt"></i> {{ __('admin/users.super_admin_protected') }}
    </div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="first_name">{{ __('admin/users.first_name') }} <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name ?? '') }}" required>
            @error('first_name')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="last_name">{{ __('admin/users.last_name') }} <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name ?? '') }}" required>
            @error('last_name')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="email">{{ __('admin/users.email') }} <span class="text-danger">*</span></label>
    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" required>
    @error('email')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="password">{{ __('admin/users.password') }} @if(!isset($user))<span class="text-danger">*</span>@endif</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" @if(!isset($user)) required @endif>
            @if(isset($user))
                <small class="form-text text-muted">{{ __('admin/users.leave_blank') }}</small>
            @endif
            @error('password')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="password_confirmation">{{ __('admin/users.password_confirmation') }} @if(!isset($user))<span class="text-danger">*</span>@endif</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" @if(!isset($user)) required @endif>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="phone">{{ __('admin/users.phone') }}</label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}">
            @error('phone')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="enable_whatsapp">{{ __('admin/users.enable_whatsapp') }}</label>
            <select name="enable_whatsapp" id="enable_whatsapp" class="form-control @error('enable_whatsapp') is-invalid @enderror">
                <option value="yes" {{ (old('enable_whatsapp', $user->enable_whatsapp ?? '') == 'yes') ? 'selected' : '' }}>{{ __('admin/users.yes') }}</option>
                <option value="no" {{ (old('enable_whatsapp', $user->enable_whatsapp ?? '') == 'no') ? 'selected' : '' }}>{{ __('admin/users.no') }}</option>
            </select>
            @error('enable_whatsapp')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="user_role">{{ __('admin/users.user_role') }} <span class="text-danger">*</span></label>
    @if(isset($user) && $user->id === 1)
        <!-- Super admin role cannot be changed -->
        <input type="hidden" name="user_role" value="admin">
        <input type="text" class="form-control" value="{{ __('admin/users.admin') }}" disabled>
        <small class="form-text text-muted">
            <i class="fas fa-info-circle"></i> {{ __('admin/users.cannot_change_super_admin_role') }}
        </small>
    @else
        <select class="form-control @error('user_role') is-invalid @enderror" id="user_role" name="user_role" required>
            <option value="agent" {{ (old('user_role', $user->user_role ?? 'agent') == 'agent') ? 'selected' : '' }}>{{ __('admin/users.agent') }}</option>
            @if(isset($user))
                <option value="admin" {{ old('user_role', $user->user_role ?? '') == 'admin' ? 'selected' : '' }}>{{ __('admin/users.admin') }}</option>
            @endif
        </select>
        @error('user_role')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    @endif
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ (old('is_active', $user->is_active ?? true)) ? 'checked' : '' }}>
                <label class="custom-control-label" for="is_active">{{ __('admin/users.is_active') }}</label>
            </div>
            @error('is_active')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="email_verified" name="email_verified" value="1" {{ (old('email_verified', isset($user) && $user->hasVerifiedEmail())) ? 'checked' : '' }}>
                <label class="custom-control-label" for="email_verified">
                    {{ __('admin/users.email_verified') }}
                    @if(isset($user) && $user->hasVerifiedEmail())
                        <small class="text-success">({{ $user->email_verified_at->format('d.m.Y H:i') }})</small>
                    @endif
                </label>
            </div>
            @error('email_verified')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="bio">Biyografi</label>
    <textarea class="form-control @error('bio') is-invalid @enderror"
              id="bio"
              name="bio"
              rows="4"
              placeholder="Danışmanın uzmanlık alanı ve bölge deneyimi">{{ old('bio', $user->bio ?? '') }}</textarea>
    @error('bio')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group mt-4">
    <button type="submit" class="btn btn-primary">{{ __('admin/users.save') }}</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-default">{{ __('admin/users.cancel') }}</a>
</div>
