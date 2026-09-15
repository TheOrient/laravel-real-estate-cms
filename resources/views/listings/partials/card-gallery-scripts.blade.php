@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const galleries = {};

    document.querySelectorAll('[data-gallery-id]').forEach(container => {
        const galleryId = container.getAttribute('data-gallery-id');
        const images = Array.from(container.querySelectorAll('[data-gallery-image="' + galleryId + '"]'));
        const dots = Array.from(container.querySelectorAll('[data-gallery-dot="' + galleryId + '"]'));

        if (!images.length) return;

        galleries[galleryId] = { current: 0, images, dots };

        const showImage = (id, index) => {
            const gallery = galleries[id];
            if (!gallery) return;

            gallery.images.forEach((img, i) => {
                if (i === index) {
                    if (!img.getAttribute('src') && img.dataset.src) {
                        img.src = img.dataset.src;
                        delete img.dataset.src;
                    }
                    img.classList.remove('opacity-0', 'pointer-events-none');
                    img.classList.add('opacity-100');
                } else {
                    img.classList.add('opacity-0', 'pointer-events-none');
                    img.classList.remove('opacity-100');
                }
            });

            gallery.dots.forEach((dot, i) => {
                if (i === index) {
                    dot.classList.remove('bg-white/50', 'w-2');
                    dot.classList.add('bg-white', 'w-6');
                } else {
                    dot.classList.add('bg-white/50', 'w-2');
                    dot.classList.remove('bg-white', 'w-6');
                }
            });

            gallery.current = index;
        };

        container.querySelectorAll('.listing-gallery-prev').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const id = this.getAttribute('data-gallery-target');
                const gallery = galleries[id];
                if (!gallery) return;
                const next = (gallery.current - 1 + gallery.images.length) % gallery.images.length;
                showImage(id, next);
            });
        });

        container.querySelectorAll('.listing-gallery-next').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const id = this.getAttribute('data-gallery-target');
                const gallery = galleries[id];
                if (!gallery) return;
                const next = (gallery.current + 1) % gallery.images.length;
                showImage(id, next);
            });
        });

        container.querySelectorAll('[data-gallery-dot="' + galleryId + '"]').forEach(dot => {
            dot.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const index = parseInt(this.getAttribute('data-gallery-index'), 10) || 0;
                showImage(galleryId, index);
            });
        });
    });
});
</script>
@endpush
