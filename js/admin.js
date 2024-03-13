$(document).ready(function() {

    if ($('.upload').length > 0) {
        /*
         * 作業対象を決定
         */
        var targets = [];
        $('.upload').each(function() {
            targets.push($(this).attr('id'));
        });

        /*
         * ファイルを選択してアップロード
         */
        $.each(targets, function(index, value) {
            (function(value) {
                var target = $('#' + value);

                target.upload({
                    url: target.data('upload'),
                    progress: function() {
                        target.find('p').html('アップロードしています。');
                    },
                    success: function(response) {
                        // トークンを更新
                        $('form input.token').val(response.values.token);
                        $('a.token').attr('data-token', response.values.token);

                        // 結果を表示
                        target.find('p').html('');

                        var date = new Date();
                        for (var i = 0; i < response.values.files.length; i++) {
                            target.find('p').append('<img src="' + response.values.files[i] + '&' + date.getTime() + '">');
                        }

                        target.find('ul').show();
                    },
                    error: function(message) {
                        // 結果を表示
                        target.find('p').html('<div class="warning">アップロードに失敗しました。' + message + '</div>');
                    },
                });
            })(value);
        });

        /*
         * ファイルをドラッグ＆ドロップしてアップロード
         */
        $(document).on('drop', function(e) {
            return false;
        }).on('dragover', function(e) {
            return false;
        });

        $.each(targets, function(index, value) {
            (function(value) {
                var target = $('#' + value);

                target.droparea({
                    form: target.closest('form').get(0),
                    url: target.data('upload'),
                    name: target.find('input[type=file]').attr('name'),
                    dragover: function() {
                        target.addClass('dragover');
                    },
                    dragleave: function() {
                        target.removeClass('dragover');
                    },
                    initialize: function() {
                        target.removeClass('dragover');
                        target.find('p').html('アップロードを開始します。');
                    },
                    progress: function(total, loaded, percent) {
                        target.find('p').html('アップロードしています。' + (total ? ' | ' + Math.round(total / 1024) + 'KB 中 ' + Math.round(loaded / 1024) + 'KB 読み込み | 進捗' + percent + '%' : ''));
                    },
                    success: function(response) {
                        // トークンを更新
                        $('form input.token').val(response.values.token);
                        $('a.token').attr('data-token', response.values.token);

                        // 結果を表示
                        target.find('p').html('');

                        var date = new Date();
                        for (var i = 0; i < response.values.files.length; i++) {
                            target.find('p').append('<img src="' + response.values.files[i] + '&' + date.getTime() + '">');
                        }

                        target.find('ul').show();
                    },
                    error: function(message) {
                        // 結果を表示
                        target.find('p').html('<div class="warning">アップロードに失敗しました。' + message + '</div>');
                    },
                });
            })(value);
        });

        /*
         * アップロードファイルを削除
         */
        var file_delete = function(key) {
            return function(e) {
                if (window.confirm('本当に削除してもよろしいですか？')) {
                    $.ajax({
                        type: 'post',
                        url: $(this).attr('href'),
                        cache: false,
                        data: '_type=json&_token=' + $(this).attr('data-token'),
                        dataType: 'json',
                        success: function(response) {
                            // トークンを更新
                            $('form input.token').val(response.values.token);
                            $('a.token').attr('data-token', response.values.token);

                            if (response.status == 'OK') {
                                // 結果を表示
                                $('#' + key + ' p img').attr('src', $('#' + key + ' p img').attr('src') + '&amp;' + new Date().getTime());
                                $('#' + key + ' ul').hide();
                            } else {
                                // 予期しないエラー
                                window.alert('予期しないエラーが発生しました。');
                            }
                        },
                        error: function(request, status, errorThrown) {
                            console.log(request);
                            console.log(status);
                            console.log(errorThrown);
                        }
                    });
                }

                return false;
            };
        };

        /*
         * ファイル選択欄を初期化
         */
        $.each(targets, function(index, value) {
            (function(value) {
                if ($('#' + value).length > 0) {
                    $('#' + value + ' ul').hide();
                    $('#' + value + '_delete').on('click', file_delete(value));
                }
            })(value);
        });

        $.ajax({
            type: 'get',
            url: $('form.validate').attr('action'),
            cache: false,
            data: '_type=json',
            dataType: 'json',
            success: function(response) {
                if (response.status == 'OK') {
                    $.each(response.files, function(key, value) {
                        if (value != null) {
                            // 必要な操作メニューを表示
                            $('#' + key + ' ul').show();
                        }
                    });
                }
            },
            error: function(request, status, errorThrown) {
                console.log(request);
                console.log(status);
                console.log(errorThrown);
            }
        });
    }

    /*
     * 日時入力
     */
    $.datetimepicker.setLocale('ja');
    $('input[name=public_begin], input[name=public_end], input[name=datetime]').datetimepicker({
        format: 'Y-m-d H:00'
    });

    /*
     * 公開
     */
    $('select[name=public]').on('change', function() {
        console.log($(this).val());
        if ($(this).val() == 1) {
            $('.for-public').show();
        } else {
            $('.for-public').hide();
        }
    });
    if ($('select[name=public]').val() == 1) {
        $('.for-public').show();
    } else {
        $('.for-public').hide();
    }

    /*
     * エディタ
     */
    if ($('.editor').length > 0) {
        document.querySelectorAll('.editor').forEach(function(editor) {
            ClassicEditor
                .create(editor, {
                    toolbar: [ 'heading', 'bold', 'italic', 'link', 'bulletedList', 'numberedList' ],
                    heading: {
                        options: [
                            { model: 'paragraph', title: '段落', class: 'ck-heading_paragraph' },
                            { model: 'heading4', view: 'h4', title: '見出し1', class: 'ck-heading_heading4' },
                            { model: 'heading5', view: 'h5', title: '見出し2', class: 'ck-heading_heading5' }
                        ]
                    }
                })
                .catch(error => {
                    console.error(error);
                });
        });
    }

    /*
     * 並び替え
     */
    $('#sortable table tbody').sortable({
        handle: 'span.handle',
        update: function(event, ui) {
            // 並び替え後の順番を取得
            var sort = [];
            $.each($('#sortable table tbody').sortable('toArray'), function(i) {
                this.match(/^sort_(\d+)$/);

                sort.push('sort[' + RegExp.$1 + ']=' + (i + 1));
            });

            // 登録情報を更新
            $.ajax({
                type: $('#sortable').attr('method'),
                url: $('#sortable').attr('action'),
                cache: false,
                data: '_type=json&_token=' + $('#sortable').find('input[name=_token]').val() + '&' + sort.join('&'),
                dataType: 'json',
                success: function(response) {
                    // トークンを更新
                    $('form input.token').val(response.values.token);
                    $('a.token').attr('data-token', response.values.token);

                    if (response.status != 'OK') {
                        // 予期しないエラー
                        window.alert(response.message);
                        window.location.reload();
                    }
                },
                error: function(request, status, errorThrown) {
                    console.log(request);
                    console.log(status);
                    console.log(errorThrown);

                    // window.location.reload();
                }
            });
        }
    });

    /*
     * 一括削除
     */
    $('form input.bulk').on('change', function() {
        // 削除対象を保持
        var data = {
            '_type': 'json',
            '_token': $('form.bulk input[name="_token"]').val(),
            'id': $(this).val(),
            'checked': $(this).prop('checked') ? 1 : 0
        };
        $.post($('form.bulk').attr('action'), data, function(response) {
            // トークンを更新
            $('form input.token').val(response.values.token);
            $('a.token').attr('data-token', response.values.token);

            if (response.status != 'OK') {
                // 予期しないエラー
                window.alert('予期しないエラーが発生しました。');
            }
        }, 'json');

        return false;
    });
    $('form input.bulks').on('change', function() {
        // 一括選択
        if ($(this).prop('checked')) {
            $('form input.bulks').prop('checked', true);
            $('form input.bulk').prop('checked', true);
        } else {
            $('form input.bulks').prop('checked', false);
            $('form input.bulk').prop('checked', false);
        }

        var list = {};
        $('form input.bulk').each(function() {
            list[$(this).val()] = $(this).prop('checked') ? 1 : 0;
        });

        // 削除対象を保持
        var data = {
            '_type': 'json',
            '_token': $('form.bulk input[name="_token"]').val(),
            'list': list
        };
        $.post($('form.bulk').attr('action'), data, function(response) {
            // トークンを更新
            $('form input.token').val(response.values.token);
            $('a.token').attr('data-token', response.values.token);

            if (response.status != 'OK') {
                // 予期しないエラー
                window.alert('予期しないエラーが発生しました。');
            }
        }, 'json');

        return false;
    });
    if ($('form input.bulk').length > 0) {
        // すべて選択済みなら一括選択にチェック
        var flag = true;
        $('form input.bulk').each(function() {
            if ($(this).prop('checked') == false) {
                flag = false;
            }
        });
        if (flag == true) {
            $('form input.bulks').prop('checked', true);
        }
    }

});
