$(document).ready(function () {
    $(document).on('click', '.save-block-constructor', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var infoElements = $(this).parents('.section-info-item-prev').find('input[name*="info"]');
        var selectedElements = $(this).parents('.section-info-item-prev').find('input[name*="constructor"]');

        var dataString = $.merge(infoElements, selectedElements)
        $.ajax({
            type: "POST",
            url: "/administrator-cms/ajax/template/saveTemplate",
            dataType: 'json',
            data: dataString,
            cache: false,
            success: function (data) {
                $('.modal-constructor').removeClass('active');
                $('.modal-constructor-overflow').removeClass('active');
                $('.section-info').append(data.content)
                location.reload();
            }
        });
    });

    $('.del-block-constructor').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let this_id = $(this).data('id');
        let this_page = $(this).data('page');
        let this_table = $(this).data('table');

        $.confirm({
            title: 'Вы дествительно хотите удалить',
            content: '',
            type: 'red',
            columnClass: 'width-400',
            dragWindowGap: 15,
            typeAnimated: true,
            buttons: {
                tryAgain: {
                    text: 'Да',
                    btnClass: 'btn-red',
                    action: function(){
                        var dataString =
                            'id=' + this_id +
                            '&table=' + this_table +
                            '&page=' + this_page;
                        $.ajax({
                            type: "POST",
                            url: "/administrator-cms/ajax/template/removeTemplate",
                            dataType: 'json',
                            data: dataString,
                            cache: false,
                            success: function (data) {
                                $('.section-info').append(data.content)
                                location.reload();
                            }
                        });
                    }
                },
                close: function () {
                }
            }
        });
    });

    $('.add-block-constructor').on('click', function (e) {
        $('.add-field-form').find("input[name='id']").val($(this).data('id'));
        $('.add-field-form').find("input[name='table']").val($(this).data('table'));
        e.preventDefault();
        e.stopPropagation();
        $('body').addClass('active-modal');
        $('.modal-add-field').addClass('active');
        $('.modal-add-field-overflow').addClass('active');
    });
    $('.modal-add-field__close').on('click', function () {
        $('body').removeClass('active-modal');
        $('.modal-add-field').removeClass('active');
        $('.modal-add-field-overflow').removeClass('active');
    })
    $('.modal-add-field-overflow').on('click', function () {
        $('body').removeClass('active-modal');
        $('.modal-add-field').removeClass('active');
        $('.modal-add-field-overflow').removeClass('active');
    })
    $('.add-field-form').on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        dataString = $(this).serialize();
        $.ajax({
            type: "POST",
            url: "/administrator-cms/ajax/template/addField",
            dataType: 'json',
            data: dataString,
            cache: false,
            success: function (data) {
                location.reload();
            }
        });
    });

    $('.create-section-info__btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('body').addClass('active-modal');
        $('.modal-constructor').addClass('active');
        $('.modal-constructor-overflow').addClass('active');
        $('.modal-constructor__template').first().addClass('active');
    })
    $('.modal-constructor__close').on('click', function () {
        $('body').removeClass('active-modal');
        $('.modal-constructor').removeClass('active');
        $('.modal-constructor-overflow').removeClass('active');
        $('.modal-constructor__template').removeClass('active');
    })
    $('.modal-constructor-overflow').on('click', function () {
        $('body').removeClass('active-modal');
        $('.modal-constructor').removeClass('active');
        $('.modal-constructor-overflow').removeClass('active');
    })
    $('.show-detail').on('click', function () {
        $(this).toggleClass('active');
        $(this).parents('.section-info-item').find('.section-info-item__content').toggleClass('active');
    })

    $('.modal-constructor-tabs__item').on('click', function () {
        $('.modal-constructor-tabs__item').removeClass('active');
        $('.modal-constructor__template').removeClass('active');
        var this_id = $(this).data('id');
        $(this).addClass('active');

        $('.modal-constructor__template').each(function( index ) {
            if (this_id == $(this).data('id')) {
                $(this).addClass('active');
            }
        });
    })
});
