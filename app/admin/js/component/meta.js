$(document).ready(function () {
    $(document).on('click', '.add-sitemap', function () {
        let id = $('input[name=id]').val();
        $.ajax({
            type: "POST",
            url: "/administrator-cms/ajax/meta/addSectionSitemap/",
            dataType: 'json',
            data: {id: id},
            cache: false,
            success: function (data) {
                $('.sitemap-section__content').append(data.content);
            },
            error: () => console.log('add section robots')
        });
    });
});