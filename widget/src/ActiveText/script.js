$(document).ready(function () {

    let timer;

    $('span.twin-active-text').on('keyup blur', function () {
        let self = $(this);
        let url = $(this).data('url');
        let params = $(this).data('params');
        params.value = $(this).text();

        if (timer !== undefined) {
            clearTimeout(timer);
        }

        timer = setTimeout(function () {
            $.post(url, params, function (response) {
                if (response.value !== undefined) {
                    self.text(response.value);
                }
            }, 'json');
        }, 1000);
    });
});
