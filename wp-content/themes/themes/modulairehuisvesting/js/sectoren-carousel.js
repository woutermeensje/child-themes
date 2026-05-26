(function () {
    'use strict';

    var GAP = 14;

    // Aantal zichtbare items per schermgrootte
    function visibleCount() {
        var w = window.innerWidth;
        if (w <= 480) return 2;
        if (w <= 768) return 3;
        return 4;
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.mh-sectoren-carousel').forEach(function (carousel) {
            var wrapper = carousel.querySelector('.mh-sectoren-carousel__track-wrapper');
            var track   = carousel.querySelector('.mh-sectoren-carousel__track');
            var btnPrev = carousel.querySelector('.mh-sectoren-carousel__btn--prev');
            var btnNext = carousel.querySelector('.mh-sectoren-carousel__btn--next');

            if (!track || !wrapper) return;

            // Zet item-breedte op basis van wrapper zodat exact N items passen
            function setItemWidths() {
                var count = visibleCount();
                var width = Math.floor((wrapper.offsetWidth - GAP * (count - 1)) / count);
                track.querySelectorAll('.mh-sectoren-carousel__item').forEach(function (item) {
                    item.style.flex = '0 0 ' + width + 'px';
                });
            }

            setItemWidths();
            window.addEventListener('resize', setItemWidths, { passive: true });

            // Scroll-hoeveelheid = breedte van één item + gap, × aantal zichtbare items
            function scrollAmount() {
                var item = track.querySelector('.mh-sectoren-carousel__item');
                if (!item) return 300;
                return (item.offsetWidth + GAP) * visibleCount();
            }

            function updateButtons() {
                if (btnPrev) btnPrev.disabled = track.scrollLeft <= 1;
                if (btnNext) btnNext.disabled = track.scrollLeft >= track.scrollWidth - track.clientWidth - 1;
            }

            if (btnPrev) btnPrev.addEventListener('click', function () {
                track.scrollBy({ left: -scrollAmount(), behavior: 'smooth' });
            });

            if (btnNext) btnNext.addEventListener('click', function () {
                track.scrollBy({ left: scrollAmount(), behavior: 'smooth' });
            });

            track.addEventListener('scroll', updateButtons, { passive: true });
            updateButtons();

            // Drag-to-scroll (muis)
            var isDragging = false;
            var startX     = 0;
            var startScroll = 0;

            track.addEventListener('mousedown', function (e) {
                isDragging  = true;
                startX      = e.pageX - track.offsetLeft;
                startScroll = track.scrollLeft;
                track.classList.add('is-dragging');
            });

            document.addEventListener('mouseup', function () {
                isDragging = false;
                track.classList.remove('is-dragging');
            });

            track.addEventListener('mousemove', function (e) {
                if (!isDragging) return;
                e.preventDefault();
                var x = e.pageX - track.offsetLeft;
                track.scrollLeft = startScroll - (x - startX) * 1.4;
            });
        });
    });
})();
