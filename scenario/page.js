/* テスト用ページ（test オブジェクトはページごとに作り直されるため、値は定数で持つ） */
var scenarioPage = {
    code:     'scenario-page',
    title:    'シナリオのページ',
    text:     '<p>シナリオの本文です。</p>',
    textBody: 'シナリオの本文です。'
};

/* 編集後の内容（コードはスラッシュを含む階層にして、/page/<階層> で開けることを確認する） */
var scenarioPageEdited = {
    code:     'scenario/page-child',
    title:    'シナリオで編集したページ',
    text:     '<p>編集後の本文です。</p>',
    textBody: '編集後の本文です。'
};

/* ページ一覧から、コードで対象の行を取得する */
var pageRow = function(code) {
    return $('table tbody tr').filter(function() {
        return $(this).find('td code').text().trim() === code;
    });
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
    // ログインできたことを確認してページ管理ページに移動
    function() {
        test.assertText('body', '管理者さん', 'ログインできていません。');
        test.click('a:contains("ページ管理")');
    },
    // 前回のテストデータが残っていないことを確認してページ登録ページに移動
    function() {
        test.assert(pageRow(scenarioPage.code).length === 0, 'ページ ' + scenarioPage.code + ' が残っています。前回のテストが中断した可能性があります。');
        test.assert(pageRow(scenarioPageEdited.code).length === 0, 'ページ ' + scenarioPageEdited.code + ' が残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("ページ登録")');
    },
    // ページを登録
    function() {
        var form = $('form.register');

        test.assert(form.find('input[name="code"]').length === 1, 'ページ登録フォームが表示されていません。');
        test.assertValue('form.register select[name="public"]', 'all', '公開の初期値が「公開」になっていません。');

        form.find('input[name="code"]').val(scenarioPage.code);
        form.find('input[name="title"]').val(scenarioPage.title);

        fillText(scenarioPage.text, function() {
            test.click('form.register button[type="submit"]');
        });
    },
    // 登録できたことを確認して、公開側のページを開く
    function() {
        test.assertText('div.alert-success', 'ページを登録しました。', 'ページを登録できていません。');
        test.assertValue('form.register input[name="code"]', scenarioPage.code, '登録した後に、登録したページの編集画面が表示されていません。');
        test.assertValue('form.register input[name="title"]', scenarioPage.title, '登録したページのタイトルが「' + scenarioPage.title + '」になっていません。');

        test.visit('/page/' + scenarioPage.code);
    },
    // 公開側に表示されることを確認して、管理画面のページ管理ページに戻る
    function() {
        test.assertText('main h2', scenarioPage.title, '公開側にページのタイトルが表示されていません。');
        test.assertText('main div.text', scenarioPage.textBody, '公開側にページの本文が表示されていません。');

        test.visit('/admin/page');
    },
    // ページ編集ページに移動
    function() {
        test.click(pageRow(scenarioPage.code).find('a:contains("編集")'), '一覧の ' + scenarioPage.code + ' の編集リンクが見つかりません。');
    },
    // ページを編集して、コードを階層にする
    function() {
        var form = $('form.register');

        test.assertValue('form.register input[name="code"]', scenarioPage.code, '編集対象が ' + scenarioPage.code + ' ではありません。');
        test.assertValue('form.register input[name="title"]', scenarioPage.title, '編集対象のタイトルが登録した内容になっていません。');

        form.find('input[name="code"]').val(scenarioPageEdited.code);
        form.find('input[name="title"]').val(scenarioPageEdited.title);

        fillText(scenarioPageEdited.text, function() {
            test.click('form.register button[type="submit"]');
        });
    },
    // 編集できたことを確認して、階層のコードで公開側のページを開く
    function() {
        test.assertText('div.alert-success', 'ページを登録しました。', 'ページを編集できていません。');
        test.assertValue('form.register input[name="code"]', scenarioPageEdited.code, '編集した後の編集画面で、コードが ' + scenarioPageEdited.code + ' になっていません。');

        test.visit('/page/' + scenarioPageEdited.code);
    },
    // 階層のコードでも表示されることを確認して、編集前のコードを開く
    function() {
        test.assertText('main h2', scenarioPageEdited.title, '階層のコード ' + scenarioPageEdited.code + ' でページを開けていません。');
        test.assertText('main div.text', scenarioPageEdited.textBody, '階層のコードで開いたページに本文が表示されていません。');

        test.visit('/page/' + scenarioPage.code);
    },
    // 編集前のコードでは表示されないことを確認して、管理画面のページ管理ページに戻る
    function() {
        test.assertText('div.alert-danger', 'ページが見つかりません。', '編集前のコード ' + scenarioPage.code + ' でページが表示されています。');
        test.visit('/admin/page');
    },
    // ページ編集ページに移動
    function() {
        test.click(pageRow(scenarioPageEdited.code).find('a:contains("編集")'), '一覧の ' + scenarioPageEdited.code + ' の編集リンクが見つかりません。');
    },
    // ページを削除
    function() {
        var form = $('form.delete');

        test.assertValue('form.register input[name="code"]', scenarioPageEdited.code, '削除対象が ' + scenarioPageEdited.code + ' ではありません。');
        test.assert(form.length === 1, '削除フォームが表示されていません。');

        form.off('submit');
        test.click('form.delete button[type="submit"]');
    },
    // 削除できたことを確認して、公開側のページを開く
    function() {
        test.assertText('div.alert-success', 'ページを削除しました。', 'ページを削除できていません。');
        test.assert(pageRow(scenarioPageEdited.code).length === 0, '削除したページが一覧に残っています。');

        test.visit('/page/' + scenarioPageEdited.code);
    },
    // 削除したページが公開側にも出ないことを確認して、管理者用ページのホームに移動
    function() {
        test.assertText('div.alert-danger', 'ページが見つかりません。', '削除したページが公開側に表示されています。');
        test.visit('/admin/');
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
