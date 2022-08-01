$(document).ready(function () {
    $("input[name='price_basePrice[]'").bind('change keyup input click', function () {bindBasePrice($(this));});
    $("input[name='price_discount[]']").bind('change keyup input click', function () {bindDiscountPrice($(this))});
    $('#addprice').on('click', function () {
        var id = $("input[name=id]").val();
        var dataString = 'id=' + id;
        $.ajax({
            type: "POST",
            url: "/administrator-cms/ajax/product/addprice",
            dataType: 'json',
            data: dataString,
            cache: false,
            success: function (data) {
                var price_price = $("input[name='price_price[]']").val();
                var price_discount = $("input[name='price_discount[]']").val();
                var priceBase = $("input[name='price_basePrice[]']").val();

                $('#load_price').append(data.content);
                $("input[name='price_basePrice[]'").bind('change keyup input click', function () {bindBasePrice($(this));});
                $("input[name='price_discount[]']").bind('change keyup input click', function () {bindDiscountPrice($(this))});
            }
        });
        return false;
    });
    $(document).on('click', '.delprice', function () {
        var conf = confirm('Вы уверены что хотите удалить данную запись?');
        if (conf) {
            var id = $(this).attr('href');
            console.log(id)
            var dataString = 'id=' + id;
            $.ajax({
                type: "POST",
                url: "/administrator-cms/ajax/product/delprice",
                dataType: 'json',
                data: dataString,
                cache: false,
                success: function (data) {
                    $('#load-price').html(data.content);
                }
            });
        }
        return false;
    });
});

function bindBasePrice($this) {
    var id = $this.parent().parent().attr('id');
    var discount = $("#" + id).find("input[name='price_discount[]']").val();
    var price = $this.val();
    let ressult = price - (price * (discount / 100));
    $this.parents("#" + id).find("input[name='price_price[]']").val(ressult);
}

function bindDiscountPrice($this) {
    var id = $this.parent().parent().attr('id');
    var price = $("#" + id).find("input[name='price_basePrice[]']").val();
    var discount = $this.val();
    let ressult = price - (price * (discount / 100));
    $this.parents("#" + id).find("input[name='price_price[]']").val(ressult);
}


