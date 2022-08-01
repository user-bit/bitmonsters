document.addEventListener('DOMContentLoaded', () => {
    var lazyLoadInstance = new LazyLoad({
        // Your custom settings go here
    });
    document.fonts.ready.then(function () {
        AOS.init({once: true,disable: 'mobile'});
        document.querySelector(".body-section").classList.add('active');
        var clickScroll = document.querySelectorAll('.anchor-link');
        clickScroll.forEach(function (click) {
            click.addEventListener('click', function (_ref) {
                document.querySelector('.header-section__menu').classList.remove('active')
                var currentTarget = _ref.currentTarget;
                event.preventDefault();
                var nameId = currentTarget.getAttribute('href');
                var topPage = document.querySelector(nameId).offsetTop;
                window.scrollTo({
                    top: topPage - 200,
                    behavior: "smooth"
                });
            });
        });
        options = {
            "cursorOuter": "circle-basic",
            "hoverEffect": "circle-move",
            "hoverItemMove": false,
            "defaultCursor": false,
            "outerWidth": 30,
            "outerHeight": 30
        };
        magicMouse(options);
        if (window.innerWidth > 1000) {
            let elements = document.querySelectorAll(".rolling-text");
            elements.forEach((element) => {
                let innerText = element.innerText;
                element.innerHTML = "";
                let textContainer = document.createElement("div");
                textContainer.classList.add("block");
                for (let letter of innerText) {
                    let span = document.createElement("span");
                    span.innerText = letter.trim() === "" ? "\xa0" : letter;
                    span.classList.add("letter");
                    textContainer.appendChild(span);
                }
                element.appendChild(textContainer);
                element.appendChild(textContainer.cloneNode(true));
            });
            elements.forEach((element) => {
                element.addEventListener("mouseover", () => {
                    element.classList.remove("play");
                });
            });
        }

        const formClose = document.querySelectorAll(".modal-close");
        formClose.forEach(function (close) {
            close.addEventListener('click', (event) => {
                const modalClose = document.querySelectorAll('.modal');
                modalClose.forEach(function (btn) {
                    btn.classList.remove("active");
                });
                document.querySelector("body").classList.remove("hidden");
                document.querySelector('.overflow').classList.remove("active");
                document.querySelector('.modal-thank').classList.remove("active");
            })
        });

        if (document.querySelector(".overflow") !== null) {
            document.querySelector(".overflow").addEventListener('click', (event) => {
                const modalClose = document.querySelectorAll('.modal');
                modalClose.forEach(function (btn) {
                    btn.classList.remove("active");
                });
                document.querySelector("body").classList.remove("hidden");
                document.querySelector('.overflow').classList.remove("active");
                document.querySelector('.modal-thank').classList.remove("active");
            })
        }

        document.querySelector(".header-section__menu-xs").addEventListener('click', (event) => {
            document.querySelector(".header-section__menu").classList.add('active');
        });
        document.querySelector(".burger").addEventListener('click', (event) => {
            document.querySelector(".header-section__menu").classList.remove('active');
        });
    });
});

window.addEventListener('scroll', function () {
    const scrolled = window.pageYOffset || document.documentElement.scrollTop;
    const scrollUpButton = document.querySelector('.header-section');
    if (scrolled >= 50) {
        scrollUpButton.classList.add('fixed');
    } else {
        scrollUpButton.classList.remove('fixed');
    }
});