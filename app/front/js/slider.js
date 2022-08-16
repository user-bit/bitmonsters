document.addEventListener('DOMContentLoaded', () => {
    new Swiper(".slider-top__slider", {
        loop: false,
        slidesPerView: 1.1,
        watchOverflow: true,
        spaceBetween: 16,
        // preloadImages: false,
        // lazy: true,
        // momentumRatio: 1,
        // freeMode: true,
        navigation: {
            nextEl: ".slider-top__next",
            prevEl: ".slider-top__prev"
        },
        breakpoints: {
            1000: {slidesPerView: 1.7, spaceBetween: 30},
        }
    });
    new Swiper(".slider-bottom__slider", {
        loop: false,
        slidesPerView: 1.3,
        watchOverflow: true,
        spaceBetween: 16,
        // preloadImages: false,
        // momentumRatio: 1,
        // freeMode: true,
        // lazy: true,
        navigation: {
            nextEl: ".slider-bottom__next",
            prevEl: ".slider-bottom__prev"
        },
        breakpoints: {
            600: {slidesPerView: 2, spaceBetween: 30},
            1440: {slidesPerView: 3},
        }
    });
    new Swiper(".events__slide", {
        loop: false,
        slidesPerView: 1,
        initialSlide: 3,
        watchOverflow: true,
        spaceBetween: 10,
        // preloadImages: false,
        // momentumRatio: 1,
        // freeMode: true,
        // lazy: true,
        breakpoints: {
            575: {slidesPerView: 2.5, initialSlide: 2},
            1000: {slidesPerView: 4.5, initialSlide: 0},
            1165: {slidesPerView: 6.5, initialSlide: 0},
        }
    });
    new Swiper(".about__content", {
        loop: false,
        slidesPerView: 1.1,
        watchOverflow: true,
        spaceBetween: 30,
        // preloadImages: false,
        // momentumRatio: 1,
        // freeMode: true,
        lazy: true,
        breakpoints: {
            550: {slidesPerView: 2},
            1000: {slidesPerView: 3},
        },
        navigation: {
            nextEl: ".about__next",
            prevEl: ".about__prev"
        },
    });

    new Swiper(".roadmap__slide", {
            loop: false,
            slidesPerView: 2.4,
            watchOverflow: true,
            spaceBetween: 35,
            // momentumRatio: 1,
            // freeMode: true,
            breakpoints: {
                756: {slidesPerView: 4},
                1000: {slidesPerView: 8},
            },
            on: {
                init: function () {
                    startSliderRoad();
                },
            },
        });
        function startSliderRoad() {
            const widthSlide = document.querySelector('.roadmap__slide .swiper-slide').offsetWidth;
            const marginSlide = parseInt(window.getComputedStyle(document.querySelector('.roadmap__slide .swiper-slide')).marginRight);
            const widthAllElement = widthSlide + marginSlide;
            const widthActive = widthAllElement * document.querySelector('.roadmap-top__line').getAttribute('data-line');
            document.querySelector('.roadmap-top__line-active').style.width = widthActive + 'px';
        }

    new Swiper(".events__slide", {
        loop: false,
        slidesPerView: 1,
        initialSlide: 3,
        watchOverflow: true,
        spaceBetween: 10,
        breakpoints: {
            575: {slidesPerView: 2.5, initialSlide: 2},
            1000: {slidesPerView: 4.5, initialSlide: 0},
            1165: {slidesPerView: 6.5, initialSlide: 0},

        },
        on: {
            init: function () {
                startSliderEvent();
            },
        },
    });
    function startSliderEvent() {
        const widthSlide = document.querySelector('.events__slide .swiper-slide').offsetWidth;
        const marginSlide = parseInt(window.getComputedStyle(document.querySelector('.events__slide .swiper-slide')).marginRight);
        const widthAllElement = widthSlide + marginSlide;
        const widthActive = widthAllElement * document.querySelector('.events__line-active').getAttribute('data-line');
        document.querySelector('.events__line-active').style.width = widthActive + 'px';
    }
});

