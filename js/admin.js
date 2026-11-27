var text = null;

/*
 * メディアを本文に挿入
 */
function insertMedia(tag) {
    if (text == null) {
        return;
    }

    if (text && text.model) {
        // CKEditor
        text.model.change(writer => {
            var viewFragment = text.data.processor.toView(tag);
            var modelFragment = text.data.toModel(viewFragment);

            text.model.insertContent(modelFragment, text.model.document.selection);
        });
    } else {
        // テキストエリア
        var textarea = text.get(0);

        var start = textarea.selectionStart;
        var end   = textarea.selectionEnd;

        var before = textarea.value.substring(0, start);
        var after  = textarea.value.substring(end);

        textarea.value = before + tag + after;

        textarea.selectionStart = textarea.selectionEnd = start + tag.length;

        textarea.focus();
    }
}

/*
 * 入力中の内容の一時保存
 *
 * form.register[data-draft] のタイトルと本文を、記事ごとにlocalStorageへ保存する
 * キーは data-draft（app_draft_key()）、data-draft-modified は記事の更新日時
 */
var draftPrefix  = 'freo2_draft:';
var draftPending = 'freo2_draft_pending';
var draftExpire  = 7 * 24 * 60 * 60 * 1000;

/*
 * ストレージを取得（使えない環境ではnull）
 */
function draftStorage(name) {
    try {
        var storage = window[name];

        storage.getItem(draftPending);

        return storage;
    } catch (e) {
        return null;
    }
}

/*
 * 下書きを取得（無い・壊れている・期限切れならnull）
 */
function draftLoad(key) {
    var storage = draftStorage('localStorage');

    if (storage == null) {
        return null;
    }

    try {
        var draft = JSON.parse(storage.getItem(key));

        if (draft && typeof draft.title === 'string' && typeof draft.text === 'string' && draft.saved > Date.now() - draftExpire) {
            return draft;
        }
    } catch (e) {
    }

    return null;
}

/*
 * 登録が完了した下書きと、期限切れの下書きを削除
 */
function draftCleanup() {
    // メディア選択のiframeでは何もしない
    if (window.self !== window.top) {
        return;
    }

    var local   = draftStorage('localStorage');
    var session = draftStorage('sessionStorage');

    if (local == null || session == null) {
        return;
    }

    try {
        // 送信した下書きは、登録が完了した画面（?ok=post）でだけ削除する
        // 入力エラーなどで登録できなかった場合は、次に開いた画面で控えだけを消す
        var pending = session.getItem(draftPending);

        session.removeItem(draftPending);

        if (pending !== null && /[?&]ok=post(&|$)/.test(window.location.search)) {
            local.removeItem(pending);
        }

        for (var i = local.length - 1; i >= 0; i--) {
            var key = local.key(i);

            if (key.indexOf(draftPrefix) === 0 && draftLoad(key) === null) {
                local.removeItem(key);
            }
        }
    } catch (e) {
    }
}

/*
 * 一時保存を開始
 */
