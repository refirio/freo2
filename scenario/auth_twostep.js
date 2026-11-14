/* テスト用の会員（test オブジェクトはページごとに作り直されるため、値は定数で持つ）。権限は最小の「ゲスト」にする */
var scenarioMember = {
    username:     'scenario-twostep',
    password:     'scenario5678',
    email:        'scenario-twostep@example.com',
    twostepEmail: 'scenario-twostep-code@example.com',
    authority:    'ゲスト'
};

/* メールから読み取ったコードの控え（メールを見に行くとコードの画面には戻れないため、sessionStorageに置く） */
var scenarioKeyCode = 'scenario_twostep_code';

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

/* ログインフォームから送信する */
var login = function(username, password) {
    var form = $('form:eq(0)');

    test.assert(form.find('input[name="username"]').length === 1, 'ログインフォームが表示されていません。');

    form.find('input[name="username"]').val(username);
    form.find('input[name="password"]').val(password);
    test.click('form:eq(0) button[type="submit"]');
};

/* コードの画面のフォーム（認証と再送信でフォームが分かれている） */
var twostepForm = function(exec) {
    return $('input[name="exec"][value="' + exec + '"]').closest('form');
};

/* 画面の本文を短くまとめる（失敗したときのメッセージ用） */
var mainText = function() {
    return $('main').text().replace(/\s+/g, ' ').trim().substring(0, 200);
};

