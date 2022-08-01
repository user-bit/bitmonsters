$(document).ready(function () {
    setTimeout(function () {
        $('.modal-error').remove();
    }, 4000)
    $(document).on('click', '.modal-error__close', function () {
        $('.modal-error').remove();
    })
    setTimeout(function () {
        $('.modal-alert').remove();
    }, 4000)
    $(document).on('click', '.modal-alert__close', function () {
        $('.modal-alert').remove();
    })
});
