(function($) {
    $.fn.droparea = function(option) {
        var settings = $.extend({
            form: null,
            url: null,
            name: null,
            setup: function() {},
            dragover: function() {},
            dragleave: function() {},
            initialize: function() {},
            progress: function() {},
            success: function() {},
            error: function() {}
        }, option);

        var target = $(this);

        if (window.File && settings.url && settings.name) {
            settings.setup();

            target.on('dragover', function(e) {
                e.originalEvent.dataTransfer.dropEffect = 'copy';
                settings.dragover();

                return false;
            }).on('dragleave', function(e) {
                settings.dragleave();

                return false;
            }).on('drop', function(e) {
                settings.initialize();

                var formData = settings.form ? new FormData($(settings.form).get(0)) : new FormData();
                for (var i = 0; i < e.originalEvent.dataTransfer.files.length; i++) {
                    formData.append(settings.name, e.originalEvent.dataTransfer.files[i]);
                }

                $.ajax({
                    type: 'post',
                    url: settings.url,
                    cache: false,
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    xhr: function() {
                        var xhr = $.ajaxSettings.xhr();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                settings.progress(e.total, e.loaded, Math.round((e.loaded / e.total) * 100));
                            } else {
                                settings.progress(0, 0, 0);
                            }
                        })
                        return xhr;
                    },
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

                return false;
            });
        }

        return this;
    };
})(jQuery);
