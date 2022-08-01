$(document).ready(function () {
    $('.btn-save-close').click(function () {
        var action = '/administrator-cms/' + $('#action').val();
        $('form[name=update-form]').attr('action', action);
    });

});