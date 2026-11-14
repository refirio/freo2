/* テスト用エントリー（test オブジェクトはページごとに作り直されるため、値は定数で持つ） */
var scenarioEntry = {
    code:     'scenario-draft',
    title:    'シナリオの下書き',
    text:     '<p>下書きの本文です。</p>',
    textBody: '下書きの本文です。'
};

/* 編集画面で入力する内容（登録時の値と部分一致しないよう、別の文言にする） */
var scenarioTitleEdited  = 'シナリオで書きかけたタイトル';
var scenarioTitleEdited2 = 'ログアウト前に書きかけたタイトル';

/* 一時保存のキーを控える場所（ページをまたいで使うためsessionStorageに置く） */
var scenarioKeyStorage = 'scenario_entry_draft';

/* エントリー一覧から、コードで対象の行を取得する */
var entryRow = function(code) {
    return $('table tbody tr').filter(function() {
        return $(this).find('td code').text().trim() === code;
    });
};

/* 一時保存された下書きを取得する */
var draftItem = function(key) {
    try {
        return JSON.parse(window.localStorage.getItem(key));
    } catch (e) {
        return null;
    }
};

/* 本文の入力欄の値を取得する */
var currentText = function() {
    var textarea = $('form.register textarea[name="text"]');

    return textarea.hasClass('editor') ? window.text.getData() : textarea.val();
};

/* 本文を入力してからコールバックを実行する */
var fillText = function(html, callback) {
    var textarea = $('form.register textarea[name="text"]');

    if (textarea.length === 0) {
        return test.abort('本文の入力欄が見つかりません。本文形式が「なし」になっている可能性があります。');
    }
    if (!textarea.hasClass('editor')) {
        // 一時保存は入力のイベントで動くので、イベントも起こす
        textarea.val(html).trigger('input');

        return callback();
    }

    // WYSIWYGエディタは非同期に作られるので、admin.js がインスタンスを保持するまで待つ
    return test.wait(function() {
        return window.text != null && typeof window.text.setData === 'function';
    }, function() {
        window.text.setData(html);

        callback();
    }, 'WYSIWYGエディタ（CKEditor）が初期化されませんでした。CDNから読み込めているか確認してください。');
};

/* 復元の確認を出すかの判定はCKEditorの準備ができてから行われるので、それを待ってからコールバックを実行する */
var whenReady = function(callback) {
    if (!$('form.register textarea[name="text"]').hasClass('editor')) {
        return callback();
    }

    // admin.js がインスタンスを保持した時点で、判定（Promiseの後続処理）も済んでいる
    return test.wait(function() {
        return window.text != null && typeof window.text.setData === 'function';
    }, callback, 'WYSIWYGエディタ（CKEditor）が初期化されませんでした。CDNから読み込めているか確認してください。');
};

