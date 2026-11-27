/* コメントの投稿先になるエントリー（test オブジェクトはページごとに作り直されるため、値は定数で持つ） */
var scenarioEntry = {
    code:  'scenario-comment-entry',
    title: 'シナリオのコメント確認'
};

/* テスト用のコメント */
var scenarioComment = {
    name:    'シナリオ投稿者',
    url:     'https://example.com/scenario',
    message: 'シナリオから投稿したコメントです。'
};

/* 一覧から、コードで対象の行を取得する */
var codeRow = function(code) {
    return $('table tbody tr').filter(function() {
        return $(this).find('td code').text().trim() === code;
    });
};

/* コメント一覧から、お名前で対象の行を取得する（ゲストの投稿はコードの列が無い） */
var commentRow = function(name) {
    return $('table tbody tr').filter(function() {
        return $(this).text().indexOf(name) !== -1;
    });
};

/* 入力欄の直後に表示された入力エラーを取得する */
var fieldWarning = function(name) {
    return $('form.register [name="' + name + '"]').parent().find('div.warning');
};

/* コメントフォームに入力して送信する */
var submitComment = function() {
    var form = $('form.register');

    form.find('input[name="name"]').val(scenarioComment.name);
    form.find('input[name="url"]').val(scenarioComment.url);
    form.find('textarea[name="message"]').val(scenarioComment.message);

    test.click('form.register button[type="submit"]');
};

/* 確認画面に入力内容が並んでいることを検証する */
var assertPreview = function() {
    test.assertText('#comment dl', scenarioComment.name, '確認画面にお名前が表示されていません。');
    test.assertText('#comment dl', scenarioComment.url, '確認画面にURLが表示されていません。');
    test.assertText('#comment dl', scenarioComment.message, '確認画面にコメント内容が表示されていません。');
};

/* 管理者用ページからログアウトする */
var logout = function() {
    test.click('a:contains("管理者さん")');
    setTimeout(function() {
        test.click('a:contains("ログアウト")');
    }, 500);
};

/* ログインフォームから送信する */
var login = function(username, password) {
    var form = $('form:eq(0)');

    test.assert(form.find('input[name="username"]').length === 1, 'ログインフォームが表示されていません。');

    form.find('input[name="username"]').val(username);
    form.find('input[name="password"]').val(password);
    test.click('form:eq(0) button[type="submit"]');
};

