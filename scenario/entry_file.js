/* テスト用エントリー（test オブジェクトはページごとに作り直されるため、値は定数で持つ） */
var scenarioEntry = {
    code:      'scenario-file',
    title:     'シナリオのファイル',
    text:      '<p>シナリオのファイルの本文です。</p>',
    pictures:  ['scenario-picture1.png', 'scenario-picture2.png'],
    thumbnail: 'scenario-thumbnail.png'
};

/* エントリー一覧から、コードで対象の行を取得する */
var entryRow = function(code) {
    return $('table tbody tr').filter(function() {
        return $(this).find('td code').text().trim() === code;
    });
};

/* 本文を入力してからコールバックを実行する（WYSIWYGエディタは初期化を待つ） */
var fillText = function(html, callback) {
    var textarea = $('form.register textarea[name="text"]');

    if (textarea.length === 0) {
        return test.abort('本文の入力欄が見つかりません。本文形式が「なし」になっている可能性があります。');
    }
    if (!textarea.hasClass('editor')) {
        textarea.val(html);

        return callback();
    }

    return test.wait(function() {
        return window.text != null && typeof window.text.setData === 'function';
    }, function() {
        window.text.setData(html);

        callback();
    }, 'WYSIWYGエディタ（CKEditor）が初期化されませんでした。CDNから読み込めているか確認してください。');
};

/* PNG画像のファイルを作る（canvas で塗りつぶした画像） */
var makePng = function(name, color, callback) {
    var canvas = document.createElement('canvas');

    canvas.width  = 16;
    canvas.height = 16;

    var context = canvas.getContext('2d');

    context.fillStyle = color;
    context.fillRect(0, 0, 16, 16);

    canvas.toBlob(function(blob) {
        callback(new File([blob], name, {type: 'image/png'}));
    }, 'image/png');
};

/* ファイル選択欄にファイルを入れて、選んだときと同じように change を起こす（アップロードは admin.js が行う） */
var selectFiles = function(input, files) {
    var transfer = new DataTransfer();

    for (var i = 0; i < files.length; i++) {
        transfer.items.add(files[i]);
    }

    input.files = transfer.files;
    $(input).trigger('change');
};

/* 画像がすべて読み込めたか（壊れた画像やダミーの画像ではないこと） */
var imagesLoaded = function(images) {
    if (images.length === 0) {
        return false;
    }

    for (var i = 0; i < images.length; i++) {
        if (!images[i].complete || images[i].naturalWidth !== 16) {
            return false;
        }
    }

    return true;
};

