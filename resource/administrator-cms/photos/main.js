$(document).ready(function () {
    $('.show-image-edit').on('click', function () {
        $(this).parents('.container-photo').find('.photo-edit').addClass('active');
        $(this).parents('.container-photo').find('.photo-edit-overflow').addClass('active');
    })
    $('.photo-edit-close').on('click', function () {
        $('.photo-edit').removeClass('active');
        $('.photo-edit-overflow').removeClass('active');
    })

    $(document).on("keyup", "input[name='photo_alt']", function () {
        var value = $(this).val();
        $("input[name='photo_title']").val(value);
    });
});