window.addEventListener("load", function () {
    const btnFeedback = document.querySelectorAll(".form-feedback");
    btnFeedback.forEach(function (feedback) {
        feedback.addEventListener('submit', ({currentTarget}) => {
            event.preventDefault();
            const formData = new FormData(currentTarget.closest('form'));
            fetch("/ajax/feedback/feedback/",
                {
                    method: "POST",
                    body: formData,
                })
                .then(function (res) {
                    return res.json();
                })
                .then(function (data) {
                    const formReset = document.querySelectorAll(".form-feedback");
                    formReset.forEach(function (reset) {
                        reset.reset();
                    });
                    document.querySelector(".modal-thank__title").innerHTML = data.title;
                    document.querySelector(".modal-thank__desc").innerHTML = data.desc;
                    document.querySelector('.overflow').classList.add("active");
                    document.querySelector(".modal-thank").classList.add('active');
                })
        });
    });
});