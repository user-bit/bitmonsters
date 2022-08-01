$(document).ready(function () {
    $('.add-comment').on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        dataString = $(this).serialize();
        $.ajax({
            type: "POST",
            url: "/administrator-cms/ajax/comments/addCommentsAdmin",
            dataType: 'json',
            data: dataString,
            cache: false,
            success: function (data) {
                location.reload();
            }
        });
    });
});