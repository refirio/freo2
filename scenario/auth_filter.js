/* テスト用の属性（test オブジェクトはページごとに作り直されるため、値は定数で持つ） */
var filterAttribute = {
    name: 'シナリオフィルター'
};
var generalAttribute = {
    name: 'シナリオ一般'
};

/* テスト用の会員。フィルターはゲストだけが対象なので、権限は「ゲスト」にする */
var scenarioMember = {
    username:  'scenario-filter',
    password:  'scenario5678',
    name:      'シナリオフィルター会員',
    email:     'scenario-filter@example.com',
    authority: 'ゲスト'
};

/* フィルター対象の属性に公開するエントリー */
var filterEntry = {
    code:  'scenario-filter-target',
    title: 'シナリオのフィルター対象'
};

/* フィルター対象外の属性に公開するエントリー */
var generalEntry = {
    code:  'scenario-filter-general',
    title: 'シナリオのフィルター対象外'
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

/* フィルターのチェックボックスを、属性名で取得する */
var filterCheckbox = function(name) {
    return $('#attribute_filters label').filter(function() {
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

/* 属性を登録する */
var registerAttribute = function(attribute, filterable) {
    var form = $('form.register');

    test.assert(form.find('input[name="name"]').length === 1, '属性登録フォームが表示されていません。');
    test.assert(form.find('select[name="filterable"] option[value="' + filterable + '"]').length === 1, '属性登録フォームに「フィルター対象」がありません。');

    form.find('input[name="name"]').val(attribute.name);
    form.find('select[name="filterable"]').val(filterable);
    test.click('form.register button[type="submit"]');
};

/* 「指定の属性に公開」でエントリーを登録する */
var registerEntry = function(entry, attributeName) {
    var form = $('form.register');

    test.assert(form.find('input[name="code"]').length === 1, 'エントリー登録フォームが表示されていません。');
    test.assert(attributeCheckbox(attributeName).length === 1, 'エントリー登録画面に属性「' + attributeName + '」がありません。');

    form.find('input[name="code"]').val(entry.code);
    form.find('input[name="title"]').val(entry.title);
    form.find('select[name="public"]').val('attribute').trigger('change');
    attributeCheckbox(attributeName).prop('checked', true);
    test.click('form.register button[type="submit"]');
};

/* フィルターの選択を変えて送信する */
var submitFilter = function(checked) {
    test.assert(filterCheckbox(filterAttribute.name).length === 1, 'フィルターに属性「' + filterAttribute.name + '」がありません。');
    test.assert(filterCheckbox(generalAttribute.name).length === 0, 'フィルター対象外の属性「' + generalAttribute.name + '」がフィルターに表示されています。');

    filterCheckbox(filterAttribute.name).prop('checked', checked);
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
        test.assert(attributeRow(filterAttribute.name).length === 0, '属性 ' + filterAttribute.name + ' が残っています。前回のテストが中断した可能性があります。');
        test.assert(attributeRow(generalAttribute.name).length === 0, '属性 ' + generalAttribute.name + ' が残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("属性登録")');
    },
    // フィルター対象の属性を登録
    function() {
        registerAttribute(filterAttribute, '1');
    },
    // 登録できたことを確認して、もう一度属性登録ページに移動
    function() {
        test.assertText('div.alert-success', '属性を登録しました。', '属性を登録できていません。');
        test.assert(attributeRow(filterAttribute.name).find('span.badge').text().trim() === '対象', '登録した属性が「フィルター対象」になっていません。');

        test.click('a:contains("属性登録")');
    },
    // フィルター対象外の属性を登録
    function() {
        registerAttribute(generalAttribute, '0');
    },
    // 登録できたことを確認してユーザー管理ページに移動
    function() {
        test.assertText('div.alert-success', '属性を登録しました。', '属性を登録できていません。');
        test.assert(attributeRow(generalAttribute.name).find('span.badge').text().trim() === '対象外', '登録した属性が「フィルター対象外」になっていません。');

        test.click('a:contains("ユーザー管理")');
    },
    // 前回のテストデータが残っていないことを確認してユーザー登録ページに移動
    function() {
        test.assert(userRow(scenarioMember.username).length === 0, 'ユーザー ' + scenarioMember.username + ' が残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("ユーザー登録")');
    },
    // 2つの属性を持つゲストのユーザーを登録
    function() {
        var form      = $('form.register');
        var authority = optionByText(form, 'authority_id', scenarioMember.authority);

        test.assert(form.find('input[name="username"]').length === 1, 'ユーザー登録フォームが表示されていません。');
        test.assert(authority.length === 1, '権限「' + scenarioMember.authority + '」がありません。');
        test.assert(attributeCheckbox(filterAttribute.name).length === 1, 'ユーザー登録画面に属性「' + filterAttribute.name + '」がありません。');
        test.assert(attributeCheckbox(generalAttribute.name).length === 1, 'ユーザー登録画面に属性「' + generalAttribute.name + '」がありません。');

        form.find('input[name="username"]').val(scenarioMember.username);
        form.find('input[name="password"]').val(scenarioMember.password);
        form.find('input[name="password_confirm"]').val(scenarioMember.password);
        form.find('input[name="name"]').val(scenarioMember.name);
        form.find('input[name="email"]').val(scenarioMember.email);
        form.find('select[name="authority_id"]').val(authority.val());
        form.find('select[name="enabled"]').val('1');
        attributeCheckbox(filterAttribute.name).prop('checked', true);
        attributeCheckbox(generalAttribute.name).prop('checked', true);
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
        test.assert(codeRow(filterEntry.code).length === 0, 'エントリー ' + filterEntry.code + ' が残っています。前回のテストが中断した可能性があります。');
        test.assert(codeRow(generalEntry.code).length === 0, 'エントリー ' + generalEntry.code + ' が残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("エントリー登録")');
    },
    // フィルター対象の属性に公開するエントリーを登録
    function() {
        registerEntry(filterEntry, filterAttribute.name);
    },
    // 登録できたことを確認して、もう一度エントリー登録ページに移動
    function() {
        test.assertText('div.alert-success', 'エントリーを登録しました。', 'エントリーを登録できていません。');
        test.assertValue('form.register input[name="code"]', filterEntry.code, '登録した後に、登録したエントリーの編集画面が表示されていません。');

        test.visit('/admin/entry_form');
    },
    // フィルター対象外の属性に公開するエントリーを登録
    function() {
        registerEntry(generalEntry, generalAttribute.name);
    },
    // 登録できたことを確認してログアウト
    function() {
        test.assertText('div.alert-success', 'エントリーを登録しました。', 'エントリーを登録できていません。');
        test.assertValue('form.register input[name="code"]', generalEntry.code, '登録した後に、登録したエントリーの編集画面が表示されていません。');

        test.visit('/auth/logout');
    },
    // 会員でログイン
    function() {
        test.assertExists('input[name="username"]', 'ログアウトできていません。');
        login(scenarioMember.username, scenarioMember.password);
    },
    // 会員ページに「フィルター」のメニューがあることを確認して、フィルター対象のエントリーを開く
    function() {
        test.assertText('main', 'ようこそ、' + scenarioMember.name + 'さん', '会員でログインできていません。');
        test.assertExists('main a:contains("フィルター")', '会員ページに「フィルター」のメニューがありません。');

        test.visit('/entry/detail/' + filterEntry.code);
    },
    // 初期状態では表示されないことを確認して、フィルター対象外のエントリーを開く
    function() {
        test.assertText('div.alert-danger', 'エントリーが見つかりません。', 'フィルターで表示を選んでいないのに、フィルター対象のエントリーが表示されています。');
        test.visit('/entry/detail/' + generalEntry.code);
    },
    // フィルター対象外の属性は今までどおり表示されることを確認して、フィルターのページに移動
    function() {
        test.assertText('#entry h2', generalEntry.title, 'フィルター対象外の属性に公開したエントリーが表示されていません。');
        test.visit('/auth/filter');
    },
    // 初期状態では選ばれていないことを確認して、表示を選んで登録
    function() {
        test.assert(filterCheckbox(filterAttribute.name).prop('checked') === false, 'フィルターの初期状態で、属性が選ばれています。');

        submitFilter(true);
    },
    // 登録できたことを確認して、フィルター対象のエントリーを開く
    function() {
        test.assertText('div.alert-success', 'フィルターを登録しました。', 'フィルターを登録できていません。');
        test.assert(filterCheckbox(filterAttribute.name).prop('checked') === true, '登録したフィルターの選択が復元されていません。');

        test.visit('/entry/detail/' + filterEntry.code);
    },
    // 表示を選ぶと表示されることを確認して、フィルターのページに移動
    function() {
        test.assertText('#entry h2', filterEntry.title, 'フィルターで表示を選んだのに、エントリーが表示されていません。');
        test.visit('/auth/filter');
    },
    // 表示の選択を外して登録
    function() {
        submitFilter(false);
    },
    // 登録できたことを確認して、フィルター対象のエントリーを開く
    function() {
        test.assertText('div.alert-success', 'フィルターを登録しました。', 'フィルターを登録できていません。');
        test.assert(filterCheckbox(filterAttribute.name).prop('checked') === false, '外したフィルターの選択が残っています。');

        test.visit('/entry/detail/' + filterEntry.code);
    },
    // 選択を外すと表示されなくなることを確認してログアウト
    function() {
        test.assertText('div.alert-danger', 'エントリーが見つかりません。', 'フィルターの選択を外したのに、エントリーが表示されています。');
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
    // フィルター対象のエントリーの編集ページに移動
    function() {
        test.click(codeRow(filterEntry.code).find('a:contains("編集")'), '一覧の ' + filterEntry.code + ' の編集リンクが見つかりません。');
    },
    // 削除
    function() {
        deleteRecord('form.register input[name="code"]', filterEntry.code, filterEntry.code);
    },
    // 削除できたことを確認して、フィルター対象外のエントリーの編集ページに移動
    function() {
        test.assertText('div.alert-success', 'エントリーを削除しました。', 'エントリーを削除できていません。');
        test.assert(codeRow(filterEntry.code).length === 0, '削除したエントリーが一覧に残っています。');

        test.click(codeRow(generalEntry.code).find('a:contains("編集")'), '一覧の ' + generalEntry.code + ' の編集リンクが見つかりません。');
    },
    // 削除
    function() {
        deleteRecord('form.register input[name="code"]', generalEntry.code, generalEntry.code);
    },
    // 削除できたことを確認してユーザー管理ページに移動
    function() {
        test.assertText('div.alert-success', 'エントリーを削除しました。', 'エントリーを削除できていません。');
        test.assert(codeRow(generalEntry.code).length === 0, '削除したエントリーが一覧に残っています。');

        test.click('a:contains("ユーザー管理")');
    },
    // 会員の編集ページに移動
    function() {
        test.click(userRow(scenarioMember.username).find('a:contains("編集")'), '一覧の ' + scenarioMember.username + ' の編集リンクが見つかりません。');
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
    // フィルター対象の属性の編集ページに移動
    function() {
        test.click(attributeRow(filterAttribute.name).find('a:contains("編集")'), '一覧の ' + filterAttribute.name + ' の編集リンクが見つかりません。');
    },
    // 属性を削除
    function() {
        test.assertValue('form.register select[name="filterable"]', '1', '属性の「フィルター対象」が編集画面に復元されていません。');

        deleteRecord('form.register input[name="name"]', filterAttribute.name, filterAttribute.name);
    },
    // 削除できたことを確認して、フィルター対象外の属性の編集ページに移動
    function() {
        test.assertText('div.alert-success', '属性を削除しました。', '属性を削除できていません。');
        test.assert(attributeRow(filterAttribute.name).length === 0, '削除した属性が一覧に残っています。');

        test.click(attributeRow(generalAttribute.name).find('a:contains("編集")'), '一覧の ' + generalAttribute.name + ' の編集リンクが見つかりません。');
    },
    // 属性を削除
    function() {
        deleteRecord('form.register input[name="name"]', generalAttribute.name, generalAttribute.name);
    },
    // 削除できたことを確認してホームに移動
    function() {
        test.assertText('div.alert-success', '属性を削除しました。', '属性を削除できていません。');
        test.assert(attributeRow(generalAttribute.name).length === 0, '削除した属性が一覧に残っています。');

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
