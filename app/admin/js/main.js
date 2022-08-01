$(document).ready(function () {
    // Откывание языков
    $('.lang').on('click', function () {
        $('.lang__content').toggleClass('active');
    });
    // Откывание языков - END

    // Откывание под-меню
    $('.menu-item__title').on('click', function () {
        $(this).toggleClass('active');
        $(this).parents('.menu-item').find('.menu-item__item').slideToggle();
    });
    // Откывание под-меню - END

    // Откывание меню справа
    $('.settings').on('click', function () {
        $('.right-menu-main').addClass('active');
    })
    $('.right-menu-main__close').on('click', function () {
        $('.right-menu-main').removeClass('active');
    })
    // Откывание меню справа - END

    $('.user-main__down').on('click', function () {
        $('.user-main__dropdown').toggleClass('active');
    });

    $(document).on('click', '.custom-control-label-all', function () {
        if ($('.custom-control-label').prop('checked'))$('.custom-control-label').prop('checked', false);
        else $('.custom-control-label').prop('checked', true);
    });

    // Open|Close modal window
    $('.modal-open').on('click', function (e) {
        e.preventDefault();
        $attr = $(this).data('open');
        console.log($attr)
        $($attr).addClass('active');
        $('.overflow').addClass('active');
        $('body').addClass('body-modal');
    })
    $('.overflow').on('click', function () {
        $('.overflow').removeClass('active');
        $('body').removeClass('body-modal');
        $('.modal').removeClass('active');
    })
    $(document).on('click', '.modal-close', function () {
        $('.overflow').removeClass('active');
        $('body').removeClass('body-modal');
        $('.modal').removeClass('active');
    })
    // Open|Close modal window - END



    $('.nav__item').on('click', function () {
        $('.nav__item').removeClass('active');
        $('.tabs').addClass('hidden');
        $(this).addClass('active');
        let open_section = '.tabs-section-'+$(this).data('open');
        $(open_section).removeClass('hidden');
        $.cookie('active_tab_content', open_section);
        $.cookie('active_tab', $(this).data('open'));
    })
    let tab_content = $.cookie('active_tab_content');
    let tab_item = $.cookie('active_tab');
    if ($(tab_content).length > 0) {
        $('.nav__item').removeClass('active');
        $('.nav__item[data-open="'+tab_item+'"]').addClass('active');
        $('.tabs').addClass('hidden');
        $(tab_content).removeClass('hidden');
    }


    $('.custom-multi-select').multiSelect({
        selectableHeader: "<input type='text' class='form-control' autocomplete='off' placeholder='Поиск'>",
        afterInit: function(ms){
            var that = this,
                $selectableSearch = that.$selectableUl.prev(),
                $selectionSearch = that.$selectionUl.prev(),
                selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
                selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected';

            that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
                .on('keydown', function(e){
                    if (e.which === 40){
                        that.$selectableUl.focus();
                        return false;
                    }
                });
        },
        afterSelect: function(){
            this.qs1.cache();
        },
        afterDeselect: function(){
            this.qs1.cache();
        }
    })
    $('.open-overflow-content').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('.section__overflow').toggleClass('active');
    });

    $('.import-file__from').on('submit', function () {
        $('.body-loader').addClass('active');
    });

    $('.import_cat_item').on('click', function () {
        if ($(this).prop('checked')) $('.import_cat_all').prop('checked', false);
    })
    $('.import_cat_all').on('click', function () {
        if ($(this).prop('checked')) $('.import_cat_item').prop('checked', false);
    })
});


function autoHide() {
    setTimeout(function () {
        $('.modal-error').remove();
    }, 4000)
    setTimeout(function () {
        $('.modal-alert').remove();
    }, 4000)
}
