/* テスト用フィールド（test オブジェクトはページごとに作り直されるため、値は定数で持つ） */
var scenarioTextField = {
    code:       'scenario-text',
    name:       'シナリオ項目',
    type:       'エントリー',
    kind:       'text',
    label:      '一行入力',
    validation: 'required',
    value:      'シナリオの値'
};

/* 選択肢を持つフィールド（「種類」に応じて選択肢の欄が出し分けられることも確認する） */
var scenarioSelectField = {
    code:    'scenario-select',
    name:    'シナリオ選択',
    type:    'エントリー',
    kind:    'select',
    label:   'セレクトボックス',
    choices: ['選択肢A', '選択肢B'],
    value:   '選択肢B'
};

/* フィールドの確認に使うエントリー */
var scenarioEntry = {
    code:  'scenario-field-entry',
    title: 'シナリオのフィールド確認'
};

/* 一覧から、コードで対象の行を取得する（フィールド・エントリーで同じ作り） */
var codeRow = function(code) {
    return $('table tbody tr').filter(function() {
        return $(this).find('td code').text().trim() === code;
    });
};

/* 対象（型）の選択肢から、名前で対象を取得する */
var typeOption = function(form, name) {
    return form.find('select[name="type_id"] option').filter(function() {
        return $(this).text() === name;
    });
};

/* エントリー編集画面から、フィールド名で入力欄のまとまりを取得する */
var fieldGroup = function(name) {
    return $('form.register div.form-group').filter(function() {
        return $(this).children('label').text().trim().indexOf(name) === 0;
    });
};

