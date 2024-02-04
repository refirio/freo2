(function($) {
    $.fn.upload = function(option) {
        var settings = $.extend({
            url: null,
            setup: function() {},
            initialize: function() {},
            success: function() {},
            error: function() {}
        }, option);

        var target = $(this);

        if (settings.url) {
            settings.setup();

            target.find('button').on('click', function() {
                target.find('input').click();
            });
            target.find('input[type=file]').on('change', function() {
                var formData = new FormData(target.closest('form').get(0));

                settings.progress();

                $.ajax({
                    type: 'post',
                    url: settings.url,
                    cache: false,
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.status == 'OK') {
                            settings.success(response);
                        } else {
                            settings.error(response.message);
                        }
                    },
                    error: function(request, status, errorThrown) {
                        console.log(request);
                        console.log(status);
                        console.log(errorThrown);

                        settings.error('error');
                    }
                });

                $(this).val('');
            });
        }

        return this;
    };
})(jQuery);
