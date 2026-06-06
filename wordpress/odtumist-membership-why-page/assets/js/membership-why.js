(function () {
    function initMembershipWhySlider(root) {
        if (document.body.classList.contains('elementor-editor-active')) {
            return;
        }

        var scope = root || document;
        var slides = Array.prototype.slice.call(scope.querySelectorAll('.odt-mwhy-slide'));
        if (!slides.length) {
            return;
        }

        var activeIndex = slides.findIndex(function (slide) {
            return slide.classList.contains('is-active');
        });

        if (activeIndex < 0) {
            activeIndex = 0;
        }

        function show(index) {
            activeIndex = (index + slides.length) % slides.length;
            slides.forEach(function (slide, currentIndex) {
                var isActive = currentIndex === activeIndex;
                slide.classList.toggle('is-active', isActive);
                slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
            });
        }

        slides.forEach(function (slide, index) {
            var prev = slide.querySelector('.odt-mwhy-prev .elementor-button');
            var next = slide.querySelector('.odt-mwhy-next .elementor-button');

            if (prev) {
                prev.setAttribute('aria-label', 'Önceki avantaj');
                prev.addEventListener('click', function (event) {
                    event.preventDefault();
                    show(index - 1);
                });
            }

            if (next) {
                next.setAttribute('aria-label', 'Sonraki avantaj');
                next.addEventListener('click', function (event) {
                    event.preventDefault();
                    show(index + 1);
                });
            }
        });

        show(activeIndex);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initMembershipWhySlider(document);
        });
    } else {
        initMembershipWhySlider(document);
    }
})();
