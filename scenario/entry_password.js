/* テスト用エントリー（test オブジェクトはページごとに作り直されるため、値は定数で持つ） */
var scenarioEntry = {
    code:     'scenario-password',
    title:    'シナリオのパスワード認証',
    text:     '<p>認証しないと読めない本文です。</p>',
    textBody: '認証しないと読めない本文です。',
    password: 'scenario1234'
};

/* 認証前に表示される内容（設定 restricted_password_title / restricted_password_text の初期値） */
var restrictedTitle = '要認証: ';
var restrictedText  = 'パスワード認証により公開されます。';

/* エントリー一覧から、コードで対象の行を取得する */
var codeRow = function(code) {
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

/* 公開側のパスワードフォームから送信する */
var submitPassword = function(password) {
    var form = $('#entry form');

    test.assert(form.find('input[name="password"]').length === 1, '公開側にパスワードの入力フォームが表示されていません。');

    form.find('input[name="password"]').val(password);
    test.click('#entry form button[type="submit"]', '公開側のパスワードフォームに認証ボタンがありません。');
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
        test.assert(codeRow(scenarioEntry.code).length === 0, 'エントリー ' + scenarioEntry.code + ' が残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("エントリー登録")');
    },
    // パスワード認証で公開するエントリーを登録
    function() {
        var form = $('form.register');

        test.assert(form.find('input[name="code"]').length === 1, 'エントリー登録フォームが表示されていません。');
        test.assert(!$('.for-password').is(':visible'), '公開が「公開」のときにパスワードの欄が表示されています。');

        form.find('select[name="public"]').val('password').trigger('change');
        test.assert($('.for-password').is(':visible'), '公開で「パスワード認証で公開」を選んでもパスワードの欄が表示されません。');

        form.find('input[name="code"]').val(scenarioEntry.code);
        form.find('input[name="title"]').val(scenarioEntry.title);
        form.find('input[name="password"]').val(scenarioEntry.password);

        fillText(scenarioEntry.text, function() {
            test.click('form.register button[type="submit"]');
        });
    },
    // 登録できたことを確認して、公開側の詳細を開く
    function() {
        test.assertText('div.alert-success', 'エントリーを登録しました。', 'エントリーを登録できていません。');
        test.assertValue('form.register input[name="code"]', scenarioEntry.code, '登録した後に、登録したエントリーの編集画面が表示されていません。');
        test.assertValue('form.register select[name="public"]', 'password', '登録したエントリーの公開が「パスワード認証で公開」になっていません。');

        test.visit('/entry/detail/' + scenarioEntry.code);
    },
    // 認証前は本文が伏せられていることを確認して、誤ったパスワードで認証する
    function() {
        test.assertText('#entry h2', restrictedTitle + scenarioEntry.title, '認証前のタイトルに「' + restrictedTitle + '」が付いていません。');
        test.assertText('#entry div.text', restrictedText, '認証前の本文が差し替えられていません。');
        test.assertNoText('#entry div.text', scenarioEntry.textBody, '認証前なのに本来の本文が表示されています。');

        submitPassword(scenarioEntry.password + 'x');
    },
    // 誤ったパスワードが拒否されたことを確認して、もう一度詳細を開く
    function() {
        test.assertText('div.alert-danger', 'パスワードが違います。', '誤ったパスワードが拒否されていません。');
        test.assertNoText('body', scenarioEntry.textBody, '誤ったパスワードなのに本来の本文が表示されています。');

        test.visit('/entry/detail/' + scenarioEntry.code);
    },
    // 正しいパスワードで認証する
    function() {
        test.assertText('#entry div.text', restrictedText, '認証前の状態に戻っていません。');

        submitPassword(scenarioEntry.password);
    },
    // 認証すると本文が表示されることを確認して、管理画面に戻る
    function() {
        test.assertNoText('#entry h2', '要認証', '認証後のタイトルに「要認証」が残っています。');
        test.assertText('#entry h2', scenarioEntry.title, '認証後にエントリーのタイトルが表示されていません。');
        test.assertText('#entry div.text', scenarioEntry.textBody, '認証しても本来の本文が表示されません。');
        test.assertNotExists('#entry form input[name="password"]', '認証後もパスワードの入力フォームが表示されています。');

        test.visit('/admin/entry');
    },
    // エントリー編集ページに移動
    function() {
        test.click(codeRow(scenarioEntry.code).find('a:contains("編集")'), '一覧の ' + scenarioEntry.code + ' の編集リンクが見つかりません。');
    },
    // エントリーを削除
    function() {
        var form = $('form.delete');

        test.assertValue('form.register input[name="code"]', scenarioEntry.code, '削除対象が ' + scenarioEntry.code + ' ではありません。');
        test.assertValue('form.register input[name="password"]', scenarioEntry.password, '登録したパスワードが編集画面に復元されていません。');
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
