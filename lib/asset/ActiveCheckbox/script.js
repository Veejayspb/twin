$(document).ready(function () {
    $('input[type=checkbox].twin-active-checkbox').change(function () {
        let self = $(this);
        let url = $(this).data('url');
        let params = $(this).data('params');
        params.value = $(this).prop('checked') ? 1 : 0;

        $.post(url, params, function (response) {
            if (response.value !== undefined) {
                self.prop('checked', response.value);
            }
        }, 'json');
    });
});