function draftStart(form) {
    if (form.length !== 1 || window.self !== window.top) {
        return;
    }

    var storage = draftStorage('localStorage');

    if (storage == null) {
        return;
    }

    var key      = form.attr('data-draft');
    var modified = form.attr('data-draft-modified');
    var title    = form.find('input[name=title]');
    var textarea = form.find('textarea[name=text]');
    var timer    = null;
    var unsaved  = false;
    var paused   = false;

    var getText = function() {
        if (textarea.length === 0) {
            return '';
        }

        var editor = textarea.data('editor');

        return editor ? editor.getData() : textarea.val();
    };
    var setText = function(value) {
        if (textarea.length === 0) {
            return;
        }

        var editor = textarea.data('editor');

        if (editor) {
            editor.setData(value);
        } else {
            textarea.val(value);
        }
    };
    var save = function() {
        clearTimeout(timer);
        timer = null;

        // 復元するかを選ぶまでは、前の下書きを上書きしない
        if (paused) {
            return;
        }

        try {
            storage.setItem(key, JSON.stringify({
                title: title.val(),
                text: getText(),
                text_type: form.find('[name=text_type]').val(),
                modified: modified,
                saved: Date.now()
            }));

            unsaved = false;
        } catch (e) {
        }
    };

    // 入力が止まってから保存する
    title.add(textarea).on('input change', function() {
        unsaved = true;

        clearTimeout(timer);
        timer = setTimeout(save, 1000);
    });

    // ページを離れる・隠れるときは、待たずに保存する
    $(window).on('pagehide', function() {
        if (unsaved) {
            save();
        }
    });
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden' && unsaved) {
            save();
        }
    });

    // 実際に送信されるときだけ、登録が完了したら削除するよう控える
    // 送信前の入力内容検証などで送信が取り消されると、ここまで伝わらない
    $(document).on('submit', 'form.register[data-draft]', function() {
        if ($(this).attr('target') === '_blank') {
            return;
        }

        if (unsaved) {
            save();
        }

        try {
            draftStorage('sessionStorage').setItem(draftPending, key);
        } catch (e) {
        }
    });

    // 前回の下書きを確認
    var draft = draftLoad(key);

    if (draft === null) {
        return;
    }
    if (draft.title === title.val() && draft.text === getText()) {
        storage.removeItem(key);

        return;
    }

    paused = true;

    var pad = function(value) {
        return ('0' + value).slice(-2);
    };
    var saved = new Date(draft.saved);
    var message = '入力中の内容があります（' + saved.getFullYear() + '/' + pad(saved.getMonth() + 1) + '/' + pad(saved.getDate()) + ' ' + pad(saved.getHours()) + ':' + pad(saved.getMinutes()) + '）。';

    // 新規登録は更新日時が無いので比べない
    if (!/:new$/.test(key) && draft.modified !== modified) {
        message += 'ただし、保存されている内容の方が新しくなっています。それでも復元しますか？';
    } else {
        message += '復元しますか？';
    }
    if (draft.text_type !== undefined && draft.text_type !== form.find('[name=text_type]').val()) {
        message += '本文形式が変わっているため、復元すると本文の表示が崩れることがあります。';
    }

    var notice = $('<div class="alert alert-warning draft"></div>');

    notice.append($('<p class="mb-2"></p>').text(message));
    notice.append('<button type="button" class="btn btn-warning btn-sm px-3 me-2 draft-restore">復元する</button>');
    notice.append('<button type="button" class="btn btn-secondary btn-sm px-3 draft-discard">破棄する</button>');

    form.before(notice);

    notice.find('.draft-restore').on('click', function() {
        notice.remove();
        paused = false;

        // 入力として扱う（入力破棄確認と、今の更新日時での保存のため）
        title.val(draft.title).trigger('input');
        setText(draft.text);
        textarea.trigger('input');

        return false;
    });
    notice.find('.draft-discard').on('click', function() {
        notice.remove();
        paused = false;

        storage.removeItem(key);

        // 選ぶまでの間に入力していれば、その内容を保存する
        if (unsaved) {
            save();
        }

        return false;
    });
}

