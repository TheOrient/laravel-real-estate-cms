@props(['currentStep' => 1])

<div class="flex items-center space-x-4">
    <!-- Step 1: Category -->
    <div class="flex items-center">
        <div class="flex items-center text-sm">
            <span class="flex items-center justify-center w-8 h-8 {{ $currentStep >= 1 ? ($currentStep > 1 ? 'bg-primary' : 'bg-primary') : 'bg-gray-200' }} text-white rounded-full font-medium">
                @if($currentStep > 1)
                    <i class="ri-check-line"></i>
                @else
                    1
                @endif
            </span>
            <span class="ml-2 {{ $currentStep >= 1 ? ($currentStep > 1 ? 'text-primary' : 'text-primary') : 'text-gray-500' }} font-medium">Kategori</span>
        </div>
    </div>

    <!-- Step 2: Essentials -->
    <div class="flex items-center">
        <div class="w-8 border-t-2 {{ $currentStep > 1 ? ($currentStep > 2 ? 'border-primary' : 'border-primary') : 'border-gray-200' }}"></div>
        <div class="flex items-center text-sm">
            <span class="flex items-center justify-center w-8 h-8 {{ $currentStep >= 2 ? ($currentStep > 2 ? 'bg-primary' : 'bg-primary') : 'bg-gray-200' }} {{ $currentStep >= 2 ? 'text-white' : 'text-gray-500' }} rounded-full font-medium">
                @if($currentStep > 2)
                    <i class="ri-check-line"></i>
                @else
                    2
                @endif
            </span>
            <span class="ml-2 {{ $currentStep >= 2 ? ($currentStep > 2 ? 'text-primary' : 'text-primary') : 'text-gray-500' }} font-medium">Temel Bilgiler</span>
        </div>
    </div>

    <!-- Step 3: Attributes -->
    <div class="flex items-center">
        <div class="w-8 border-t-2 {{ $currentStep > 2 ? ($currentStep > 3 ? 'border-primary' : 'border-primary') : 'border-gray-200' }}"></div>
        <div class="flex items-center text-sm">
            <span class="flex items-center justify-center w-8 h-8 {{ $currentStep >= 3 ? ($currentStep > 3 ? 'bg-primary' : 'bg-primary') : 'bg-gray-200' }} {{ $currentStep >= 3 ? 'text-white' : 'text-gray-500' }} rounded-full font-medium">
                @if($currentStep > 3)
                    <i class="ri-check-line"></i>
                @else
                    3
                @endif
            </span>
            <span class="ml-2 {{ $currentStep >= 3 ? ($currentStep > 3 ? 'text-primary' : 'text-primary') : 'text-gray-500' }} font-medium">Özellikler</span>
        </div>
    </div>

    <!-- Step 4: Images -->
    <div class="flex items-center">
        <div class="w-8 border-t-2 {{ $currentStep > 3 ? 'border-primary' : 'border-gray-200' }}"></div>
        <div class="flex items-center text-sm">
            <span class="flex items-center justify-center w-8 h-8 {{ $currentStep >= 4 ? 'bg-primary' : 'bg-gray-200' }} {{ $currentStep >= 4 ? 'text-white' : 'text-gray-500' }} rounded-full font-medium">4</span>
            <span class="ml-2 {{ $currentStep >= 4 ? 'text-primary' : 'text-gray-500' }} font-medium">Fotoğraflar</span>
        </div>
    </div>
</div>
