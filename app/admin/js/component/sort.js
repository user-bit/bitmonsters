$(document).ready(function () {
    // Сортировка таблицы
    $('.tb_sort').tableDnD({
        onDragClass: "hover"
    });
    $(document).on('mouseup', ".tb_sort .move", function () {
        var isLoadPrice = false;
        if ($("#load_price") > 0) {
            isLoadPrice = true;
        }
        sortA(isLoadPrice);
    });
});

function sortA(id) {
    var tb = $("#action").val();
    var tb2 = $("#action2").val();
    if (id == true) {
        tb2 = 'price';
        $('.price_config').remove();
    }
    var arr = $(".tb_sort").tableDnDSerialize();
    var dataString = 'arr=' + arr + '&tb=' + tb + '&tb2=' + tb2;
    $.ajax({
        type: "POST",
        url: "/administrator-cms/ajax/sort",
        dataType: 'json',
        data: dataString,
        cache: false,
        success: function (data) {
            $('body').append(data.message);
            autoHide();
        }
    });
}