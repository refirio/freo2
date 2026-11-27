/* テスト用ページ（test オブジェクトはページごとに作り直されるため、値は定数で持つ） */
var scenarioPage = {
    code:     'scenario-page-public',
    title:    'シナリオのページ公開範囲',
    text:     '<p>ページの本文です。</p>',
    textBody: 'ページの本文です。',
    password: 'scenario1234'
};

/* 認証前に表示される内容（設定 restricted_password_title / restricted_password_text の初期値） */
var restrictedTitle = '要認証: ';
var restrictedText  = 'パスワード認証により公開されます。';

/* 公開終了日時に使う過去の日時（datetimepicker と同じ Y-m-d H:i 形式） */
var periodPast = '2000-01-01 00:00';

/* ページ一覧から、コードで対象の行を取得する */
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

/* 公開範囲を変えて送信する */
var submitPublic = function(published) {
    var form = $('form.register');

    form.find('select[name="public"]').val(published).trigger('change');
    test.click('form.register button[type="submit"]');
};

/* 公開側のパスワードフォームから送信する（ページは /page/<コード> に送信される） */
var submitPassword = function(password) {
    var form = $('main form');

    test.assert(form.find('input[name="password"]').length === 1, '公開側にパスワードの入力フォームが表示されていません。');
    test.assert(/\/page\//.test(form.attr('action')), 'パスワードフォームの送信先がページのURLになっていません。（' + form.attr('action') + '）');

    form.find('input[name="password"]').val(password);
    test.click('main form button[type="submit"]', '公開側のパスワードフォームに認証ボタンがありません。');
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
        test.assert(codeRow(scenarioPage.code).length === 0, 'ページ ' + scenarioPage.code + ' が残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("ページ登録")');
    },
    // 公開のページを登録
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

        test.visit('/page/' + scenarioPage.code);
    },
    // 公開側に表示されることを確認して、管理画面に戻る
    function() {
        test.assertText('main h2', scenarioPage.title, '公開のページが公開側に表示されていません。');
        test.assertText('main div.text', scenarioPage.textBody, '公開のページの本文が表示されていません。');

        test.visit('/admin/page');
    },
    // ページ編集ページに移動
    function() {
        test.click(codeRow(scenarioPage.code).find('a:contains("編集")'), '一覧の ' + scenarioPage.code + ' の編集リンクが見つかりません。');
    },
    // 非公開にする
    function() {
        test.assertValue('form.register input[name="code"]', scenarioPage.code, '編集対象が ' + scenarioPage.code + ' ではありません。');

        submitPublic('none');
    },
    // 編集できたことを確認して、公開側のページを開く
    function() {
        test.assertText('div.alert-success', 'ページを登録しました。', 'ページを編集できていません。');
        test.assertValue('form.register select[name="public"]', 'none', 'ページの公開が「非公開」になっていません。');

        test.visit('/page/' + scenarioPage.code);
    },
    // 非公開のページが表示されないことを確認して、管理画面に戻る
    function() {
        test.assertText('div.alert-danger', 'ページが見つかりません。', '非公開のページが公開側に表示されています。');
        test.assertNoText('body', scenarioPage.title, '非公開のページのタイトルが公開側に表示されています。');

        test.visit('/admin/page');
    },
    // ページ編集ページに移動
    function() {
        test.click(codeRow(scenarioPage.code).find('a:contains("編集")'), '一覧の ' + scenarioPage.code + ' の編集リンクが見つかりません。');
    },
    // パスワード認証で公開する
    function() {
        var form = $('form.register');

        test.assert(!$('.for-password').is(':visible'), '公開が「非公開」のときにパスワードの欄が表示されています。');

        form.find('select[name="public"]').val('password').trigger('change');
        test.assert($('.for-password').is(':visible'), '公開で「パスワード認証で公開」を選んでもパスワードの欄が表示されません。');

        form.find('input[name="password"]').val(scenarioPage.password);
        test.click('form.register button[type="submit"]');
    },
    // 編集できたことを確認して、公開側のページを開く
    function() {
        test.assertText('div.alert-success', 'ページを登録しました。', 'ページを編集できていません。');
        test.assertValue('form.register select[name="public"]', 'password', 'ページの公開が「パスワード認証で公開」になっていません。');

        test.visit('/page/' + scenarioPage.code);
    },
    // 認証前は本文が伏せられていることを確認して、誤ったパスワードで認証する
    function() {
        test.assertText('main h2', restrictedTitle + scenarioPage.title, '認証前のタイトルに「' + restrictedTitle + '」が付いていません。');
        test.assertText('main div.text', restrictedText, '認証前の本文が差し替えられていません。');
        test.assertNoText('main div.text', scenarioPage.textBody, '認証前なのに本来の本文が表示されています。');

        submitPassword(scenarioPage.password + 'x');
    },
    // 誤ったパスワードが拒否されたことを確認して、もう一度ページを開く
    function() {
        test.assertText('div.alert-danger', 'パスワードが違います。', '誤ったパスワードが拒否されていません。');
        test.assertNoText('body', scenarioPage.textBody, '誤ったパスワードなのに本来の本文が表示されています。');

        test.visit('/page/' + scenarioPage.code);
    },
    // 正しいパスワードで認証する
    function() {
        test.assertText('main div.text', restrictedText, '認証前の状態に戻っていません。');

        submitPassword(scenarioPage.password);
    },
    // 認証すると本文が表示されることを確認して、管理画面に戻る
    function() {
        test.assertNoText('main h2', '要認証', '認証後のタイトルに「要認証」が残っています。');
        test.assertText('main h2', scenarioPage.title, '認証後にページのタイトルが表示されていません。');
        test.assertText('main div.text', scenarioPage.textBody, '認証しても本来の本文が表示されません。');
        test.assertNotExists('main form input[name="password"]', '認証後もパスワードの入力フォームが表示されています。');

        test.visit('/admin/page');
    },
    // ページ編集ページに移動
    function() {
        test.click(codeRow(scenarioPage.code).find('a:contains("編集")'), '一覧の ' + scenarioPage.code + ' の編集リンクが見つかりません。');
    },
    // 公開に戻したうえで、公開終了日時を過去にする
    function() {
        var form = $('form.register');

        form.find('select[name="public"]').val('all').trigger('change');
        test.assert(!$('.for-password').is(':visible'), '公開を「公開」に戻してもパスワードの欄が表示されています。');
        test.assert($('.for-public').is(':visible'), '公開が「公開」のときに公開期間の欄が表示されていません。');

        form.find('input[name="public_end"]').val(periodPast);
        test.click('form.register button[type="submit"]');
    },
    // 編集できたことを確認して、公開側のページを開く
    function() {
        test.assertText('div.alert-success', 'ページを登録しました。', 'ページを編集できていません。');
        test.assertValue('form.register select[name="public"]', 'all', 'ページの公開が「公開」に戻っていません。');

        test.visit('/page/' + scenarioPage.code);
    },
    // 公開でも公開終了後なら表示されないことを確認して、管理画面に戻る
    function() {
        test.assertText('div.alert-danger', 'ページが見つかりません。', '公開終了後のページが公開側に表示されています。');
        test.assertNoText('body', scenarioPage.title, '公開終了後のページのタイトルが公開側に表示されています。');

        test.visit('/admin/page');
    },
    // ページ編集ページに移動
    function() {
        test.click(codeRow(scenarioPage.code).find('a:contains("編集")'), '一覧の ' + scenarioPage.code + ' の編集リンクが見つかりません。');
    },
    // ページを削除
    function() {
        var form = $('form.delete');

        test.assertValue('form.register input[name="code"]', scenarioPage.code, '削除対象が ' + scenarioPage.code + ' ではありません。');
        test.assertValue('form.register input[name="public_end"]', periodPast, '編集した公開終了日時が編集画面に復元されていません。');
        test.assert(form.length === 1, '削除フォームが表示されていません。');

        form.off('submit');
        test.click('form.delete button[type="submit"]');
    },
    // 削除できたことを確認してホームに移動
    function() {
        test.assertText('div.alert-success', 'ページを削除しました。', 'ページを削除できていません。');
        test.assert(codeRow(scenarioPage.code).length === 0, '削除したページが一覧に残っています。');

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
