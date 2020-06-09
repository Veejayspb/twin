$(document).ready(function () {
    $('select.twin-active-select').change(function () {
        let self = $(this);
        let url = $(this).data('url');
        let params = $(this).data('params');
        params.value = $(this).val();

        $.post(url, params, function (response) {
            if (response.value !== undefined) {
                self.val(response.value);
            }
        }, 'json');
    });
});
