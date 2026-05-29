/**
 * Carrusel automático: transiciones suaves, pausa al hover y soporte táctil.
 */
(function () {
    'use strict';

    var root = document.querySelector('.venue-carousel');
    if (!root) return;

    var slides = root.querySelectorAll('.carousel-slide');
    var dots = root.querySelectorAll('.carousel-dot');
    var prev = document.getElementById('carouselPrev');
    var next = document.getElementById('carouselNext');
    var progress = document.getElementById('carouselProgress');
    var total = slides.length;
    if (total === 0) return;

    var current = 0;
    var timer = null;
    var intervalMs = 5500;
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function goTo(index) {
        current = (index + total) % total;
        slides.forEach(function (slide, i) {
            var active = i === current;
            slide.classList.toggle('is-active', active);
            slide.setAttribute('aria-hidden', active ? 'false' : 'true');
        });
        dots.forEach(function (dot, i) {
            var active = i === current;
            dot.classList.toggle('is-active', active);
            dot.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        if (progress) {
            progress.style.width = '0';
            if (!reducedMotion) {
                requestAnimationFrame(function () {
                    progress.style.width = '100%';
                });
            }
        }
    }

    function nextSlide() {
        goTo(current + 1);
    }

    function prevSlide() {
        goTo(current - 1);
    }

    function startAuto() {
        clearInterval(timer);
        if (reducedMotion || total < 2) return;
        timer = setInterval(nextSlide, intervalMs);
    }

    function stopAuto() {
        clearInterval(timer);
        timer = null;
    }

    root.addEventListener('mouseenter', stopAuto);
    root.addEventListener('mouseleave', startAuto);
    root.addEventListener('focusin', stopAuto);
    root.addEventListener('focusout', function (e) {
        if (!root.contains(e.relatedTarget)) startAuto();
    });

    if (prev) prev.addEventListener('click', function () { prevSlide(); startAuto(); });
    if (next) next.addEventListener('click', function () { nextSlide(); startAuto(); });

    dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
            goTo(parseInt(dot.getAttribute('data-index'), 10));
            startAuto();
        });
    });

    /* Navegación táctil */
    var touchStartX = 0;
    root.addEventListener('touchstart', function (e) {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    root.addEventListener('touchend', function (e) {
        var diff = e.changedTouches[0].screenX - touchStartX;
        if (Math.abs(diff) < 50) return;
        if (diff < 0) nextSlide();
        else prevSlide();
        startAuto();
    }, { passive: true });

    goTo(0);
    startAuto();
    if (progress && !reducedMotion) {
        progress.style.transition = 'width ' + intervalMs + 'ms linear';
    }
})();