test.scenario = [
    // 初期ページからログインページに移動
    function() {
        test.click('a:contains("ログイン")');
    },
    // 管理者でログイン
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
    // 2段階認証を「利用する」にした会員を登録（2段階認証用メールアドレスの必須も確認）
    function() {
        var form      = $('form.register');
        var authority = optionByText(form, 'authority_id', scenarioMember.authority);

        test.assert(form.find('select[name="twostep"]').length === 1, 'ユーザー登録画面に2段階認証の欄がありません。');
        test.assert(authority.length === 1, '権限「' + scenarioMember.authority + '」がありません。');
        test.assert(form.find('.for-twostep').is(':hidden'), '初期状態で2段階認証用メールアドレスの欄が出ています。');

        form.find('input[name="username"]').val(scenarioMember.username);
        form.find('input[name="password"]').val(scenarioMember.password);
        form.find('input[name="password_confirm"]').val(scenarioMember.password);
        form.find('input[name="email"]').val(scenarioMember.email);
        authority.prop('selected', true);
        form.find('select[name="twostep"]').val('1').trigger('change');

        test.assert(form.find('.for-twostep').is(':visible'), '「利用する」にしても2段階認証用メールアドレスの欄が出ません。');

        // メールアドレスを空のまま送ると、必須の入力エラーだけが出ること
        $('div.warning').remove();
        form.find('button[type="submit"]').trigger('click');

        test.wait(function() {
            return form.find('div.warning').length > 0;
        }, function() {
            test.assertText('form.register', '2段階認証用メールアドレスが入力されていません。', '2段階認証用メールアドレスの必須の入力エラーが出ていません。');
            test.assert(form.find('div.warning').length === 1, '必須以外にも入力エラーが出ています。（' + form.find('div.warning').text() + '）');

            form.find('input[name="twostep_email"]').val(scenarioMember.twostepEmail);
            test.click('form.register button[type="submit"]');
        }, '2段階認証用メールアドレスの入力エラーが表示されませんでした。');
    },
    // 登録できたことを確認してログアウト
    function() {
        test.assertText('div.alert-success', 'ユーザーを登録しました。', 'ユーザーを登録できていません。（' + mainText() + '）');
        test.assert(userRow(scenarioMember.username).length === 1, '登録した会員が一覧にありません。');

        test.visit('/auth/logout');
    },
    // 会員でログイン
    function() {
        login(scenarioMember.username, scenarioMember.password);
    },
    // コードの画面になり、パスワードを受け渡していないことを確認して、誤ったコードを送る
    function() {
        var form = twostepForm('twostep');

        test.assert(form.find('input[name="twostep_code"]').length === 1, '2段階認証のコードを求められません。（' + mainText() + '）');
        test.assertNotExists('input[name="password"]', 'コードの画面にパスワードの欄が残っています。');
        test.assertNotExists('input[name="username"]', 'コードの画面にユーザー名の欄が残っています。');

        form.find('input[name="twostep_code"]').val('zzzzzzzz');
        test.click(form.find('button[type="submit"]'), 'コードの画面に認証のボタンがありません。');
    },
    // 誤りの表示を確認して、コードを再送信する
    function() {
        test.assertText('div.alert-danger', '2段階認証用コードが違います。', '誤ったコードが弾かれていません。（' + mainText() + '）');
        test.click(twostepForm('twostep_resend').find('button[type="submit"]'), 'コードの再送信のボタンがありません。');
    },
    // 再送信してもコードの画面のままであることを確認して、届いたメールを開く
    function() {
        test.assertExists('input[name="twostep_code"]', '再送信した後にコードの画面になっていません。（' + mainText() + '）');
        test.assertNotExists('div.alert-danger', '再送信した後にエラーが出ています。');

        // mail_log の記録は <時刻>_<宛先>.txt。/tool/test/mail は指定の時刻から10秒前まで遡って、近いものを返す（再送信したほう）
        test.visit('/tool/test/mail?date=' + test.date + '&filename=' + encodeURIComponent(test.time + '_' + scenarioMember.twostepEmail));
    },
    // メールからコードを読み取り、ログインページに戻る
    function() {
        var matches = $('body').text().match(/コードは\s*(\d{8})/);

        if (!test.assert(matches !== null, '2段階認証用メールアドレスにコードが届いていません。')) {
            return;
        }

        sessionStorage.setItem(scenarioKeyCode, matches[1]);
        test.visit('/auth/');
    },
    // 正しいコードを送り、このブラウザを信頼する
    // （コードの画面はPOSTの結果なので、メールを見に行くと戻れない。認証待ちの状態はセッションに残っているので、同じ内容のフォームを組み立てて送る）
    function() {
        var code  = sessionStorage.getItem(scenarioKeyCode);
        var token = $('form input[name="_token"]').first().val();
        var form  = $('<form method="post"></form>').attr('action', location.pathname);

        sessionStorage.removeItem(scenarioKeyCode);

        test.assert(token, 'ログイン画面にワンタイムトークンがありません。');

        form.append($('<input type="hidden" name="_token">').val(token));
        form.append($('<input type="hidden" name="exec" value="twostep">'));
        form.append($('<input type="hidden" name="twostep_code">').val(code));
        form.append($('<input type="hidden" name="twostep_session" value="keep">'));
        form.append($('<button type="submit" id="scenario_twostep_submit">'));
        $('body').append(form);

        test.click('#scenario_twostep_submit');
    },
    // ログインできたことを確認してログアウト
    function() {
        test.assertExists('a[href*="/auth/logout"]', '正しいコードでログインできていません。（' + mainText() + '）');
        test.visit('/auth/logout');
    },
    // 同じブラウザでもう一度ログイン
    function() {
        login(scenarioMember.username, scenarioMember.password);
    },
    // 信頼したブラウザなので、コードを求められずにログインできることを確認して、ユーザー情報編集に移動
    function() {
        test.assertNotExists('input[name="twostep_code"]', '信頼したブラウザなのにコードを求められました。');
        test.assertExists('a[href*="/auth/logout"]', 'コードを省略してログインできていません。（' + mainText() + '）');

        test.visit('/auth/modify');
    },
    // 本人が2段階認証を「利用しない」にする
    function() {
        var form = $('form.register');

        test.assertValue('form.register select[name="twostep"]', '1', 'ユーザー情報編集に2段階認証の設定が出ていません。');
        test.assertValue('form.register input[name="twostep_email"]', scenarioMember.twostepEmail, 'ユーザー情報編集に2段階認証用メールアドレスが出ていません。');

        form.find('select[name="twostep"]').val('0').trigger('change');

        test.assert(form.find('.for-twostep').is(':hidden'), '「利用しない」にしても2段階認証用メールアドレスの欄が消えません。');

        test.click('form.register button[type="submit"]');
    },
    // 確認画面を確認して登録
    function() {
        test.assertText('main dl', '利用しない', '確認画面に「利用しない」が出ていません。（' + mainText() + '）');
        test.assertNoText('main dl', scenarioMember.twostepEmail, '「利用しない」なのに確認画面に2段階認証用メールアドレスが出ています。');

        test.click('main form button[type="submit"]');
    },
    // 完了を確認してログアウト
    function() {
        test.assert(location.pathname.indexOf('modify_complete') >= 0, 'ユーザー情報を編集できていません。（' + mainText() + '）');
        test.visit('/auth/logout');
    },
    // 無効にしたのでコードを求められずにログインできることを確認して、ユーザー情報編集に移動
    function() {
        login(scenarioMember.username, scenarioMember.password);
    },
    function() {
        test.assertNotExists('input[name="twostep_code"]', '2段階認証を無効にしたのにコードを求められました。');
        test.assertExists('a[href*="/auth/logout"]', 'ログインできていません。（' + mainText() + '）');

        test.visit('/auth/modify');
    },
    // 同じメールアドレスのまま、もう一度「利用する」にする
    function() {
        var form = $('form.register');

        form.find('select[name="twostep"]').val('1').trigger('change');

        test.assertValue('form.register input[name="twostep_email"]', scenarioMember.twostepEmail, '2段階認証用メールアドレスが残っていません。');
        test.click('form.register button[type="submit"]');
    },
    // 確認画面: 有効にするので、次の画面でコードを確認する旨が出ていること
    function() {
        test.assertText('main dl', '利用する', '確認画面に「利用する」が出ていません。（' + mainText() + '）');
        test.assertText('main', '次の画面で、2段階認証用メールアドレスに送るコードを入力して', '確認画面に、コードを確認する旨が出ていません。');

        test.click('main form button[type="submit"]');
    },
    // コードの入力画面: 送り先が出ていることを確認して、誤ったコードを送る
    function() {
        var form = twostepForm('verify');

        test.assert(location.pathname.indexOf('modify_verify') >= 0, '2段階認証用メールアドレスの確認画面になっていません。（' + mainText() + '）');
        test.assertText('main', scenarioMember.twostepEmail, '確認画面に送り先のメールアドレスが出ていません。');

        form.find('input[name="twostep_code"]').val('zzzzzzzz');
        test.click(form.find('button[type="submit"]'), 'コードの入力画面に登録のボタンがありません。');
    },
    // 誤りの表示を確認して、コードを再送信する
    function() {
        test.assertText('div.alert-danger', '2段階認証用コードが違います。', '誤ったコードが弾かれていません。（' + mainText() + '）');
        test.click(twostepForm('resend').find('button[type="submit"]'), 'コードの入力画面に再送信のボタンがありません。');
    },
    // 再送信してもコードの入力画面のままであることを確認して、届いたメールを開く（近いほう＝再送信したほうが返る）
    function() {
        test.assertExists('input[name="twostep_code"]', '再送信した後にコードの入力画面になっていません。（' + mainText() + '）');
        test.assertNotExists('div.alert-danger', '再送信した後にエラーが出ています。');

        test.visit('/tool/test/mail?date=' + test.date + '&filename=' + encodeURIComponent(test.time + '_' + scenarioMember.twostepEmail));
    },
    // メールからコードを読み取り、コードの入力画面に戻る（開き直してもコードは送り直さない）
    function() {
        var matches = $('body').text().match(/コードは\s*(\d{8})/);

        if (!test.assert(matches !== null, '2段階認証用メールアドレスにコードが届いていません。')) {
            return;
        }

        sessionStorage.setItem(scenarioKeyCode, matches[1]);
        test.visit('/auth/modify_verify');
    },
    // 正しいコードを送って登録する
    function() {
        var form = twostepForm('verify');
        var code = sessionStorage.getItem(scenarioKeyCode);

        sessionStorage.removeItem(scenarioKeyCode);

        test.assert(form.length === 1, 'コードの入力画面に戻れていません。（' + mainText() + '）');

        form.find('input[name="twostep_code"]').val(code);
        test.click(form.find('button[type="submit"]'), 'コードの入力画面に登録のボタンがありません。');
    },
    function() {
        test.assert(location.pathname.indexOf('modify_complete') >= 0, 'ユーザー情報を編集できていません。（' + mainText() + '）');
        test.visit('/auth/logout');
    },
    // 無効にしたときに信頼が解除されているので、同じブラウザでもコードを求められること
    function() {
        login(scenarioMember.username, scenarioMember.password);
    },
    function() {
        test.assertExists('input[name="twostep_code"]', '2段階認証を無効にした後なのに、信頼したブラウザのままコードを省略されました。');

        // コードは入力せずにログインページに戻る（認証待ちの状態は、次のログインで破棄される）
        test.visit('/auth/');
    },
    // 後始末: 管理者でログインして会員を削除する
    function() {
        login('admin', 'abcd1234');
    },
    function() {
        test.assertText('body', '管理者さん', 'ログインできていません。');
        test.click('a:contains("ユーザー管理")');
    },
    function() {
        test.click(userRow(scenarioMember.username).find('a').first(), '一覧に ' + scenarioMember.username + ' の編集リンクがありません。');
    },
    function() {
        var form = $('form.delete');

        test.assertValue('form.register input[name="username"]', scenarioMember.username, '削除対象が ' + scenarioMember.username + ' ではありません。');
        test.assert(form.length === 1, '削除フォームが表示されていません。');

        form.off('submit');
        test.click('form.delete button[type="submit"]');
    },
    // 削除できたことを確認してログアウト
    function() {
        test.assertText('div.alert-success', 'ユーザーを削除しました。', 'ユーザーを削除できていません。');
        test.assert(userRow(scenarioMember.username).length === 0, '削除した会員が一覧に残っています。');

        test.click('a:contains("ログアウト")');
    }
];