test.scenario = [
    // 初期ページからログインページに移動
    function() {
        test.assertExists('a:contains("ログイン")', '初期ページにログインへのリンクがありません。');
        test.click('a:contains("ログイン")');
    },
    // 管理者用ページにログイン
    function() {
        var form = $('form:eq(0)');

        test.assert(form.find('input[name="username"]').length === 1, 'ログインフォームが表示されていません。');

        form.find('input[name="username"]').val('admin');
        form.find('input[name="password"]').val('abcd1234');
        test.click('form:eq(0) button[type="submit"]');
    },
    // ログインできたことを確認してエントリー管理ページに移動
    function() {
        test.assertText('body', '管理者さん', 'ログインできていません。');
        test.click('a:contains("エントリー管理")');
    },
    // 前回のテストデータが残っていないことを確認してエントリー登録ページに移動
    function() {
        test.assert(entryRow(scenarioEntry.code).length === 0, 'エントリー ' + scenarioEntry.code + ' が残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("エントリー登録")');
    },
    // 画像2枚とサムネイルをアップロードし、プレビューが出たら登録する
    function() {
        var form = $('form.register');

        test.assert(form.find('input[name="code"]').length === 1, 'エントリー登録フォームが表示されていません。');
        test.assert(form.find('input[name="pictures[]"]').length === 1, '画像の入力欄がありません。設定「エントリーの画像」が無効になっている可能性があります。');
        test.assert(form.find('input[name="thumbnail"]').length === 1, 'サムネイルの入力欄がありません。設定「エントリーのサムネイル」が無効になっている可能性があります。');

        makePng(scenarioEntry.pictures[0], '#ff0000', function(picture1) {
            makePng(scenarioEntry.pictures[1], '#00ff00', function(picture2) {
                // 画像（一括アップロード）
                selectFiles(form.find('input[name="pictures[]"]')[0], [picture1, picture2]);

                test.wait(function() {
                    return imagesLoaded($('#pictures .pictures_result img')) && $('#pictures .pictures_result img').length === 2;
                }, function() {
                    test.assert($('#pictures input[name="picture_files[]"]').length === 2, 'アップロードした画像が選択されていません。');

                    makePng(scenarioEntry.thumbnail, '#0000ff', function(thumbnail) {
                        // サムネイル
                        selectFiles(form.find('input[name="thumbnail"]')[0], [thumbnail]);

                        test.wait(function() {
                            return imagesLoaded($('#thumbnail p img'));
                        }, function() {
                            form.find('input[name="code"]').val(scenarioEntry.code);
                            form.find('input[name="title"]').val(scenarioEntry.title);

                            fillText(scenarioEntry.text, function() {
                                test.click('form.register button[type="submit"]');
                            });
                        }, 'サムネイルのプレビューが表示されません。アップロードに失敗した可能性があります。');
                    });
                }, '画像のプレビューが表示されません。アップロードに失敗した可能性があります。');
            });
        });
    },
    // 登録できたことを確認して、公開側の詳細を開く
    function() {
        test.assertText('div.alert-success', 'エントリーを登録しました。', 'エントリーを登録できていません。');
        test.assert(entryRow(scenarioEntry.code).length === 1, '登録したエントリーが一覧にありません。');

        test.visit('/entry/detail/' + scenarioEntry.code);
    },
    // 詳細にサムネイルが出ることを確認して、画像の一覧を開く
    function() {
        var thumbnail = $('#entry .images img');

        test.assert(thumbnail.length === 1, '詳細にサムネイルが表示されていません。');
        test.assert(thumbnail.attr('src').indexOf('/' + scenarioEntry.thumbnail) !== -1, '詳細のサムネイルが、アップロードしたファイルになっていません。（' + thumbnail.attr('src') + '）');

        test.wait(function() {
            return imagesLoaded(thumbnail);
        }, function() {
            test.click('#entry .images a', '詳細のサムネイルに、画像の一覧へのリンクがありません。');
        }, '詳細のサムネイルの画像を読み込めません。保存されていない可能性があります。');
    },
    // 画像の一覧に2枚とも出ることを確認して、管理画面のエントリー管理ページに戻る
    function() {
        var pictures = $('img').filter(function() {
            var src = $(this).attr('src') || '';

            return src.indexOf('/' + scenarioEntry.pictures[0]) !== -1 || src.indexOf('/' + scenarioEntry.pictures[1]) !== -1;
        });

        test.assert(pictures.length === 2, '画像の一覧に、アップロードした画像が2枚とも表示されていません。（' + pictures.length + '枚）');

        test.wait(function() {
            return imagesLoaded(pictures);
        }, function() {
            test.visit('/admin/entry');
        }, '画像の一覧の画像を読み込めません。保存されていない可能性があります。');
    },
    // エントリー編集ページに移動
    function() {
        test.click(entryRow(scenarioEntry.code).find('a:contains("編集")'), '一覧の ' + scenarioEntry.code + ' の編集リンクが見つかりません。');
    },
    // 編集画面に、登録した画像とサムネイルが出ることを確認して、エントリーを削除
    function() {
        var form = $('form.delete');

        test.assertValue('form.register input[name="code"]', scenarioEntry.code, '削除対象が ' + scenarioEntry.code + ' ではありません。');
        test.assert(form.length === 1, '削除フォームが表示されていません。');

        test.wait(function() {
            return imagesLoaded($('#thumbnail p img')) && $('#pictures input[name="picture_files[]"]').length === 2;
        }, function() {
            form.off('submit');
            test.click('form.delete button[type="submit"]');
        }, '編集画面に、登録した画像とサムネイルが表示されません。');
    },
    // 削除できたことを確認してホームに移動
    function() {
        test.assertText('div.alert-success', 'エントリーを削除しました。', 'エントリーを削除できていません。');
        test.assert(entryRow(scenarioEntry.code).length === 0, '削除したエントリーが一覧に残っています。');

        test.click('a:contains("ホーム")');
    },
    // 管理者用ページからログアウト
    function() {
        test.assertExists('a:contains("管理者さん")', '管理者用ページが表示されていません。');

        test.click('a:contains("管理者さん")');
        setTimeout(function() {
            test.click('a:contains("ログアウト")');
        }, 500);
    },
    // ログアウトできたことを確認して初期ページに戻る
    function() {
        test.assertExists('input[name="username"]', 'ログアウトできていません。');
        test.click('a:contains("ホームページへ戻る")');
    }
];
