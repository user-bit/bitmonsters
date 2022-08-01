$(document).ready(function () {
    getProduct('id=' + $('.search-name').data('id'));
    $('.search-name').bind('change keyup input click', function () {
        if ($(this).val().length >= 3) {
            var id = $(this).data('id');
            var search = $(this).val();
            var dataString = 'id=' + id + '&search=' + search;
            getProduct(dataString);
        }
    });

    $(document).on("click", ".res-add-product", function () {
        var id = $(this).data('id');
        var order_id = $('.order-add-product-result__list').data('order');
        var dataString = 'id=' + id + '&order_id=' + order_id;
        $.ajax({
            type: "POST",
            url: "/administrator-cms/ajax/orders/orderproductview",
            dataType: 'json',
            data: dataString,
            cache: false,
            success: function (data) {
                $('#order_product').html(data.content);
            }
        });

    })
    $(document).on("click", ".send_sms_btn", function (e) {
        e.preventDefault();
        e.stopPropagation();
        const phone = $(this).data('phone');
        const text = $("textarea[name='send_sms']").val();
        const dataString = 'phone=' + phone + '&text=' + text;
        $.ajax({
            type: "POST",
            url: "/administrator-cms/ajax/orders/sendSmsAdmin",
            dataType: 'json',
            data: dataString,
            cache: false,
            success: function (data) {
                $.confirm({
                    title: 'СМС отправлена',
                    content: '',
                    type: 'blue',
                    columnClass: 'width-400',
                    dragWindowGap: 15,
                    typeAnimated: true,
                    buttons: {
                        tryAgain: {
                            text: 'OK',
                            btnClass: 'btn-blue',
                            action: function(){
                                location.reload();
                            }
                        },
                        close: function () {
                        }
                    }
                });
            }
        });
    })

    $(document).on("click", ".send_sms_ttn_btn", function (e) {
        e.preventDefault();
        e.stopPropagation();
        const phone = $(this).data('phone');
        const post_name = $("input[name='post_name']").val();
        const post_ttn = $("input[name='post_ttn']").val();
        console.log(post_ttn)
        const dataString = 'phone=' + phone + '&post_name=' + post_name + '&post_ttn=' + post_ttn;
        $.ajax({
            type: "POST",
            url: "/administrator-cms/ajax/orders/sendSmsTtnAdmin",
            dataType: 'json',
            data: dataString,
            cache: false,
            success: function (data) {
                $.confirm({
                    title: 'СМС отправлена',
                    content: '',
                    type: 'blue',
                    columnClass: 'width-400',
                    dragWindowGap: 15,
                    typeAnimated: true,
                    buttons: {
                        tryAgain: {
                            text: 'OK',
                            btnClass: 'btn-blue',
                            action: function(){
                                location.reload();
                            }
                        },
                        close: function () {
                        }
                    }
                });
            }
        });
    })
});

function getProduct($dataString) {
    $.ajax({
        type: "POST",
        url: "/administrator-cms/ajax/orders/searchProductName/",
        dataType: 'json',
        data: $dataString,
        cache: false,
        success: function (data) {
            $(".order-add-product__result").html(data.content).fadeIn();
        }
    });
}