/* フィールドを登録する（「種類」は change を起こして、選択肢の欄の出し分けも確認する） */
var registerField = function(field) {
    var form = $('form.register');
    var type = typeOption(form, field.type);

    test.assert(form.find('input[name="code"]').length === 1, 'フィールド登録フォームが表示されていません。');
    test.assert(type.length === 1, '対象に指定する型「' + field.type + '」がありません。');

    form.find('input[name="code"]').val(field.code);
    form.find('input[name="name"]').val(field.name);
    form.find('select[name="type_id"]').val(type.val());
    form.find('select[name="kind"]').val(field.kind).trigger('change');

    if (field.choices) {
        test.assert($('.for-kind').is(':visible'), '種類で「' + field.label + '」を選んでも選択肢の欄が表示されません。');

        form.find('textarea[name="choices"]').val(field.choices.join('\n'));
    } else {
        test.assert(!$('.for-kind').is(':visible'), '種類で「' + field.label + '」を選ぶと選択肢の欄が表示されます。');
    }

    form.find('select[name="validation"]').val(field.validation ? field.validation : 'none');
    test.click('form.register button[type="submit"]');
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
    // ログインできたことを確認してフィールド管理ページに移動
    function() {
        test.assertText('body', '管理者さん', 'ログインできていません。');
        test.click('a:contains("フィールド管理")');
    },
    // 前回のテストデータが残っていないことを確認してフィールド登録ページに移動
    function() {
        test.assert(codeRow(scenarioTextField.code).length === 0, 'フィールド ' + scenarioTextField.code + ' が残っています。前回のテストが中断した可能性があります。');
        test.assert(codeRow(scenarioSelectField.code).length === 0, 'フィールド ' + scenarioSelectField.code + ' が残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("フィールド登録")');
    },
    // 必須の「一行入力」のフィールドを登録
    function() {
        registerField(scenarioTextField);
    },
    // 登録できたことを確認して、2つ目のフィールドの登録ページに移動
    function() {
        test.assertText('div.alert-success', 'フィールドを登録しました。', 'フィールドを登録できていません。');
        test.assert(codeRow(scenarioTextField.code).length === 1, '登録したフィールドが一覧にありません。');
        test.assertText(codeRow(scenarioTextField.code), scenarioTextField.name, '登録したフィールドの名前が「' + scenarioTextField.name + '」になっていません。');
        test.assertText(codeRow(scenarioTextField.code), scenarioTextField.label, '登録したフィールドの種類が「' + scenarioTextField.label + '」になっていません。');

        test.click('a:contains("フィールド登録")');
    },
    // 「セレクトボックス」のフィールドを登録
    function() {
        registerField(scenarioSelectField);
    },
    // 登録できたことを確認してエントリー管理ページに移動
    function() {
        test.assertText('div.alert-success', 'フィールドを登録しました。', 'フィールドを登録できていません。');
        test.assert(codeRow(scenarioSelectField.code).length === 1, '登録したフィールドが一覧にありません。');
        test.assertText(codeRow(scenarioSelectField.code), scenarioSelectField.label, '登録したフィールドの種類が「' + scenarioSelectField.label + '」になっていません。');

        test.click('a:contains("エントリー管理")');
    },
    // 前回のテストデータが残っていないことを確認してエントリー登録ページに移動
    function() {
        test.assert(codeRow(scenarioEntry.code).length === 0, 'エントリー ' + scenarioEntry.code + ' が残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("エントリー登録")');
    },
    // 入力欄が追加されていることを確認し、必須のフィールドを空のまま送信して入力エラーを確認
    function() {
        var textGroup   = fieldGroup(scenarioTextField.name);
        var selectGroup = fieldGroup(scenarioSelectField.name);
        var form        = $('form.register');

        test.assert(textGroup.length === 1, 'エントリー登録画面にフィールド「' + scenarioTextField.name + '」の入力欄がありません。');
        test.assert(selectGroup.length === 1, 'エントリー登録画面にフィールド「' + scenarioSelectField.name + '」の入力欄がありません。');
        test.assert(textGroup.find('input[type="text"]').length === 1, 'フィールド「' + scenarioTextField.name + '」が一行入力になっていません。');
        test.assert(selectGroup.find('select').length === 1, 'フィールド「' + scenarioSelectField.name + '」がセレクトボックスになっていません。');
        test.assertText(textGroup.children('label'), '必須', 'バリデーションが「必須」のフィールドに必須の表示がありません。');
        test.assertNoText(selectGroup.children('label'), '必須', 'バリデーションが「なし」のフィールドに必須の表示があります。');

        // 選択肢がそのまま並んでいること
        for (var i = 0; i < scenarioSelectField.choices.length; i++) {
            test.assert(selectGroup.find('option').filter(function() {
                return $(this).val() === scenarioSelectField.choices[i];
            }).length === 1, '選択肢「' + scenarioSelectField.choices[i] + '」がセレクトボックスにありません。');
        }

        // 必須のフィールドだけを空にして送信する
        form.find('input[name="code"]').val(scenarioEntry.code);
        form.find('input[name="title"]').val(scenarioEntry.title);
        textGroup.find('input[type="text"]').val('');

        $('div.warning').remove();
        test.click('form.register button[type="submit"]');
        test.wait(function() {
            return $('form.register div.warning').length > 0;
        }, function() {
            test.assertText(fieldGroup(scenarioTextField.name).find('div.warning'), scenarioTextField.name + 'が入力されていません。', '必須のフィールドの入力エラーが表示されていません。');
            test.assert($('form.register div.warning').length === 1, '必須のフィールド以外にも入力エラーが表示されています。（' + $('form.register div.warning').text() + '）');

            test.reload();
        }, '必須のフィールドを空にしても入力エラーが表示されませんでした。');
    },
    // フィールドに値を入れてエントリーを登録
    function() {
        var form = $('form.register');

        form.find('input[name="code"]').val(scenarioEntry.code);
        form.find('input[name="title"]').val(scenarioEntry.title);
        fieldGroup(scenarioTextField.name).find('input[type="text"]').val(scenarioTextField.value);
        fieldGroup(scenarioSelectField.name).find('select').val(scenarioSelectField.value);

        test.click('form.register button[type="submit"]');
    },
    // 登録できたことを確認して、公開側の詳細を開く
    function() {
        test.assertText('div.alert-success', 'エントリーを登録しました。', 'エントリーを登録できていません。');
        test.assertValue('form.register input[name="code"]', scenarioEntry.code, '登録した後に、登録したエントリーの編集画面が表示されていません。');

        test.visit('/entry/detail/' + scenarioEntry.code);
    },
    // 公開側にフィールドの名前と値が表示されることを確認して、管理画面のエントリー管理ページに戻る
    function() {
        test.assertExists('#entry table', '公開側の詳細にフィールドの表が表示されていません。');
        test.assertText('#entry table', scenarioTextField.name, '公開側の詳細にフィールド「' + scenarioTextField.name + '」の名前が表示されていません。');
        test.assertText('#entry table', scenarioTextField.value, '公開側の詳細にフィールド「' + scenarioTextField.name + '」の値が表示されていません。');
        test.assertText('#entry table', scenarioSelectField.name, '公開側の詳細にフィールド「' + scenarioSelectField.name + '」の名前が表示されていません。');
        test.assertText('#entry table', scenarioSelectField.value, '公開側の詳細にフィールド「' + scenarioSelectField.name + '」の値が表示されていません。');

        test.visit('/admin/entry');
    },
    // エントリー編集ページに移動
    function() {
        test.click(codeRow(scenarioEntry.code).find('a:contains("編集")'), '一覧の ' + scenarioEntry.code + ' の編集リンクが見つかりません。');
    },
    // 編集画面にフィールドの値が復元されていることを確認して、エントリーを削除
    function() {
        var form = $('form.delete');

        test.assertValue('form.register input[name="code"]', scenarioEntry.code, '削除対象が ' + scenarioEntry.code + ' ではありません。');
        test.assertValue(fieldGroup(scenarioTextField.name).find('input[type="text"]'), scenarioTextField.value, 'フィールド「' + scenarioTextField.name + '」の値が編集画面に復元されていません。');
        test.assertValue(fieldGroup(scenarioSelectField.name).find('select'), scenarioSelectField.value, 'フィールド「' + scenarioSelectField.name + '」の値が編集画面に復元されていません。');
        test.assert(form.length === 1, '削除フォームが表示されていません。');

        form.off('submit');
        test.click('form.delete button[type="submit"]');
    },
    // エントリーを削除できたことを確認してフィールド管理ページに移動
    function() {
        test.assertText('div.alert-success', 'エントリーを削除しました。', 'エントリーを削除できていません。');
        test.assert(codeRow(scenarioEntry.code).length === 0, '削除したエントリーが一覧に残っています。');

        test.click('a:contains("フィールド管理")');
    },
    // 「一行入力」のフィールドの編集ページに移動
    function() {
        test.click(codeRow(scenarioTextField.code).find('a'), '一覧の ' + scenarioTextField.code + ' の編集リンクが見つかりません。');
    },
    // 「一行入力」のフィールドを削除
    function() {
        var form = $('form.delete');

        test.assertValue('form.register input[name="code"]', scenarioTextField.code, '削除対象が ' + scenarioTextField.code + ' ではありません。');
        test.assert(form.length === 1, '削除フォームが表示されていません。');

        form.off('submit');
        test.click('form.delete button[type="submit"]');
    },
    // 削除できたことを確認して、「セレクトボックス」のフィールドの編集ページに移動
    function() {
        test.assertText('div.alert-success', 'フィールドを削除しました。', 'フィールドを削除できていません。');
        test.assert(codeRow(scenarioTextField.code).length === 0, '削除したフィールドが一覧に残っています。');

        test.click(codeRow(scenarioSelectField.code).find('a'), '一覧の ' + scenarioSelectField.code + ' の編集リンクが見つかりません。');
    },
    // 「セレクトボックス」のフィールドを削除
    function() {
        var form = $('form.delete');

        test.assertValue('form.register input[name="code"]', scenarioSelectField.code, '削除対象が ' + scenarioSelectField.code + ' ではありません。');
        test.assert(form.length === 1, '削除フォームが表示されていません。');

        form.off('submit');
        test.click('form.delete button[type="submit"]');
    },
    // 削除できたことを確認してホームに移動
    function() {
        test.assertText('div.alert-success', 'フィールドを削除しました。', 'フィールドを削除できていません。');
        test.assert(codeRow(scenarioSelectField.code).length === 0, '削除したフィールドが一覧に残っています。');

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
