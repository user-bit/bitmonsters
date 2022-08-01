$(document).ready(function () {
    $('.infoblock-select').change(function(e) {
        e.preventDefault();
        e.stopPropagation();
        let this_template = 'path=' + $(this).val();
        $.ajax({
            type: "POST",
            url: "/administrator-cms/ajax/infoblocks/getTemplate",
            dataType: 'json',
            data: this_template,
            cache: false,
            success: function (data) {
                $('.section-field-ajax').html(data)
            }
        });
    });
});