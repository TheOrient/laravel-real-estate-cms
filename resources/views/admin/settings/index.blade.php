@extends('admin.layouts.app')

@section('title', 'Site Ayarları')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cog mr-2"></i>
                    Site Ayarları
                </h3>
                <div class="card-tools">
                    <form action="{{ route('admin.settings.clear-cache') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="fas fa-broom mr-1"></i>
                            Önbelleği Temizle
                        </button>
                    </form>
                </div>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    @foreach($settingsGrouped as $groupName => $settings)
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2 mb-3">
                                    @switch($groupName)
                                        @case('general')
                                            <i class="fas fa-info-circle mr-2"></i>Genel Ayarlar
                                            @break
                                        @case('appearance')
                                            <i class="fas fa-paint-brush mr-2"></i>Görünüm Ayarları
                                            @break
                                        @case('contact')
                                            <i class="fas fa-phone mr-2"></i>İletişim Bilgileri
                                            @break
                                        @case('footer')
                                            <i class="fas fa-copyright mr-2"></i>Footer Ayarları
                                            @break
                                        @case('social')
                                            <i class="fab fa-facebook mr-2"></i>Sosyal Medya
                                            @break
                                        @case('mobile')
                                            <i class="fas fa-mobile-alt mr-2"></i>Mobil Uygulamalar
                                            @break
                                        @case('email')
                                            <i class="fas fa-envelope mr-2"></i>E-posta Ayarları
                                            @break
                                        @case('scripts')
                                            <i class="fas fa-code mr-2"></i>Özel Script Kodları
                                            @break
                                        @case('system')
                                            <i class="fas fa-cogs mr-2"></i>Sistem Ayarları
                                            @break
                                        @case('api')
                                            <i class="fas fa-key mr-2"></i>API Ayarları
                                            @break
                                        @case('security')
                                            <i class="fas fa-shield-alt mr-2"></i>Güvenlik ve CAPTCHA
                                            @break
                                        @default
                                            <i class="fas fa-cog mr-2"></i>{{ ucfirst($groupName) }}
                                    @endswitch
                                </h5>
                            </div>
                        </div>

                        {{-- Show PayTR Callback URL for payment settings --}}
                        @if($groupName === 'payment')
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="callout callout-info">
                                    <h5><i class="fas fa-info-circle"></i> PayTR Entegrasyon Bilgileri</h5>
                                    <p>PayTR paneline giriş yaparak aşağıdaki callback URL'lerini kaydetmeniz gerekmektedir:</p>
                                    <div class="mb-2">
                                        <strong><i class="fas fa-link"></i> Callback URL (Bildirim URL):</strong><br>
                                        <code class="bg-white px-2 py-1 border">{{ route('payment.paytr-callback') }}</code>
                                        <button type="button" class="btn btn-sm btn-outline-secondary ml-2" onclick="copyToClipboard('{{ route('payment.paytr-callback') }}')">
                                            <i class="fas fa-copy"></i> Kopyala
                                        </button>
                                    </div>
                                    <div class="mb-2">
                                        <strong><i class="fas fa-check-circle"></i> Başarılı URL:</strong><br>
                                        <code class="bg-white px-2 py-1 border">{{ route('payment.callback.success') }}</code>
                                        <button type="button" class="btn btn-sm btn-outline-secondary ml-2" onclick="copyToClipboard('{{ route('payment.callback.success') }}')">
                                            <i class="fas fa-copy"></i> Kopyala
                                        </button>
                                    </div>
                                    <div class="mb-2">
                                        <strong><i class="fas fa-times-circle"></i> Başarısız URL:</strong><br>
                                        <code class="bg-white px-2 py-1 border">{{ route('payment.callback.fail') }}</code>
                                        <button type="button" class="btn btn-sm btn-outline-secondary ml-2" onclick="copyToClipboard('{{ route('payment.callback.fail') }}')">
                                            <i class="fas fa-copy"></i> Kopyala
                                        </button>
                                    </div>
                                    <hr>
                                    <p class="mb-0">
                                        <small class="text-muted">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Not:</strong> Bu URL'leri PayTR merchant panelinden ayarlamalısınız.
                                            Ödeme bildirimleri bu URL'lere POST yöntemiyle gönderilecektir.
                                        </small>
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Show Security Configuration info --}}
                        @if($groupName === 'security')
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="callout callout-warning">
                                    <h5><i class="fas fa-shield-alt"></i> Güvenlik ve CAPTCHA Ayarları</h5>
                                    <p>Sitenizi botlardan ve spam gönderimlerden korumak için CAPTCHA servisi kullanabilirsiniz.</p>
                                    <ul>
                                        <li><strong>reCAPTCHA v2:</strong> "Ben robot değilim" kutucuğu işaretlenir.</li>
                                        <li><strong>reCAPTCHA v2 Invisible:</strong> Görünmez, otomatik çalışır. Şüpheli aktivitede challenge gösterir.</li>
                                        <li><strong>reCAPTCHA v3:</strong> Kullanıcı etkileşimi gerektirmez, arka planda çalışır.</li>
                                        <li><strong>Cloudflare Turnstile:</strong> Gizlilik odaklı, kullanıcı dostu alternatif.</li>
                                    </ul>
                                    <hr>
                                    <p class="mb-0">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Not:</strong> Seçtiğiniz servisin API anahtarlarını ilgili alanlara girmelisiniz.
                                        </small>
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Show Scripts Configuration info --}}
                        @if($groupName === 'scripts')
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="callout callout-info">
                                    <h5><i class="fas fa-code"></i> Özel Script Kodları Bilgilendirme</h5>
                                    <p>Bu bölümden sitenize özel HTML, JavaScript veya CSS kodları ekleyebilirsiniz:</p>
                                    <ul>
                                        <li><strong>Head Scriptleri:</strong> <code>&lt;/head&gt;</code> etiketinden önce eklenir. Google Analytics, Facebook Pixel, Meta Tags gibi kodlar için uygundur.</li>
                                        <li><strong>Footer Scriptleri:</strong> <code>&lt;/body&gt;</code> etiketinden önce eklenir. Chat Widget, Tracking Scripts gibi kodlar için uygundur.</li>
                                    </ul>
                                    <div>
                                        <strong>Örnek Kullanımlar:</strong>
                                        <ul>
                                            <li>Google Analytics / Google Tag Manager</li>
                                            <li>Facebook Pixel</li>
                                            <li>Chat Widget (Tawk.to, Crisp, vs.)</li>
                                            <li>Heatmap Araçları (Hotjar, Crazy Egg, vs.)</li>
                                            <li>Özel CSS veya JavaScript kodları</li>
                                        </ul>
                                    </div>
                                    <hr>
                                    <p class="mb-0">
                                        <small class="text-muted">
                                            <i class="fas fa-exclamation-triangle text-warning"></i>
                                            <strong>Dikkat:</strong> Yanlış kod eklenmesi sitenizin çalışmamasına neden olabilir. Kodları eklerken dikkatli olun.
                                        </small>
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            @foreach($settings as $setting)
                                <div class="{{ $setting->type === 'code' ? 'col-12' : 'col-md-6' }} mb-3">
                                    <label for="{{ $setting->key }}" class="form-label fw-bold">
                                        {{ $setting->description }}
                                    </label>

                                    @if($setting->is_encrypted)
                                        <span class="badge badge-warning mb-2">
                                            <i class="fas fa-lock"></i> Şifreli
                                        </span>
                                    @endif

                                    @switch($setting->type)
                                        @case('code')
                                            <textarea class="form-control @error($setting->key) is-invalid @enderror"
                                                      id="{{ $setting->key }}"
                                                      name="{{ $setting->key }}"
                                                      rows="8"
                                                      style="font-family: 'Courier New', monospace; font-size: 12px;"
                                                      placeholder="{{ $setting->description }}">{{ old($setting->key, $setting->value) }}</textarea>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-code"></i> HTML, JavaScript veya CSS kodlarını buraya ekleyebilirsiniz.
                                                @if(str_contains($setting->key, 'head'))
                                                    Google Analytics, Facebook Pixel gibi kodlar için uygundur.
                                                @endif
                                            </small>
                                            @break

                                        @case('text')
                                        @case('email')
                                            @if($setting->is_encrypted)
                                                {{-- Encrypted fields: show masked value --}}
                                                <input type="password"
                                                       class="form-control @error($setting->key) is-invalid @enderror"
                                                       id="{{ $setting->key }}"
                                                       name="{{ $setting->key }}"
                                                       value=""
                                                       placeholder="{{ $setting->hasValue() ? '••••••••••••••••' : $setting->description }}"
                                                       autocomplete="off">
                                                <small class="form-text text-muted">
                                                    @if($setting->hasValue())
                                                        <i class="fas fa-shield-alt text-success"></i> Kayıtlı (Değiştirmek için yeni değer girin, aksi halde boş bırakın)
                                                    @else
                                                        Henüz ayarlanmamış
                                                    @endif
                                                </small>
                                            @else
                                                {{-- Non-encrypted fields: show normally --}}
                                                <input type="{{ $setting->type }}"
                                                       class="form-control @error($setting->key) is-invalid @enderror"
                                                       id="{{ $setting->key }}"
                                                       name="{{ $setting->key }}"
                                                       value="{{ old($setting->key, $setting->value) }}"
                                                       placeholder="{{ $setting->description }}">
                                            @endif
                                            @break

                                        @case('password')
                                            <input type="password"
                                                   class="form-control @error($setting->key) is-invalid @enderror"
                                                   id="{{ $setting->key }}"
                                                   name="{{ $setting->key }}"
                                                   value=""
                                                   placeholder="{{ $setting->hasValue() ? '••••••••••••••••' : $setting->description }}"
                                                   autocomplete="new-password">
                                            <small class="form-text text-muted">
                                                @if($setting->hasValue())
                                                    <i class="fas fa-shield-alt text-success"></i> Kayıtlı (Değiştirmek için yeni şifre girin, aksi halde boş bırakın)
                                                @else
                                                    Boş bırakılabilir
                                                @endif
                                            </small>
                                            @break

                                        @case('textarea')
                                            <textarea class="form-control @error($setting->key) is-invalid @enderror"
                                                      id="{{ $setting->key }}"
                                                      name="{{ $setting->key }}"
                                                      rows="3"
                                                      placeholder="{{ $setting->description }}">{{ old($setting->key, $setting->value) }}</textarea>
                                            @break

                                        @case('image')
                                            @if($setting->value)
                                                <div class="mb-2">
                                                    {{-- Tüm ayar görselleri public/uploads altından servis edilir --}}
                                                    <img src="{{ asset($setting->value) }}"
                                                         alt="{{ $setting->description }}"
                                                         class="img-thumbnail"
                                                         style="max-height: 100px;">
                                                    <div class="mt-1">
                                                        <small class="text-muted">Mevcut: {{ basename($setting->value) }}</small>
                                                    </div>
                                                </div>
                                            @endif
                                            <input type="file"
                                                   class="form-control @error($setting->key) is-invalid @enderror"
                                                   id="{{ $setting->key }}"
                                                   name="{{ $setting->key }}"
                                                   accept="image/*">
                                            <small class="form-text text-muted">
                                                Desteklenen formatlar: JPG, PNG, GIF. Maksimum boyut: 2MB
                                            </small>
                                            @break

                                        @case('boolean')
                                            <div class="form-check">
                                                <input type="checkbox"
                                                       class="form-check-input @error($setting->key) is-invalid @enderror"
                                                       id="{{ $setting->key }}"
                                                       name="{{ $setting->key }}"
                                                       value="1"
                                                       {{ old($setting->key, $setting->value) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="{{ $setting->key }}">
                                                    Aktif
                                                </label>
                                            </div>
                                            @break

                                        @case('number')
                                            <input type="number"
                                                   class="form-control @error($setting->key) is-invalid @enderror"
                                                   id="{{ $setting->key }}"
                                                   name="{{ $setting->key }}"
                                                   value="{{ old($setting->key, $setting->value) }}"
                                                   placeholder="{{ $setting->description }}">
                                            @break

                                        @case('select')
                                            @php
                                                $options = explode(',', $setting->options ?? '');
                                            @endphp
                                            <select class="form-control @error($setting->key) is-invalid @enderror"
                                                    id="{{ $setting->key }}"
                                                    name="{{ $setting->key }}">
                                                @foreach($options as $option)
                                                    <option value="{{ $option }}" {{ old($setting->key, $setting->value) == $option ? 'selected' : '' }}>
                                                        @switch($option)
                                                            @case('none') Devre Dışı @break
                                                            @case('recaptcha_v2') reCAPTCHA v2 @break
                                                            @case('recaptcha_v2_invisible') reCAPTCHA v2 Invisible @break
                                                            @case('recaptcha_v3') reCAPTCHA v3 @break
                                                            @case('turnstile') Cloudflare Turnstile @break
                                                            @case('inline') Yan Yana @break
                                                            @case('dropdown') Açılır Menü @break
                                                            @default {{ ucfirst($option) }}
                                                        @endswitch
                                                    </option>
                                                @endforeach
                                            </select>
                                            @break

                                        @default
                                            <input type="text"
                                                   class="form-control @error($setting->key) is-invalid @enderror"
                                                   id="{{ $setting->key }}"
                                                   name="{{ $setting->key }}"
                                                   value="{{ old($setting->key, $setting->value) }}"
                                                   placeholder="{{ $setting->description }}">
                                    @endswitch

                                    @error($setting->key)
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                        @if(!$loop->last)
                            <hr class="my-4">
                        @endif
                    @endforeach
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Ayarları Kaydet
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-times mr-2"></i>
                        İptal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    // Create temporary textarea
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);

    // Select and copy
    textarea.select();
    textarea.setSelectionRange(0, 99999); // For mobile devices

    try {
        document.execCommand('copy');
        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Kopyalandı!',
            text: 'URL panoya kopyalandı.',
            timer: 2000,
            showConfirmButton: false
        });
    } catch (err) {
        // Fallback for older browsers
        alert('URL kopyalandı: ' + text);
    }

    // Remove textarea
    document.body.removeChild(textarea);
}
</script>
@endpush