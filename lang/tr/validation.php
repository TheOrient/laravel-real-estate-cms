<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Doğrulama Dil Satırları
    |--------------------------------------------------------------------------
    |
    | Aşağıdaki dil satırları, doğrulayıcı sınıfı tarafından kullanılan
    | varsayılan hata mesajlarını içerir. Bu kuralların bazılarının boyut
    | kuralları gibi birden fazla sürümü vardır. Bu mesajların her birini
    | burada özelleştirebilirsiniz.
    |
    */

    'accepted' => ':attribute kabul edilmelidir.',
    'accepted_if' => ':attribute, :other :value olduğunda kabul edilmelidir.',
    'active_url' => ':attribute geçerli bir URL olmalıdır.',
    'after' => ':attribute, :date tarihinden sonra bir tarih olmalıdır.',
    'after_or_equal' => ':attribute, :date tarihine eşit veya sonraki bir tarih olmalıdır.',
    'alpha' => ':attribute sadece harflerden oluşmalıdır.',
    'alpha_dash' => ':attribute sadece harf, rakam, tire ve alt çizgi içerebilir.',
    'alpha_num' => ':attribute sadece harf ve rakamlardan oluşmalıdır.',
    'any_of' => ':attribute geçersizdir.',
    'array' => ':attribute bir dizi olmalıdır.',
    'ascii' => ':attribute sadece tek baytlık alfanümerik karakterler ve semboller içermelidir.',
    'before' => ':attribute, :date tarihinden önce bir tarih olmalıdır.',
    'before_or_equal' => ':attribute, :date tarihine eşit veya önceki bir tarih olmalıdır.',
    'between' => [
        'array' => ':attribute, :min ile :max arasında öğeye sahip olmalıdır.',
        'file' => ':attribute, :min ile :max kilobayt arasında olmalıdır.',
        'numeric' => ':attribute, :min ile :max arasında olmalıdır.',
        'string' => ':attribute, :min ile :max karakter arasında olmalıdır.',
    ],
    'boolean' => ':attribute doğru veya yanlış olmalıdır.',
    'can' => ':attribute yetkisiz bir değer içeriyor.',
    'confirmed' => ':attribute onayı eşleşmiyor.',
    'contains' => ':attribute gerekli bir değeri içermiyor.',
    'current_password' => 'Şifre yanlış.',
    'date' => ':attribute geçerli bir tarih olmalıdır.',
    'date_equals' => ':attribute, :date tarihine eşit bir tarih olmalıdır.',
    'date_format' => ':attribute, :format formatıyla eşleşmelidir.',
    'decimal' => ':attribute, :decimal ondalık basamağa sahip olmalıdır.',
    'declined' => ':attribute reddedilmelidir.',
    'declined_if' => ':attribute, :other :value olduğunda reddedilmelidir.',
    'different' => ':attribute ile :other farklı olmalıdır.',
    'digits' => ':attribute, :digits basamaklı olmalıdır.',
    'digits_between' => ':attribute, :min ile :max basamak arasında olmalıdır.',
    'dimensions' => ':attribute geçersiz görsel boyutlarına sahip.',
    'distinct' => ':attribute yinelenen bir değere sahip.',
    'doesnt_end_with' => ':attribute şunlardan biriyle bitmemelidir: :values.',
    'doesnt_start_with' => ':attribute şunlardan biriyle başlamamalıdır: :values.',
    'email' => ':attribute geçerli bir e-posta adresi olmalıdır.',
    'ends_with' => ':attribute şunlardan biriyle bitmelidir: :values.',
    'enum' => 'Seçilen :attribute geçersiz.',
    'exists' => 'Seçilen :attribute geçersiz.',
    'extensions' => ':attribute şu uzantılardan birine sahip olmalıdır: :values.',
    'file' => ':attribute bir dosya olmalıdır.',
    'filled' => ':attribute bir değere sahip olmalıdır.',
    'gt' => [
        'array' => ':attribute, :value öğeden daha fazla öğeye sahip olmalıdır.',
        'file' => ':attribute, :value kilobayttan büyük olmalıdır.',
        'numeric' => ':attribute, :value değerinden büyük olmalıdır.',
        'string' => ':attribute, :value karakterden uzun olmalıdır.',
    ],
    'gte' => [
        'array' => ':attribute, :value veya daha fazla öğeye sahip olmalıdır.',
        'file' => ':attribute, :value kilobayta eşit veya daha büyük olmalıdır.',
        'numeric' => ':attribute, :value değerine eşit veya daha büyük olmalıdır.',
        'string' => ':attribute, :value karaktere eşit veya daha uzun olmalıdır.',
    ],
    'hex_color' => ':attribute geçerli bir onaltılık renk olmalıdır.',
    'image' => ':attribute bir resim olmalıdır.',
    'in' => 'Seçilen :attribute geçersiz.',
    'in_array' => ':attribute, :other içinde bulunmalıdır.',
    'integer' => ':attribute bir tam sayı olmalıdır.',
    'ip' => ':attribute geçerli bir IP adresi olmalıdır.',
    'ipv4' => ':attribute geçerli bir IPv4 adresi olmalıdır.',
    'ipv6' => ':attribute geçerli bir IPv6 adresi olmalıdır.',
    'json' => ':attribute geçerli bir JSON dizesi olmalıdır.',
    'list' => ':attribute bir liste olmalıdır.',
    'lowercase' => ':attribute küçük harf olmalıdır.',
    'lt' => [
        'array' => ':attribute, :value öğeden daha az öğeye sahip olmalıdır.',
        'file' => ':attribute, :value kilobayttan küçük olmalıdır.',
        'numeric' => ':attribute, :value değerinden küçük olmalıdır.',
        'string' => ':attribute, :value karakterden kısa olmalıdır.',
    ],
    'lte' => [
        'array' => ':attribute, :value öğeden fazla öğeye sahip olmamalıdır.',
        'file' => ':attribute, :value kilobayta eşit veya daha küçük olmalıdır.',
        'numeric' => ':attribute, :value değerine eşit veya daha küçük olmalıdır.',
        'string' => ':attribute, :value karaktere eşit veya daha kısa olmalıdır.',
    ],
    'mac_address' => ':attribute geçerli bir MAC adresi olmalıdır.',
    'max' => [
        'array' => ':attribute, :max öğeden fazla öğeye sahip olmamalıdır.',
        'file' => ':attribute, :max kilobayttan büyük olmamalıdır.',
        'numeric' => ':attribute, :max değerinden büyük olmamalıdır.',
        'string' => ':attribute, :max karakterden uzun olmamalıdır.',
    ],
    'max_digits' => ':attribute, :max basamaktan fazla olmamalıdır.',
    'mimes' => ':attribute şu türde bir dosya olmalıdır: :values.',
    'mimetypes' => ':attribute şu türde bir dosya olmalıdır: :values.',
    'min' => [
        'array' => ':attribute en az :min öğeye sahip olmalıdır.',
        'file' => ':attribute en az :min kilobayt olmalıdır.',
        'numeric' => ':attribute en az :min olmalıdır.',
        'string' => ':attribute en az :min karakter olmalıdır.',
    ],
    'min_digits' => ':attribute en az :min basamağa sahip olmalıdır.',
    'missing' => ':attribute eksik olmalıdır.',
    'missing_if' => ':attribute, :other :value olduğunda eksik olmalıdır.',
    'missing_unless' => ':attribute, :other :values içinde olmadıkça eksik olmalıdır.',
    'missing_with' => ':attribute, :values mevcut olduğunda eksik olmalıdır.',
    'missing_with_all' => ':attribute, :values mevcut olduğunda eksik olmalıdır.',
    'multiple_of' => ':attribute, :value değerinin katı olmalıdır.',
    'not_in' => 'Seçilen :attribute geçersiz.',
    'not_regex' => ':attribute formatı geçersiz.',
    'numeric' => ':attribute bir sayı olmalıdır.',
    'password' => [
        'letters' => ':attribute en az bir harf içermelidir.',
        'mixed' => ':attribute en az bir büyük harf ve bir küçük harf içermelidir.',
        'numbers' => ':attribute en az bir rakam içermelidir.',
        'symbols' => ':attribute en az bir sembol içermelidir.',
        'uncompromised' => 'Verilen :attribute bir veri sızıntısında göründü. Lütfen farklı bir :attribute seçin.',
    ],
    'present' => ':attribute mevcut olmalıdır.',
    'present_if' => ':attribute, :other :value olduğunda mevcut olmalıdır.',
    'present_unless' => ':attribute, :other :values içinde olmadıkça mevcut olmalıdır.',
    'present_with' => ':attribute, :values mevcut olduğunda mevcut olmalıdır.',
    'present_with_all' => ':attribute, :values mevcut olduğunda mevcut olmalıdır.',
    'prohibited' => ':attribute yasaktır.',
    'prohibited_if' => ':attribute, :other :value olduğunda yasaktır.',
    'prohibited_if_accepted' => ':attribute, :other kabul edildiğinde yasaktır.',
    'prohibited_if_declined' => ':attribute, :other reddedildiğinde yasaktır.',
    'prohibited_unless' => ':attribute, :other :values içinde olmadıkça yasaktır.',
    'prohibits' => ':attribute, :other değerinin mevcut olmasını yasaklar.',
    'regex' => ':attribute formatı geçersiz.',
    'required' => ':attribute alanı zorunludur.',
    'required_array_keys' => ':attribute şunlar için girdiler içermelidir: :values.',
    'required_if' => ':attribute, :other :value olduğunda zorunludur.',
    'required_if_accepted' => ':attribute, :other kabul edildiğinde zorunludur.',
    'required_if_declined' => ':attribute, :other reddedildiğinde zorunludur.',
    'required_unless' => ':attribute, :other :values içinde olmadıkça zorunludur.',
    'required_with' => ':attribute, :values mevcut olduğunda zorunludur.',
    'required_with_all' => ':attribute, :values mevcut olduğunda zorunludur.',
    'required_without' => ':attribute, :values mevcut olmadığında zorunludur.',
    'required_without_all' => ':attribute, :values hiçbiri mevcut olmadığında zorunludur.',
    'same' => ':attribute ile :other eşleşmelidir.',
    'size' => [
        'array' => ':attribute, :size öğe içermelidir.',
        'file' => ':attribute, :size kilobayt olmalıdır.',
        'numeric' => ':attribute, :size olmalıdır.',
        'string' => ':attribute, :size karakter olmalıdır.',
    ],
    'starts_with' => ':attribute şunlardan biriyle başlamalıdır: :values.',
    'string' => ':attribute bir metin olmalıdır.',
    'timezone' => ':attribute geçerli bir saat dilimi olmalıdır.',
    'unique' => ':attribute zaten alınmış.',
    'uploaded' => ':attribute yüklenemedi.',
    'uppercase' => ':attribute büyük harf olmalıdır.',
    'url' => ':attribute geçerli bir URL olmalıdır.',
    'ulid' => ':attribute geçerli bir ULID olmalıdır.',
    'uuid' => ':attribute geçerli bir UUID olmalıdır.',

    /*
    |--------------------------------------------------------------------------
    | Özel Doğrulama Dil Satırları
    |--------------------------------------------------------------------------
    |
    | Burada, "attribute.rule" kuralını kullanarak nitelikler için özel
    | doğrulama mesajları belirtebilirsiniz. Bu, belirli bir nitelik kuralı
    | için özel bir dil satırı belirtmeyi kolaylaştırır.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Özel Doğrulama Nitelikleri
    |--------------------------------------------------------------------------
    |
    | Aşağıdaki dil satırları, nitelik yer tutucularımızı "email" yerine
    | "E-Posta Adresi" gibi daha okunabilir bir şeyle değiştirmek için
    | kullanılır. Bu, mesajlarımızı daha anlaşılır hale getirir.
    |
    */

    'attributes' => [],

];
