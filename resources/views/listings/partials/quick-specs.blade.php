@php
    $attributeValues = $listing->attributeValues ?? collect();
    $customValues = $listing->customAttributeValues ?? collect();

    $findFromValues = function (array $needles, $inCustom = false) use ($attributeValues, $customValues) {
        $source = $inCustom ? $customValues : $attributeValues;

        foreach ($source as $item) {
            $attribute = $item->attribute ?? null;
            if (!$attribute) {
                continue;
            }

            $name = mb_strtolower($attribute->name);
            foreach ($needles as $needle) {
                if (str_contains($name, $needle)) {
                    return $item->value;
                }
            }
        }

        return null;
    };

    $sizeValue = $findFromValues(['metrekare', 'm²'], true);
    $roomValue = $findFromValues(['oda']);
    $floorValue = $findFromValues(['kat', 'floor'], true);
    $bathroomValue = $findFromValues(['banyo'], true);
@endphp

@if($sizeValue || $roomValue || $floorValue || $bathroomValue)
    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
        @if($sizeValue)
            <div class="flex items-center text-sm text-[#1A1A1A]/70">
                <i class="ri-ruler-line mr-1"></i>
                {{ is_numeric($sizeValue) ? number_format($sizeValue, 0) . ' m²' : $sizeValue }}
            </div>
        @endif

        @if($roomValue)
            <div class="flex items-center text-sm text-[#1A1A1A]/70">
                <i class="ri-door-open-line mr-1"></i>
                {{ $roomValue }}
            </div>
        @endif

        @if($floorValue)
            <div class="flex items-center text-sm text-[#1A1A1A]/70">
                <i class="ri-building-line mr-1"></i>
                {{ is_numeric($floorValue) ? $floorValue . '. Kat' : $floorValue }}
            </div>
        @endif

        @if($bathroomValue)
            <div class="flex items-center text-sm text-[#1A1A1A]/70">
                <i class="ri-drop-line mr-1"></i>
                {{ is_numeric($bathroomValue) ? $bathroomValue . ' Banyo' : $bathroomValue }}
            </div>
        @endif
    </div>
@endif