$(document).ready(function() {

    /*
     * ファイルアップロード
     */
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
                            target.find('p').append('<img src="' + response.values.files[i].data + '&' + date.getTime() + '">');
                        }

                        target.find('p').show();
                        target.find('ul').show();
                    },
                    error: function(message) {
                        // 結果を表示
                        target.find('p').html('<div class="warning">アップロードに失敗しました。' + message + '</div>');
                        target.find('p').show();
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
                            target.find('p').append('<img src="' + response.values.files[i].data + '&' + date.getTime() + '">');
                        }

                        target.find('p').show();
                        target.find('ul').show();
                    },
                    error: function(message) {
                        // 結果を表示
                        target.find('p').html('<div class="warning">アップロードに失敗しました。' + message + '</div>');
                        target.find('p').show();
                    },
                });
            })(value);
        });

        /*
         * アップロードファイルを削除
         */
        var file_delete = function(key) {
            return function(e) {
                //if (window.confirm('本当に削除してもよろしいですか？')) {
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
                                $('#' + key + ' p').hide();
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
                //}

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
                    $('#' + value + ' p').hide();
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
                        if (value == null) {
                            // 画像欄を非表示
                            $('#' + key + ' p').hide();
                        } else {
                            // 必要な操作メニューを表示
                            $('#' + key + ' ul').show();
                            $('#' + key + ' p').show();
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
     * ファイル一括アップロード
     */
    if ($('#pictures').length > 0) {
        /*
         * ファイルを選択してアップロード
         */
        var target = $('#pictures');

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
                var date = new Date();
                for (var i = 0; i < response.values.files.length; i++) {
                    target.find('.pictures_result').append('<div class="file"><span class="handle"><img src="' + response.values.files[i].data + '&' + date.getTime() + '"></span><input type="hidden" name="picture_files[]" value="' + response.values.files[i].filename + '"><a href="#" class="remove">×</a></div>');
                }
            },
            error: function(message) {
                // 結果を表示
                target.find('.pictures_result').html('<div class="warning">アップロードに失敗しました。' + message + '</div>');
            },
        });

        /*
         * ファイルをドラッグ＆ドロップしてアップロード
         */
        $(document).on('drop', function(e) {
            return false;
        }).on('dragover', function(e) {
            return false;
        });

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
                target.find('.pictures_result').html('アップロードを開始します。');
            },
            progress: function(total, loaded, percent) {
                target.find('.pictures_result').html('アップロードしています。' + (total ? ' | ' + Math.round(total / 1024) + 'KB 中 ' + Math.round(loaded / 1024) + 'KB 読み込み | 進捗' + percent + '%' : ''));
            },
            success: function(response) {
                // 結果を表示
                var date = new Date();
                for (var i = 0; i < response.values.files.length; i++) {
                    target.find('.pictures_result').append('<div class="file"><span class="handle"><img src="' + response.values.files[i].data + '&' + date.getTime() + '"></span><input type="hidden" name="picture_files[]" value="' + response.values.files[i].filename + '"><a href="#" class="remove">×</a></div>');
                }
            },
            error: function(message) {
                // 結果を表示
                target.find('.pictures_result').html('<div class="warning">アップロードに失敗しました。' + message + '</div>');
            },
        });

        /*
         * ファイル選択欄を初期化
         */
        $.ajax({
            type: 'get',
            url: $('form.validate').attr('action'),
            cache: false,
            data: '_type=json',
            dataType: 'json',
            success: function(response) {
                if (response.status == 'OK') {
                    var date = new Date();
                    if (response.data.entry.pictures) {
                        $.each(response.data.entry.pictures.split("\n"), function(index, value) {
                            target.find('.pictures_result').append('<div class="file"><span class="handle"><img src="' + $('#pictures').data('show') + '&index=' + index + '&' + date.getTime() + '"></span><input type="hidden" name="picture_files[]" value="' + value + '"><a href="#" class="remove">×</a></div>');
                        });
                    }
                }
            },
            error: function(request, status, errorThrown) {
                console.log(request);
                console.log(status);
                console.log(errorThrown);
            }
        });

        /*
         * 並び替え
         */
        $('div.pictures_result').sortable({
            handle: 'span.handle'
        });

        /*
         * 「×」ボタンでファイルを削除
         */
        $(document).on('click', 'a.remove', function() {
            //if (window.confirm('本当に削除してもよろしいですか？')) {
                $(this).parent().remove();
            //}

            return false;
        });
    }

    /*
     * メディア一括アップロード
     */
    if ($('#medias').length > 0) {
        /*
         * ファイルを選択してアップロード
         */
        var target = $('#medias');

        target.upload({
            url: target.data('uploads'),
            progress: function() {
                target.find('p').html('アップロードしています。');
            },
            success: function(response) {
                // トークンを更新
                $('form input.token').val(response.values.token);
                $('a.token').attr('data-token', response.values.token);

                // 結果を表示
                target.find('p').html('');

                for (var i = 0; i < response.values.files.length; i++) {
                    target.find('p').append('<div class="file">' + response.values.files[i] + '</div>');
                }
            },
            error: function(message) {
                // 結果を表示
                target.find('p').html('<div class="warning">アップロードに失敗しました。' + message + '</div>');
            },
        });

        /*
         * ファイルをドラッグ＆ドロップしてアップロード
         */
        $(document).on('drop', function(e) {
            return false;
        }).on('dragover', function(e) {
            return false;
        });

        target.droparea({
            form: target.closest('form').get(0),
            url: target.data('uploads'),
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
                // 結果を表示
                target.find('p').html('');

                for (var i = 0; i < response.values.files.length; i++) {
                    target.find('p').append('<div class="file">' + response.values.files[i] + '<input type="hidden" name="temps[]" value="' + response.values.files[i] + '"></div>');
                }
            },
            error: function(message) {
                // 結果を表示
                target.find('p').html('<div class="warning">アップロードに失敗しました。' + message + '</div>');
            },
        });
    }

    /*
     * 日時入力
     */
    $.datetimepicker.setLocale('ja');
    $('input[name=public_begin], input[name=public_end], input[name=datetime], input[name=attribute_begin], input[name=attribute_end]').datetimepicker({
        format: 'Y-m-d H:i',
        step: 10
    });

    /*
     * 公開
     */
    $('select[name=public]').on('change', function() {
        if ($(this).val() == 'none') {
            $('.for-public').hide();
            $('.for-password').hide();
        } else if ($(this).val() == 'password') {
            $('.for-public').show();
            $('.for-password').show();
        } else {
            $('.for-public').show();
            $('.for-password').hide();
        }
    });
    if ($('select[name=public]').val() == 'none') {
        $('.for-public').hide();
        $('.for-password').hide();
    } else if ($('select[name=public]').val() == 'password') {
        $('.for-public').show();
        $('.for-password').show();
    } else {
        $('.for-public').show();
        $('.for-password').hide();
    }

    /*
     * 本文
     */
    var editors = [];

    if ($('.editor').length > 0) {
        // CKEditor
        document.querySelectorAll('.editor').forEach(function(editor) {
            editors.push(ClassicEditor
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
                .then(instance => {
                    if (text == null) {
                        text = instance;
                    }

                    // 入力中の内容の一時保存で使う
                    $(editor).data('editor', instance);

                    // 変更を元のtextareaのinputイベントとして通知する（入力破棄確認・一時保存のため）
                    instance.model.document.on('change:data', function() {
                        $(editor).trigger('input');
                    });
                })
                .catch(error => {
                    console.error(error);
                }));
        });
    } else {
        // テキストエリア
        text = $('textarea[name=text]');
    }

    /*
     * 入力中の内容の一時保存
     */
    draftCleanup();

    // CKEditorの準備ができてから、本文を比べる（HTMLの書き方をそろえるため）
    Promise.all(editors).then(function() {
        draftStart($('form.register[data-draft]'));
    });

    /*
     * メディアを本文に挿入
     */
    $('.insert-media').on('click', function () {
        var mediaUrl     = $(this).data('url');
        var mediaName    = $(this).data('name');
        var thumbnailUrl = $(this).data('thumbnail');

        var extension = mediaName.split('.').pop().toLowerCase();

        var mediaTag;
        if (thumbnailUrl) {
            // サムネイルを表示し、クリックでオリジナルを表示する
            mediaTag = '<a href="' + mediaUrl + '" target="_blank"><img src="' + thumbnailUrl + '" alt="' + mediaName + '"></a>';
        } else if (['png', 'jpeg', 'jpg', 'jpe', 'gif'].includes(extension)) {
            mediaTag = '<img src="' + mediaUrl + '" alt="' + mediaName + '">';
        } else {
            mediaTag = '<a href="' + mediaUrl + '" target="_blank">' + mediaName + '</a>';
        }

        window.parent.insertMedia(mediaTag);
    });

    /*
     * 権限
     */
    $('select[name=authority_id]').on('change', function() {
        if ($('input[name="attribute_sets[]"]').length > 0 && $(this).find('option:selected').text() == 'ゲスト') {
            $('.for-authority_id').show();
        } else {
            $('.for-authority_id').hide();
        }
    });
    if ($('input[name="attribute_sets[]"]').length > 0 && $('select[name=authority_id] option:selected').text() == 'ゲスト') {
        $('.for-authority_id').show();
    } else {
        $('.for-authority_id').hide();
    }

    /*
     * 種類
     */
    $('select[name=kind]').on('change', function() {
        if ($(this).val() == 'select' || $(this).val() == 'radio' || $(this).val() == 'checkbox') {
            $('.for-kind').show();
        } else {
            $('.for-kind').hide();
        }
    });
    if ($('select[name=kind]').val() == 'select' || $('select[name=kind]').val() == 'radio' || $('select[name=kind]').val() == 'checkbox') {
        $('.for-kind').show();
    } else {
        $('.for-kind').hide();
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
        },
        helper: function(e, tr) {
            var originals = tr.children();
            var helper = tr.clone();
            helper.children().each(function(index) {
                $(this).width(originals.eq(index).width());
            });
            return helper;
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

    /*
     * HTML出力の案内
     *
     * 登録・編集などの完了メッセージ（無ければ本文の先頭）の後ろに出す。案内の中身は footer.php が template に入れる
     */
    if ($('template#export_notice').length > 0) {
        var notice  = $($('template#export_notice').html());
        var success = $('main div.alert-success').first();

        if (success.length > 0) {
            success.after(notice);
        } else {
            $('main').first().prepend(notice);
        }
    }

});
