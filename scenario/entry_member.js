/* テスト用の属性（test オブジェクトはページごとに作り直されるため、値は定数で持つ） */
var scenarioAttribute = {
    name: 'シナリオ属性'
};

/* テスト用の会員。属性による絞り込みを見るため、権限は最小の「ゲスト」にする */
var scenarioMember = {
    username:  'scenario-guest',
    password:  'scenario5678',
    name:      'シナリオ会員',
    email:     'scenario-guest@example.com',
    authority: 'ゲスト'
};

/* 「登録ユーザーに公開」のエントリー */
var userEntry = {
    code:  'scenario-public-user',
    title: 'シナリオの登録ユーザー公開'
};

/* 「指定の属性に公開」のエントリー */
var attributeEntry = {
    code:  'scenario-public-attribute',
    title: 'シナリオの属性公開'
};

/* エントリー一覧から、コードで対象の行を取得する */
var codeRow = function(code) {
    return $('table tbody tr').filter(function() {
        return $(this).find('td code').text().trim() === code;
    });
};

/* ユーザー一覧から、ユーザー名で対象の行を取得する（メールアドレスの列も code なので先頭の td だけを見る） */
var userRow = function(username) {
    return $('table tbody tr').filter(function() {
        return $(this).find('td').first().find('code').text().trim() === username;
    });
};

/* 属性一覧から、名前で対象の行を取得する（属性はコードを持たない） */
var attributeRow = function(name) {
    return $('table tbody tr').filter(function() {
        return $(this).find('td').first().text().trim() === name;
    });
};

/* 選択肢から、表示名で対象を取得する */
var optionByText = function(form, name, text) {
    return form.find('select[name="' + name + '"] option').filter(function() {
        return $(this).text() === text;
    });
};

/* 属性のチェックボックスを、属性名で取得する（エントリー・ユーザーで同じ作り） */
var attributeCheckbox = function(name) {
    return $('#validate_attribute_sets label').filter(function() {
        return $(this).text().trim() === name;
    }).find('input[type="checkbox"]');
};

/* ログインフォームから送信する */
var login = function(username, password) {
    var form = $('form:eq(0)');

    test.assert(form.find('input[name="username"]').length === 1, 'ログインフォームが表示されていません。');

    form.find('input[name="username"]').val(username);
    form.find('input[name="password"]').val(password);
    test.click('form:eq(0) button[type="submit"]');
};

/* 公開範囲を指定してエントリーを登録する */
var registerEntry = function(entry, published, attributeName) {
    var form = $('form.register');

    test.assert(form.find('input[name="code"]').length === 1, 'エントリー登録フォームが表示されていません。');

    form.find('input[name="code"]').val(entry.code);
    form.find('input[name="title"]').val(entry.title);
    form.find('select[name="public"]').val(published).trigger('change');

    if (attributeName) {
        test.assert(attributeCheckbox(attributeName).length === 1, 'エントリー登録画面に属性「' + attributeName + '」がありません。');

        attributeCheckbox(attributeName).prop('checked', true);
    }

    test.click('form.register button[type="submit"]');
};

