$(document).ready(function () {
//Добавление|удаление FAQ in Meta
    $(document).on('click', '.add-faq', function () {
        let id = $('input[name=id]').val();
        $.ajax({
            type: "POST",
            url: "/administrator-cms/ajax/meta/addFaq/",
            dataType: 'json',
            data: {id: id},
            cache: false,
            success: function (data) {
                $('.faq-result').append(data.content);
            },
            error: () => console.log('otherproduct ERROR')
        });
    });
    $(document).on('click', '.del-faq-item', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let id = $(this).parents('.faq-meta').find('.meta_id').val();
        $.ajax({
            type: "POST",
            url: "/administrator-cms/ajax/meta/delFaq/",
            dataType: 'json',
            data: {id: id},
            cache: false,
            success: function (data) {
                console.log(data)
                $('.faq-del-' + data).remove();
            },
            error: () => console.log('faq ERROR')
        });
    });
//Добавление|удаление FAQ in Meta - END
});