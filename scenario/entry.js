/* テスト用エントリー（test オブジェクトはページごとに作り直されるため、値は定数で持つ） */
var scenarioEntry = {
    code:     'scenario-entry',
    title:    'シナリオのエントリー',
    text:     '<p>シナリオの本文です。</p>',
    textBody: 'シナリオの本文です。'
};

/* 編集後の内容（登録時の値と部分一致しないよう、別の文言にする） */
var scenarioEntryEdited = {
    title:    'シナリオで編集したエントリー',
    text:     '<p>編集後の本文です。</p>',
    textBody: '編集後の本文です。'
};

/* エントリー一覧から、コードで対象の行を取得する */
var entryRow = function(code) {
    return $('table tbody tr').filter(function() {
        return $(this).find('td code').text().trim() === code;
    });
};

/* 一覧の行から、公開の表示を取得する（承認列が有効なときもあるので、公開は最後のバッジになる） */
var entryPublicLabel = function(row) {
    return row.find('span.badge').last().text().trim();
};

/* 本文を入力してからコールバックを実行する */
var fillText = function(html, callback) {
    var textarea = $('form.register textarea[name="text"]');

    if (textarea.length === 0) {
        return test.abort('本文の入力欄が見つかりません。本文形式が「なし」になっている可能性があります。');
    }
    if (!textarea.hasClass('editor')) {
        textarea.val(html);

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
    // エントリーを登録
    function() {
        var form = $('form.register');

        test.assert(form.find('input[name="code"]').length === 1, 'エントリー登録フォームが表示されていません。');
        test.assertValue('form.register select[name="public"]', 'all', '公開の初期値が「公開」になっていません。');
        test.assert(form.find('input[name="datetime"]').val() !== '', '日時の初期値が入力されていません。');

        form.find('input[name="code"]').val(scenarioEntry.code);
        form.find('input[name="title"]').val(scenarioEntry.title);

        fillText(scenarioEntry.text, function() {
            test.click('form.register button[type="submit"]');
        });
    },
    // 登録できたことを確認して、エントリー管理ページに移動
    function() {
        test.assertText('div.alert-success', 'エントリーを登録しました。', 'エントリーを登録できていません。');
        test.assertValue('form.register input[name="code"]', scenarioEntry.code, '登録した後に、登録したエントリーの編集画面が表示されていません。');
        test.assertValue('form.register input[name="title"]', scenarioEntry.title, '登録した後の編集画面に、登録したタイトルが表示されていません。');

        test.visit('/admin/entry');
    },
    // 一覧に並ぶことを確認して、公開側の詳細を開く
    function() {
        test.assert(entryRow(scenarioEntry.code).length === 1, '登録したエントリーが一覧にありません。');
        test.assertText(entryRow(scenarioEntry.code), scenarioEntry.title, '登録したエントリーのタイトルが「' + scenarioEntry.title + '」になっていません。');
        test.assert(entryPublicLabel(entryRow(scenarioEntry.code)) === '公開', '登録したエントリーの公開が「公開」になっていません。');

        test.visit('/entry/detail/' + scenarioEntry.code);
    },
    // 公開側の詳細に表示されることを確認して、公開側の一覧を開く
    function() {
        test.assertText('#entry h2', scenarioEntry.title, '公開側の詳細にエントリーのタイトルが表示されていません。');
        test.assertText('#entry div.text', scenarioEntry.textBody, '公開側の詳細にエントリーの本文が表示されていません。');

        test.visit('/entry/');
    },
    // 公開側の一覧に並ぶことを確認して、詳細へのリンクから移動
    function() {
        test.assertText('#entry', scenarioEntry.title, '公開側の一覧にエントリーが表示されていません。');
        test.click('#entry a[href$="/entry/detail/' + scenarioEntry.code + '"]', '公開側の一覧に詳細へのリンクがありません。');
    },
    // リンクから詳細を開けたことを確認して、管理画面のエントリー管理ページに戻る
    function() {
        test.assertText('#entry h2', scenarioEntry.title, '公開側の一覧のリンクから詳細を開けていません。');
        test.visit('/admin/entry');
    },
    // エントリー編集ページに移動
    function() {
        test.click(entryRow(scenarioEntry.code).find('a:contains("編集")'), '一覧の ' + scenarioEntry.code + ' の編集リンクが見つかりません。');
    },
    // エントリーを編集して非公開にする
    function() {
        var form = $('form.register');

        test.assertValue('form.register input[name="code"]', scenarioEntry.code, '編集対象が ' + scenarioEntry.code + ' ではありません。');
        test.assertValue('form.register input[name="title"]', scenarioEntry.title, '編集対象のタイトルが登録した内容になっていません。');

        form.find('input[name="title"]').val(scenarioEntryEdited.title);
        form.find('select[name="public"]').val('none');

        fillText(scenarioEntryEdited.text, function() {
            test.click('form.register button[type="submit"]');
        });
    },
    // 編集できたことを確認して、エントリー管理ページに移動
    function() {
        test.assertText('div.alert-success', 'エントリーを登録しました。', 'エントリーを編集できていません。');
        test.assertValue('form.register input[name="title"]', scenarioEntryEdited.title, '編集した後の編集画面に、編集したタイトルが表示されていません。');

        test.visit('/admin/entry');
    },
    // 一覧の表示が変わったことを確認して、公開側の詳細を開く
    function() {
        test.assertText(entryRow(scenarioEntry.code), scenarioEntryEdited.title, '編集したエントリーのタイトルが「' + scenarioEntryEdited.title + '」になっていません。');
        test.assert(entryPublicLabel(entryRow(scenarioEntry.code)) === '非公開', '編集したエントリーの公開が「非公開」になっていません。');

        test.visit('/entry/detail/' + scenarioEntry.code);
    },
    // 非公開のエントリーが公開側の詳細に出ないことを確認して、公開側の一覧を開く
    function() {
        test.assertText('div.alert-danger', 'エントリーが見つかりません。', '非公開にしたエントリーが公開側の詳細に表示されています。');
        test.assertNoText('body', scenarioEntryEdited.title, '非公開にしたエントリーのタイトルが公開側に表示されています。');

        test.visit('/entry/');
    },
    // 公開側の一覧からも消えていることを確認して、管理画面のエントリー管理ページに戻る
    function() {
        test.assertNoText('#entry', scenarioEntryEdited.title, '非公開にしたエントリーが公開側の一覧に表示されています。');
        test.visit('/admin/entry');
    },
    // エントリー編集ページに移動
    function() {
        test.click(entryRow(scenarioEntry.code).find('a:contains("編集")'), '一覧の ' + scenarioEntry.code + ' の編集リンクが見つかりません。');
    },
    // エントリーを削除
    function() {
        var form = $('form.delete');

        test.assertValue('form.register input[name="code"]', scenarioEntry.code, '削除対象が ' + scenarioEntry.code + ' ではありません。');
        test.assert(form.length === 1, '削除フォームが表示されていません。');

        form.off('submit');
        test.click('form.delete button[type="submit"]');
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
