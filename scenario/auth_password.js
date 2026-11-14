/* テスト用の会員（test オブジェクトはページごとに作り直されるため、値は定数で持つ） */
var scenarioMember = {
    username:  'scenario-reset',
    password:  'scenario5678',
    name:      'シナリオ会員',
    email:     'scenario-reset@example.com',
    authority: 'ゲスト'
};

/* 再設定するパスワード */
var newPassword = 'scenario9012';

/* パスワード再設定メールの件名（設定 mail_password_subject の初期値） */
var passwordSubject = 'パスワード再設定';

/* 暗証コードは service_user_code() の rand_number(10000000, 99999999) なので、00000000 は絶対に一致しない */
var wrongTokenCode = '00000000';

/*
 * ステップ間で値を引き継ぐための保存先。
 * test オブジェクトはページごとに作り直されるので、sessionStorageを使う。
 * パスワード再登録のURLは token を含んでいてシナリオからは組み立てられないため、
 * メールを見に行く前に控えておき、戻ってくるのに使う。
 */
var storageKey = {
    url:  'scenario_password_url',
    code: 'scenario_password_code'
};

/* 現在のURLを、test.visit() に渡せる形（MAIN_FILE 以降）で取り出す */
var currentPath = function() {
    var path = location.pathname + location.search;

    if (test.config.main_file && path.indexOf(test.config.main_file) === 0) {
        path = path.substring(test.config.main_file.length);
    }

    return path;
};

/* ユーザー一覧から、ユーザー名で対象の行を取得する（メールアドレスの列も code なので先頭の td だけを見る） */
var userRow = function(username) {
    return $('table tbody tr').filter(function() {
        return $(this).find('td').first().find('code').text().trim() === username;
    });
};

/* 選択肢から、表示名で対象を取得する */
var optionByText = function(form, name, text) {
    return form.find('select[name="' + name + '"] option').filter(function() {
        return $(this).text() === text;
    });
};

/* 入力欄の直後に表示された入力エラーを取得する */
var fieldWarning = function(name) {
    return $('form.register [name="' + name + '"]').parent().find('div.warning');
};

/* ログインフォームから送信する */
var login = function(username, password) {
    var form = $('form:eq(0)');

    test.assert(form.find('input[name="username"]').length === 1, 'ログインフォームが表示されていません。');

    form.find('input[name="username"]').val(username);
    form.find('input[name="password"]').val(password);
    test.click('form:eq(0) button[type="submit"]');
};