test.scenario = [
    // 初期ページからログインページに移動
    function() {
        test.assertExists('a:contains("ログイン")', '初期ページにログインへのリンクがありません。');
        test.click('a:contains("ログイン")');
    },
    // 管理者用ページにログイン
    function() {
        login('admin', 'abcd1234');
    },
    // ログインできたことを確認してエントリー管理ページに移動
    function() {
        test.assertText('body', '管理者さん', 'ログインできていません。');
        test.click('a:contains("エントリー管理")');
    },
    // 前回のテストデータが残っていないことを確認してエントリー登録ページに移動
    function() {
        test.assert(codeRow(scenarioEntry.code).length === 0, 'エントリー ' + scenarioEntry.code + ' が残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("エントリー登録")');
    },
    // コメントを受け付けるエントリーを登録
    function() {
        var form = $('form.register');

        test.assert(form.find('input[name="code"]').length === 1, 'エントリー登録フォームが表示されていません。');
        test.assert(form.find('select[name="comment"] option[value="opened"]').length === 1, 'コメントの受付に「受け付ける」がありません。');

        form.find('input[name="code"]').val(scenarioEntry.code);
        form.find('input[name="title"]').val(scenarioEntry.title);
        form.find('select[name="comment"]').val('opened');
        test.click('form.register button[type="submit"]');
    },
    // 登録できたことを確認してログアウト（コメントはゲストとして投稿する）
    function() {
        test.assertText('div.alert-success', 'エントリーを登録しました。', 'エントリーを登録できていません。');
        test.assertValue('form.register input[name="code"]', scenarioEntry.code, '登録した後に、登録したエントリーの編集画面が表示されていません。');

        logout();
    },
    // ログアウトできたことを確認して、公開側のエントリー詳細を開く
    function() {
        test.assertExists('input[name="username"]', 'ログアウトできていません。');
        test.visit('/entry/detail/' + scenarioEntry.code);
    },
    // コメントフォームがあることを確認し、未入力で送信して入力エラーを確認
    function() {
        test.assertExists('#comment_form', 'エントリー詳細にコメントフォームが表示されていません。');
        test.assertNotExists('#comment', 'コメントを投稿する前からコメントが表示されています。');

        $('div.warning').remove();
        $('form.register [name="name"]').val('');
        $('form.register [name="message"]').val('');
        test.click('form.register button[type="submit"]');
        test.wait(function() {
            return $('form.register div.warning').length > 0;
        }, function() {
            test.assertText(fieldWarning('name'), 'お名前が入力されていません。', 'お名前未入力のエラーが表示されていません。');
            test.assertText(fieldWarning('message'), 'コメント内容が入力されていません。', 'コメント内容未入力のエラーが表示されていません。');
            test.assert($('form.register div.warning').length === 2, '必須項目以外にも入力エラーが表示されています。（' + $('form.register div.warning').text() + '）');

            test.reload();
        }, '未入力でも入力エラーが表示されませんでした。');
    },
    // 入力して確認画面に進む
    function() {
        submitComment();
    },
    // 確認画面に入力内容が表示されることを確認して、「修正」で入力画面に戻る
    function() {
        assertPreview();

        test.click('#comment a:contains("修正")', '確認画面に「修正」のリンクがありません。');
    },
    // 入力内容が復元されていることを確認して、もう一度確認画面に進む
    function() {
        test.assertValue('form.register input[name="name"]', scenarioComment.name, '「修正」で戻ったときにお名前が復元されていません。');
        test.assertValue('form.register input[name="url"]', scenarioComment.url, '「修正」で戻ったときにURLが復元されていません。');
        test.assertValue('form.register textarea[name="message"]', scenarioComment.message, '「修正」で戻ったときにコメント内容が復元されていません。');

        submitComment();
    },
    // 確認画面から投稿する
    function() {
        assertPreview();

        test.click('#comment form button[type="submit"]', '確認画面に投稿ボタンがありません。');
    },
    // 投稿できたことを確認して、エントリー詳細に戻る
    function() {
        test.assertText('#comment', 'コメントを投稿しました。', 'コメントを投稿できていません。');

        test.click('#comment a:contains("戻る")', '完了画面にエントリーへ戻るリンクがありません。');
    },
    // 投稿したコメントが公開側に表示されることを確認して、ログインページに移動
    function() {
        test.assertExists('#comment', '投稿したコメントがエントリー詳細に表示されていません。');
        test.assertText('#comment', scenarioComment.name, '投稿したコメントのお名前が表示されていません。');
        test.assertText('#comment', scenarioComment.message, '投稿したコメントの内容が表示されていません。');

        test.visit('/auth/');
    },
    // 管理者用ページにログイン
    function() {
        login('admin', 'abcd1234');
    },
    // ログインできたことを確認してコメント管理ページに移動
    function() {
        test.assertText('body', '管理者さん', 'ログインできていません。');
        test.click('a:contains("コメント管理")');
    },
    // 投稿したコメントが一覧にあることを確認して、編集ページに移動
    function() {
        var row = commentRow(scenarioComment.name);

        test.assert(row.length === 1, '投稿したコメントが管理画面の一覧にありません。');

        test.click(row.find('a:contains("編集")'), '一覧に「編集」のリンクが見つかりません。');
    },
    // 投稿内容を確認してコメントを削除
    function() {
        var form = $('form.delete');

        test.assertValue('form.register [name="name"]', scenarioComment.name, '削除対象が投稿したコメントではありません。');
        test.assertValue('form.register textarea[name="message"]', scenarioComment.message, '削除対象のコメント内容が投稿した内容ではありません。');
        test.assert(form.length === 1, '削除フォームが表示されていません。');

        form.off('submit');
        test.click('form.delete button[type="submit"]');
    },
    // 削除できたことを確認してエントリー管理ページに移動
    function() {
        test.assertText('div.alert-success', 'コメントを削除しました。', 'コメントを削除できていません。');
        test.assert(commentRow(scenarioComment.name).length === 0, '削除したコメントが一覧に残っています。');

        test.click('a:contains("エントリー管理")');
    },
    // エントリー編集ページに移動
    function() {
        test.click(codeRow(scenarioEntry.code).find('a:contains("編集")'), '一覧の ' + scenarioEntry.code + ' の編集リンクが見つかりません。');
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
        test.assert(codeRow(scenarioEntry.code).length === 0, '削除したエントリーが一覧に残っています。');

        test.click('a:contains("ホーム")');
    },
    // 管理者用ページからログアウト
    function() {
        test.assertExists('a:contains("管理者さん")', '管理者用ページが表示されていません。');

        logout();
    },
    // ログアウトできたことを確認して初期ページに戻る
    function() {
        test.assertExists('input[name="username"]', 'ログアウトできていません。');
        test.click('a:contains("ホームページへ戻る")');
    }
];