/* タイトルが一時保存されるまで待ってからコールバックを実行する */
var waitDraft = function(key, title, callback) {
    test.wait(function() {
        var draft = draftItem(key);

        return draft !== null && draft.title === title;
    }, callback, '入力中の内容が一時保存されていません。');
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
    // タイトルと本文を入力し、一時保存されたら再読み込みする
    function() {
        var form = $('form.register');
        var key  = form.attr('data-draft');

        test.assert(form.length === 1, 'エントリー登録フォームが表示されていません。');
        test.assert(/:new$/.test(key || ''), '登録フォームに一時保存のキーがありません。');

        whenReady(function() {
            test.assertNotExists('div.draft', '前回の入力中の内容が残っています。前回のテストが中断した可能性があります。');

            window.sessionStorage.setItem(scenarioKeyStorage, key);

            form.find('input[name="title"]').val(scenarioEntry.title).trigger('input');

            fillText(scenarioEntry.text, function() {
                waitDraft(key, scenarioEntry.title, function() {
                    test.reload();
                });
            });
        });
    },
    // 復元の確認が出ることを確認して復元し、エントリーを登録
    function() {
        var form = $('form.register');

        whenReady(function() {
            test.assertText('div.draft', '入力中の内容があります', '入力中の内容を復元するかの確認が表示されていません。');
            test.assertNoText('div.draft', '保存されている内容の方が新しく', '新規登録なのに、保存されている内容の方が新しいと表示されています。');
            test.assertValue('form.register input[name="title"]', '', '復元する前からタイトルが入力されています。');

            test.click('div.draft button.draft-restore', '「復元する」のボタンが見つかりません。');

            test.assertNotExists('div.draft', '復元した後も確認が表示されたままです。');
            test.assertValue('form.register input[name="title"]', scenarioEntry.title, 'タイトルが復元されていません。');
            test.assert(currentText().indexOf(scenarioEntry.textBody) !== -1, '本文が復元されていません。');

            form.find('input[name="code"]').val(scenarioEntry.code);
            test.click('form.register button[type="submit"]');
        });
    },
    // 登録できたら一時保存が消えていることを確認して、編集ページに移動
    function() {
        test.assertText('div.alert-success', 'エントリーを登録しました。', 'エントリーを登録できていません。');
        test.assert(entryRow(scenarioEntry.code).length === 1, '登録したエントリーが一覧にありません。');
        test.assert(draftItem(window.sessionStorage.getItem(scenarioKeyStorage)) === null, '登録した後も、入力中の内容が残っています。');

        test.click(entryRow(scenarioEntry.code).find('a:contains("編集")'), '一覧の ' + scenarioEntry.code + ' の編集リンクが見つかりません。');
    },
    // タイトルを書きかけ、一時保存されたら「その後に別の人が保存した」状態にして再読み込みする
    function() {
        var form = $('form.register');
        var key  = form.attr('data-draft');

        test.assertValue('form.register input[name="code"]', scenarioEntry.code, '編集対象が ' + scenarioEntry.code + ' ではありません。');
        test.assert(/:\d+$/.test(key || ''), '編集フォームに一時保存のキーがありません。');

        whenReady(function() {
            test.assertNotExists('div.draft', '登録したばかりのエントリーで、復元の確認が表示されています。');

            window.sessionStorage.setItem(scenarioKeyStorage, key);

            form.find('input[name="title"]').val(scenarioTitleEdited).trigger('input');

            waitDraft(key, scenarioTitleEdited, function() {
                // 下書きを取った時点の更新日時を古くする（保存されている内容の方が新しい状態）
                var draft = draftItem(key);

                draft.modified = '2000-01-01 00:00:00';
                window.localStorage.setItem(key, JSON.stringify(draft));

                test.reload();
            });
        });
    },
    // 保存されている内容の方が新しいと表示されることを確認して破棄し、また書きかけてログアウトする
    function() {
        var form = $('form.register');
        var key  = window.sessionStorage.getItem(scenarioKeyStorage);

        whenReady(function() {
            test.assertText('div.draft', '保存されている内容の方が新しくなっています', '保存されている内容の方が新しいことが表示されていません。');

            test.click('div.draft button.draft-discard', '「破棄する」のボタンが見つかりません。');

            test.assertNotExists('div.draft', '破棄した後も確認が表示されたままです。');
            test.assertValue('form.register input[name="title"]', scenarioEntry.title, '破棄したのに、タイトルが書き換わっています。');
            test.assert(draftItem(key) === null, '破棄したのに、入力中の内容が残っています。');

            form.find('input[name="title"]').val(scenarioTitleEdited2).trigger('input');

            waitDraft(key, scenarioTitleEdited2, function() {
                test.visit('/auth/logout');
            });
        });
    },
    // ログアウトすると一時保存が消えていることを確認して、ログインし直す
    function() {
        var form = $('form:eq(0)');

        test.assert(form.find('input[name="username"]').length === 1, 'ログアウトできていません。');
        test.assert(draftItem(window.sessionStorage.getItem(scenarioKeyStorage)) === null, 'ログアウトしたのに、入力中の内容が残っています。');

        window.sessionStorage.removeItem(scenarioKeyStorage);

        form.find('input[name="username"]').val('admin');
        form.find('input[name="password"]').val('abcd1234');
        test.click('form:eq(0) button[type="submit"]');
    },
    // エントリー管理ページに移動
    function() {
        test.assertText('body', '管理者さん', 'ログインし直せていません。');
        test.click('a:contains("エントリー管理")');
    },
    // エントリー編集ページに移動
    function() {
        test.click(entryRow(scenarioEntry.code).find('a:contains("編集")'), '一覧の ' + scenarioEntry.code + ' の編集リンクが見つかりません。');
    },
    // 復元の確認が出ないことを確認して、エントリーを削除
    function() {
        var form = $('form.delete');

        test.assertValue('form.register input[name="code"]', scenarioEntry.code, '削除対象が ' + scenarioEntry.code + ' ではありません。');
        test.assert(form.length === 1, '削除フォームが表示されていません。');

        whenReady(function() {
            test.assertNotExists('div.draft', 'ログアウトした後も、復元の確認が表示されています。');

            form.off('submit');
            test.click('form.delete button[type="submit"]');
        });
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