/* 管理者用ページからログアウトする */
var logout = function() {
    test.click('a:contains("管理者さん")');
    setTimeout(function() {
        test.click('a:contains("ログアウト")');
    }, 500);
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
    // ログインできたことを確認してユーザー管理ページに移動
    function() {
        test.assertText('body', '管理者さん', 'ログインできていません。');
        test.click('a:contains("ユーザー管理")');
    },
    // 前回のテストデータが残っていないことを確認してユーザー登録ページに移動
    function() {
        test.assert(userRow(scenarioMember.username).length === 0, 'ユーザー ' + scenarioMember.username + ' が残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("ユーザー登録")');
    },
    // テスト用の会員を登録
    function() {
        var form      = $('form.register');
        var authority = optionByText(form, 'authority_id', scenarioMember.authority);

        test.assert(form.find('input[name="username"]').length === 1, 'ユーザー登録フォームが表示されていません。');
        test.assert(authority.length === 1, '権限「' + scenarioMember.authority + '」がありません。');

        form.find('input[name="username"]').val(scenarioMember.username);
        form.find('input[name="password"]').val(scenarioMember.password);
        form.find('input[name="password_confirm"]').val(scenarioMember.password);
        form.find('input[name="name"]').val(scenarioMember.name);
        form.find('input[name="email"]').val(scenarioMember.email);
        form.find('select[name="authority_id"]').val(authority.val());
        form.find('select[name="enabled"]').val('1');
        test.click('form.register button[type="submit"]');
    },
    // 登録できたことを確認してログアウト
    function() {
        test.assertText('div.alert-success', 'ユーザーを登録しました。', 'ユーザーを登録できていません。');
        test.assert(userRow(scenarioMember.username).length === 1, '登録した会員が一覧にありません。');

        logout();
    },
    // パスワード再発行ページに移動
    function() {
        test.assertExists('input[name="username"]', 'ログアウトできていません。');
        test.click('a:contains("パスワード再発行")', 'ログインページにパスワード再発行へのリンクがありません。');
    },
    // 登録されていないメールアドレスでは受け付けられないことを確認
    function() {
        var form = $('form.register');

        test.assert(form.find('input[name="email"]').length === 1, 'パスワード再発行フォームが表示されていません。');

        $('div.warning').remove();
        form.find('input[name="email"]').val('scenario-unknown@example.com');
        test.click('form.register button[type="submit"]');
        test.wait(function() {
            return $('form.register div.warning').length > 0;
        }, function() {
            test.assertText(fieldWarning('email'), '指定されたメールアドレスが見つかりません。', '登録されていないメールアドレスのエラーが表示されていません。');

            test.reload();
        }, '登録されていないメールアドレスでも入力エラーが表示されませんでした。');
    },
    // 登録されているメールアドレスで再発行を申し込む
    function() {
        var form = $('form.register');

        form.find('input[name="email"]').val(scenarioMember.email);
        test.click('form.register button[type="submit"]');
    },
    // 誤った暗証コードが拒否されることを確認して、記録されたメールを開く
    function() {
        var form = $('form.register');

        test.assert(form.find('input[name="token_code"]').length === 1, 'パスワード再登録フォームが表示されていません。');

        // このページのURLは token を含んでいてシナリオからは組み立てられないので、控えてから離れる
        sessionStorage.setItem(storageKey.url, currentPath());

        $('div.warning').remove();
        form.find('input[name="token_code"]').val(wrongTokenCode);
        form.find('input[name="password"]').val(newPassword);
        form.find('input[name="password_confirm"]').val(newPassword);
        test.click('form.register button[type="submit"]');
        test.wait(function() {
            return $('form.register div.warning').length > 0;
        }, function() {
            test.assertText(fieldWarning('token_code'), '暗証コードが違います。', '誤った暗証コードが拒否されていません。');
            test.assert($('form.register div.warning').length === 1, '暗証コード以外にも入力エラーが表示されています。（' + $('form.register div.warning').text() + '）');

            // mail_log の記録は <時刻>_<宛先>.txt。/tool/test/mail は指定の時刻から10秒前まで遡って探す
            test.visit('/tool/test/mail?date=' + test.date + '&filename=' + encodeURIComponent(test.time + '_' + scenarioMember.email));
        }, '誤った暗証コードでも入力エラーが表示されませんでした。');
    },
    // メールから認証コードを読み取って、パスワード再登録ページに戻る
    function() {
        test.assertText('body', 'to: ' + scenarioMember.email, 'パスワード再設定のメールが記録されていません。（mail_log が無効になっている可能性があります）');
        test.assertText('body', 'subject: ' + passwordSubject, 'メールの件名が「' + passwordSubject + '」になっていません。');

        var matches = $('body').text().match(/認証コード\s*(\d{8})/);
        if (!test.assert(matches !== null, 'メールの本文から認証コードを読み取れません。')) {
            return;
        }

        // 有効期限（token_expire）が本文に入っていること
        test.assert(/有効期限\s*\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}/.test($('body').text()), 'メールの本文に認証コードの有効期限が入っていません。');

        sessionStorage.setItem(storageKey.code, matches[1]);
        test.visit(sessionStorage.getItem(storageKey.url));
    },
    // メールの認証コードと新しいパスワードで再設定する
    function() {
        var form = $('form.register');
        var code = sessionStorage.getItem(storageKey.code);

        test.assert(form.find('input[name="token_code"]').length === 1, 'パスワード再登録ページに戻れていません。');
        if (!test.assert(code !== null && code !== '', 'メールから読み取った認証コードを引き継げていません。')) {
            return;
        }

        sessionStorage.removeItem(storageKey.url);
        sessionStorage.removeItem(storageKey.code);

        form.find('input[name="token_code"]').val(code);
        form.find('input[name="password"]').val(newPassword);
        form.find('input[name="password_confirm"]').val(newPassword);
        test.click('form.register button[type="submit"]');
    },
    // 再設定できたことを確認して、ログインページに移動
    function() {
        test.assertText('main', 'パスワードを再設定しました。', 'パスワードを再設定できていません。');

        test.visit('/auth/');
    },
    // 新しいパスワードでログイン
    function() {
        login(scenarioMember.username, newPassword);
    },
    // 会員ページが表示されることを確認してログアウト
    function() {
        test.assertText('main', 'ようこそ、' + scenarioMember.name + 'さん', '新しいパスワードでログインできていません。');

        test.visit('/auth/logout');
    },
    // 管理者用ページにログイン
    function() {
        test.assertExists('input[name="username"]', 'ログアウトできていません。');
        login('admin', 'abcd1234');
    },
    // ログインできたことを確認してユーザー管理ページに移動
    function() {
        test.assertText('body', '管理者さん', 'ログインできていません。');
        test.click('a:contains("ユーザー管理")');
    },
    // 会員の編集ページに移動
    function() {
        test.click(userRow(scenarioMember.username).find('a'), '一覧の ' + scenarioMember.username + ' の編集リンクが見つかりません。');
    },
    // 会員を削除
    function() {
        var form = $('form.delete');

        test.assertValue('form.register input[name="username"]', scenarioMember.username, '削除対象が ' + scenarioMember.username + ' ではありません。');
        test.assert(form.length === 1, '削除フォームが表示されていません。');

        form.off('submit');
        test.click('form.delete button[type="submit"]');
    },
    // 削除できたことを確認してホームに移動
    function() {
        test.assertText('div.alert-success', 'ユーザーを削除しました。', 'ユーザーを削除できていません。');
        test.assert(userRow(scenarioMember.username).length === 0, '削除した会員が一覧に残っています。');

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