/* 編集ページで削除する */
var deleteRecord = function(selector, value, label) {
    var form = $('form.delete');

    test.assertValue(selector, value, '削除対象が ' + label + ' ではありません。');
    test.assert(form.length === 1, '削除フォームが表示されていません。');

    form.off('submit');
    test.click('form.delete button[type="submit"]');
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
    // ログインできたことを確認して属性管理ページに移動
    function() {
        test.assertText('body', '管理者さん', 'ログインできていません。');
        test.click('a:contains("属性管理")');
    },
    // 前回のテストデータが残っていないことを確認して属性登録ページに移動
    function() {
        test.assert(attributeRow(scenarioAttribute.name).length === 0, '属性 ' + scenarioAttribute.name + ' が残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("属性登録")');
    },
    // 属性を登録
    function() {
        var form = $('form.register');

        test.assert(form.find('input[name="name"]').length === 1, '属性登録フォームが表示されていません。');

        form.find('input[name="name"]').val(scenarioAttribute.name);
        test.click('form.register button[type="submit"]');
    },
    // 登録できたことを確認してユーザー管理ページに移動
    function() {
        test.assertText('div.alert-success', '属性を登録しました。', '属性を登録できていません。');
        test.assert(attributeRow(scenarioAttribute.name).length === 1, '登録した属性が一覧にありません。');

        test.click('a:contains("ユーザー管理")');
    },
    // 前回のテストデータが残っていないことを確認してユーザー登録ページに移動
    function() {
        test.assert(userRow(scenarioMember.username).length === 0, 'ユーザー ' + scenarioMember.username + ' が残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("ユーザー登録")');
    },
    // 属性を持たないゲストのユーザーを登録
    function() {
        var form      = $('form.register');
        var authority = optionByText(form, 'authority_id', scenarioMember.authority);

        test.assert(form.find('input[name="username"]').length === 1, 'ユーザー登録フォームが表示されていません。');
        test.assert(authority.length === 1, '権限「' + scenarioMember.authority + '」がありません。');
        test.assert(attributeCheckbox(scenarioAttribute.name).length === 1, 'ユーザー登録画面に属性「' + scenarioAttribute.name + '」がありません。');

        form.find('input[name="username"]').val(scenarioMember.username);
        form.find('input[name="password"]').val(scenarioMember.password);
        form.find('input[name="password_confirm"]').val(scenarioMember.password);
        form.find('input[name="name"]').val(scenarioMember.name);
        form.find('input[name="email"]').val(scenarioMember.email);
        form.find('select[name="authority_id"]').val(authority.val());
        form.find('select[name="enabled"]').val('1');
        test.click('form.register button[type="submit"]');
    },
    // 登録できたことを確認してエントリー管理ページに移動
    function() {
        test.assertText('div.alert-success', 'ユーザーを登録しました。', 'ユーザーを登録できていません。');
        test.assertText(userRow(scenarioMember.username), scenarioMember.authority, '登録したユーザーの権限が「' + scenarioMember.authority + '」になっていません。');

        test.click('a:contains("エントリー管理")');
    },
    // 前回のテストデータが残っていないことを確認してエントリー登録ページに移動
    function() {
        test.assert(codeRow(userEntry.code).length === 0, 'エントリー ' + userEntry.code + ' が残っています。前回のテストが中断した可能性があります。');
        test.assert(codeRow(attributeEntry.code).length === 0, 'エントリー ' + attributeEntry.code + ' が残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("エントリー登録")');
    },
    // 「登録ユーザーに公開」のエントリーを登録
    function() {
        registerEntry(userEntry, 'user');
    },
    // 登録できたことを確認して、もう一度エントリー登録ページに移動
    function() {
        test.assertText('div.alert-success', 'エントリーを登録しました。', 'エントリーを登録できていません。');
        test.assertValue('form.register input[name="code"]', userEntry.code, '登録した後に、登録したエントリーの編集画面が表示されていません。');
        test.assertValue('form.register select[name="public"]', 'user', '公開が「登録ユーザーに公開」になっていません。');

        test.visit('/admin/entry_form');
    },
    // 「指定の属性に公開」のエントリーを登録
    function() {
        registerEntry(attributeEntry, 'attribute', scenarioAttribute.name);
    },
    // 登録できたことを確認してログアウト
    function() {
        test.assertText('div.alert-success', 'エントリーを登録しました。', 'エントリーを登録できていません。');
        test.assertValue('form.register input[name="code"]', attributeEntry.code, '登録した後に、登録したエントリーの編集画面が表示されていません。');
        test.assertValue('form.register select[name="public"]', 'attribute', '公開が「指定の属性に公開」になっていません。');

        test.visit('/auth/logout');
    },
    // 未ログインで「登録ユーザーに公開」のエントリーを開く
    function() {
        test.assertExists('input[name="username"]', 'ログアウトできていません。');
        test.visit('/entry/detail/' + userEntry.code);
    },
    // 未ログインでは見えないことを確認して、「指定の属性に公開」のエントリーを開く
    function() {
        test.assertText('div.alert-danger', 'エントリーが見つかりません。', '「登録ユーザーに公開」のエントリーが未ログインで表示されています。');
        test.visit('/entry/detail/' + attributeEntry.code);
    },
    // 未ログインでは見えないことを確認して、ログインページに移動
    function() {
        test.assertText('div.alert-danger', 'エントリーが見つかりません。', '「指定の属性に公開」のエントリーが未ログインで表示されています。');
        test.visit('/auth/');
    },
    // 属性を持たない会員でログイン
    function() {
        login(scenarioMember.username, scenarioMember.password);
    },
    // 会員ページが表示されることを確認して、「登録ユーザーに公開」のエントリーを開く
    function() {
        test.assertText('main', 'ようこそ、' + scenarioMember.name + 'さん', '会員でログインできていません。');
        test.visit('/entry/detail/' + userEntry.code);
    },
    // 会員には見えることを確認して、「指定の属性に公開」のエントリーを開く
    function() {
        test.assertText('#entry h2', userEntry.title, '「登録ユーザーに公開」のエントリーが会員に表示されていません。');
        test.visit('/entry/detail/' + attributeEntry.code);
    },
    // 属性を持たない会員には見えないことを確認してログアウト
    function() {
        test.assertText('div.alert-danger', 'エントリーが見つかりません。', '属性を持たない会員に「指定の属性に公開」のエントリーが表示されています。');
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
    // 会員に属性を付与
    function() {
        test.assertValue('form.register input[name="username"]', scenarioMember.username, '編集対象が ' + scenarioMember.username + ' ではありません。');
        test.assert(attributeCheckbox(scenarioAttribute.name).prop('checked') === false, '会員にまだ属性が付いていないはずです。');

        attributeCheckbox(scenarioAttribute.name).prop('checked', true);
        test.click('form.register button[type="submit"]');
    },
    // 付与できたことを確認してログアウト
    function() {
        test.assertText('div.alert-success', 'ユーザーを登録しました。', '会員に属性を付与できていません。');

        test.visit('/auth/logout');
    },
    // 属性を持つようになった会員でログイン
    function() {
        test.assertExists('input[name="username"]', 'ログアウトできていません。');
        login(scenarioMember.username, scenarioMember.password);
    },
    // 会員ページが表示されることを確認して、「指定の属性に公開」のエントリーを開く
    function() {
        test.assertText('main', 'ようこそ、' + scenarioMember.name + 'さん', '会員でログインできていません。');
        test.visit('/entry/detail/' + attributeEntry.code);
    },
    // 属性を持つ会員には見えることを確認してログアウト
    function() {
        test.assertText('#entry h2', attributeEntry.title, '属性を持つ会員に「指定の属性に公開」のエントリーが表示されていません。');

        test.visit('/auth/logout');
    },
    // 管理者用ページにログイン
    function() {
        test.assertExists('input[name="username"]', 'ログアウトできていません。');
        login('admin', 'abcd1234');
    },
    // ログインできたことを確認してエントリー管理ページに移動
    function() {
        test.assertText('body', '管理者さん', 'ログインできていません。');
        test.click('a:contains("エントリー管理")');
    },
    // 「登録ユーザーに公開」のエントリーの編集ページに移動
    function() {
        test.click(codeRow(userEntry.code).find('a:contains("編集")'), '一覧の ' + userEntry.code + ' の編集リンクが見つかりません。');
    },
    // 削除
    function() {
        deleteRecord('form.register input[name="code"]', userEntry.code, userEntry.code);
    },
    // 削除できたことを確認して、「指定の属性に公開」のエントリーの編集ページに移動
    function() {
        test.assertText('div.alert-success', 'エントリーを削除しました。', 'エントリーを削除できていません。');
        test.assert(codeRow(userEntry.code).length === 0, '削除したエントリーが一覧に残っています。');

        test.click(codeRow(attributeEntry.code).find('a:contains("編集")'), '一覧の ' + attributeEntry.code + ' の編集リンクが見つかりません。');
    },
    // 削除
    function() {
        deleteRecord('form.register input[name="code"]', attributeEntry.code, attributeEntry.code);
    },
    // 削除できたことを確認してユーザー管理ページに移動
    function() {
        test.assertText('div.alert-success', 'エントリーを削除しました。', 'エントリーを削除できていません。');
        test.assert(codeRow(attributeEntry.code).length === 0, '削除したエントリーが一覧に残っています。');

        test.click('a:contains("ユーザー管理")');
    },
    // 会員の編集ページに移動
    function() {
        test.click(userRow(scenarioMember.username).find('a'), '一覧の ' + scenarioMember.username + ' の編集リンクが見つかりません。');
    },
    // 会員を削除
    function() {
        deleteRecord('form.register input[name="username"]', scenarioMember.username, scenarioMember.username);
    },
    // 削除できたことを確認して属性管理ページに移動
    function() {
        test.assertText('div.alert-success', 'ユーザーを削除しました。', 'ユーザーを削除できていません。');
        test.assert(userRow(scenarioMember.username).length === 0, '削除した会員が一覧に残っています。');

        test.click('a:contains("属性管理")');
    },
    // 属性の編集ページに移動
    function() {
        test.click(attributeRow(scenarioAttribute.name).find('a'), '一覧の ' + scenarioAttribute.name + ' の編集リンクが見つかりません。');
    },
    // 属性を削除
    function() {
        deleteRecord('form.register input[name="name"]', scenarioAttribute.name, scenarioAttribute.name);
    },
    // 削除できたことを確認してホームに移動
    function() {
        test.assertText('div.alert-success', '属性を削除しました。', '属性を削除できていません。');
        test.assert(attributeRow(scenarioAttribute.name).length === 0, '削除した属性が一覧に残っています。');

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
