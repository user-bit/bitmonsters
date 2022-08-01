$(document).ready(function () {
    $(document).on('click', '.catalog-status', function () {
        $(this).parent().text().trim() == 'Вкл.' ? turn = 'Выключить' : turn = 'Включить';
        return confirm(turn + ' все товары из каталога?');
    });

    // Active
    $(document).on('click', '.active-status', function () {
        var tb = $("#action").val();
        var tb2 = $("#action2").val();
        var id = $(this).attr('id');
        var dataString = 'id=' + id + '&tb=' + tb + '&tb2=' + tb2;
        $.ajax({
            type: "POST",
            url: "/administrator-cms/ajax/active",
            dataType: 'json',
            data: dataString,
            cache: false,
            success: function (data) {
                if (!data.access) {
                    $('#' + id).html(data.active);
                }
                $('#message').html(data.message);
                autoHide();
                if (tb == 'modules') {
                    location.reload();
                }
            }
        });
    });
});
