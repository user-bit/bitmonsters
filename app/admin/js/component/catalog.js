$(document).on('click', '.add-header-info', function () {
    let id = $(this).data('type')
    let catalog_id = $('input[name=id]').val();
    $.ajax({
        type: "POST",
        url: "/administrator-cms/ajax/catalog/addInfo/",
        dataType: 'json',
        data: {id: id, catalog_id: catalog_id},
        cache: false,
        success: function (data) {
            $('.header-info-content_'+data.content_id).append(data.content);
        },
        error: () => console.log('addInfo ERROR')
    });
});
$(document).on('click', '.save-info', function (e) {
    e.preventDefault();
    e.stopPropagation();
    const info_id = $(this).parents('.header-info-content__info').find('.page_info_id').val();
    const info_link = $(this).parents('.header-info-content__info').find('.page_info_title').val();
    const info_name = $(this).parents('.header-info-content__info').find('.page_info_desc').val();
    $.ajax({
        type: "POST",
        url: "/administrator-cms/ajax/catalog/saveInfo/",
        dataType: 'json',
        data: {info_id: info_id, info_link: info_link, info_name: info_name},
        cache: false,
        success: function (data) {
            console.log(data)
        },
        error: () => console.log('faq ERROR')
    });
});
$(document).on('click', '.del-info-item', function (e) {
    e.preventDefault();
    e.stopPropagation();
    let id = $(this).parents('.header-info-content__info').find('.page_info_id').val();
    $.ajax({
        type: "POST",
        url: "/administrator-cms/ajax/catalog/delInfo/",
        dataType: 'json',
        data: {id: id},
        cache: false,
        success: function (data) {
            $('.header-info-content-' + data).remove();
        },
        error: () => console.log('faq ERROR')
    });
});