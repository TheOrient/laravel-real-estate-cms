@extends('layouts.app')

@section('title', __('faq.title'))
@section('meta_description', __('faq.description'))

@section('content')
<section class="relative pt-32 pb-20 bg-gradient-to-br from-[#1E6F5C] to-[#13493E]">
    <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-black/10 to-black/20"></div>
    <div class="container mx-auto px-6 relative z-10">
        <div class="max-w-3xl mx-auto text-center text-white">
            <h1 class="text-5xl font-bold mb-6" style="font-family: Inter, sans-serif;">{{ __('faq.title') }}</h1>
            <p class="text-xl text-white/90" style="font-family: Inter, sans-serif;">{{ __('faq.description') }}</p>
        </div>
    </div>
</section>

<section class="py-20">
    <div class="container mx-auto px-6">
        <div class="max-w-5xl mx-auto">
            <!-- Category Filters -->
            <div class="flex flex-wrap gap-3 justify-center mb-12">
                <a href="{{ route('faq.index', ['category' => 'all']) }}"
                   class="px-6 py-3 rounded-full text-sm font-semibold transition-all duration-300 whitespace-nowrap cursor-pointer {{ $selectedCategory === 'all' ? 'bg-[#1E6F5C] text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-50' }}"
                   style="font-family: Inter, sans-serif;">
                    {{ __('faq.all') }}
                </a>
                @foreach($categories as $category)
                    <a href="{{ route('faq.index', ['category' => $category->id]) }}"
                       class="px-6 py-3 rounded-full text-sm font-semibold transition-all duration-300 whitespace-nowrap cursor-pointer {{ $selectedCategory == $category->id ? 'bg-[#1E6F5C] text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-50' }}"
                       style="font-family: Inter, sans-serif;">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>

            <!-- FAQ Items -->
            <div class="space-y-4">
                @if($faqsByCategory->count() > 0)
                    @foreach($faqsByCategory as $categoryId => $categoryFaqs)
                        @foreach($categoryFaqs as $faq)
                            <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden faq-item" data-category="{{ $faq->faq_category_id }}">
                                <button class="w-full px-8 py-6 flex items-center justify-between text-left cursor-pointer hover:bg-gray-50 transition-colors duration-200 faq-toggle">
                                    <div class="flex items-start gap-4 flex-1">
                                        <div class="w-10 h-10 flex items-center justify-center bg-[#1E6F5C]/10 rounded-lg flex-shrink-0 mt-1">
                                            <i class="ri-question-line text-xl text-[#1E6F5C]"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-lg font-bold text-gray-900 mb-1" style="font-family: Inter, sans-serif;">
                                                {{ $faq->question }}
                                            </h3>
                                            <span class="inline-block px-3 py-1 bg-[#1E6F5C]/10 text-[#1E6F5C] text-xs font-semibold rounded-full" style="font-family: Inter, sans-serif;">
                                                {{ $faq->category->name }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="w-8 h-8 flex items-center justify-center transition-transform duration-300 faq-icon">
                                        <i class="ri-arrow-down-s-line text-2xl text-gray-400"></i>
                                    </div>
                                </button>
                                <div class="overflow-hidden transition-all duration-300 max-h-0 faq-content">
                                    <div class="px-8 pb-6 pl-20">
                                        <p class="text-gray-600 leading-relaxed" style="font-family: Inter, sans-serif;">
                                            {!! nl2br(e($faq->answer)) !!}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                @else
                    <div class="text-center py-12">
                        <i class="ri-question-line text-5xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-bold text-gray-700 mb-2" style="font-family: Inter, sans-serif;">{{ __('faq.no_faqs') }}</h3>
                        <p class="text-gray-500" style="font-family: Inter, sans-serif;">{{ __('faq.coming_soon') }}</p>
                    </div>
                @endif
            </div>

            <!-- Contact CTA -->
            <div class="mt-16 bg-gradient-to-br from-[#1E6F5C] to-[#13493E] rounded-2xl p-10 text-center text-white">
                <div class="w-16 h-16 flex items-center justify-center bg-white/20 rounded-full mx-auto mb-6">
                    <i class="ri-customer-service-2-line text-3xl"></i>
                </div>
                <h2 class="text-3xl font-bold mb-4" style="font-family: Inter, sans-serif;">{{ __('faq.question_not_answered') }}</h2>
                <p class="text-lg text-white/90 mb-8 max-w-2xl mx-auto" style="font-family: Inter, sans-serif;">
                    {{ __('faq.expert_help') }}
                </p>
                <a href="{{ route('pages.show', 'iletisim') }}"
                   class="inline-block px-8 py-4 bg-white text-[#1E6F5C] text-base font-bold rounded-xl hover:bg-gray-50 transition-all duration-300 whitespace-nowrap cursor-pointer"
                   style="font-family: Inter, sans-serif;">
                    {{ __('faq.contact_us') }}
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const faqToggles = document.querySelectorAll('.faq-toggle');

    faqToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const item = this.closest('.faq-item');
            const content = item.querySelector('.faq-content');
            const icon = item.querySelector('.faq-icon');

            // Close all other items
            faqToggles.forEach(otherToggle => {
                if (otherToggle !== toggle) {
                    const otherItem = otherToggle.closest('.faq-item');
                    const otherContent = otherItem.querySelector('.faq-content');
                    const otherIcon = otherItem.querySelector('.faq-icon');

                    otherContent.style.maxHeight = null;
                    otherIcon.classList.remove('rotate-180');
                }
            });

            // Toggle current item
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
                icon.classList.remove('rotate-180');
            } else {
                content.style.maxHeight = content.scrollHeight + "px";
                icon.classList.add('rotate-180');
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.faq-icon.rotate-180 {
    transform: rotate(180deg);
}
</style>
@endpush
