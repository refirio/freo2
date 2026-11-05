/* テスト用エントリー（test オブジェクトはページごとに作り直されるため、値は定数で持つ） */
var scenarioBulkEntries = [
    { code: 'scenario-bulk-1', title: 'シナリオの一括削除1' },
    { code: 'scenario-bulk-2', title: 'シナリオの一括削除2' },
    { code: 'scenario-bulk-3', title: 'シナリオの一括削除3' }
];

/* 一括削除するエントリー（3件目は選択を外して残す） */
var scenarioBulkDeleted = ['scenario-bulk-1', 'scenario-bulk-2'];
var scenarioBulkKept    = 'scenario-bulk-3';

/* エントリー一覧から、コードで対象の行を取得する */
var entryRow = function(code) {
    return $('table tbody tr').filter(function() {
        return $(this).find('td code').text().trim() === code;
    });
};

/* 一覧の行から、一括削除のチェックボックスを取得する */
var bulkCheckbox = function(code) {
    return entryRow(code).find('input.bulk');
};

/* チェックボックスを切り替えて、選択を控える Ajax が終わってからコールバックを実行する */
var bulkCheck = function(codes, checked, callback) {
    for (var i = 0; i < codes.length; i++) {
        if (bulkCheckbox(codes[i]).length !== 1) {
            return test.abort('一覧の ' + codes[i] + ' に一括削除のチェックボックスがありません。');
        }
        bulkCheckbox(codes[i]).prop('checked', checked).trigger('change');
    }

    // admin.js が change ごとに /admin/entry_bulk へ送るので、すべて終わるまで待つ
    return test.wait(function() {
        return $.active === 0;
    }, callback, '一括削除の選択を保存する通信が終わりませんでした。');
};

/* WYSIWYGエディタの準備を待ってからコールバックを実行する（本文は空のまま登録する） */
var waitEditor = function(callback) {
    if (!$('form.register textarea[name="text"]').hasClass('editor')) {
        return callback();
    }

    return test.wait(function() {
        return window.text != null && typeof window.text.setData === 'function';
    }, callback, 'WYSIWYGエディタ（CKEditor）が初期化されませんでした。CDNから読み込めているか確認してください。');
};

/* エントリーを1件登録し、登録できたことを一覧で確認するステップを作る */
var registerSteps = function(entry, index) {
    return [
        // エントリーを登録
        function() {
            var form = $('form.register');

            test.assert(form.find('input[name="code"]').length === 1, 'エントリー登録フォームが表示されていません。');

            form.find('input[name="code"]').val(entry.code);
            form.find('input[name="title"]').val(entry.title);

            waitEditor(function() {
                test.click('form.register button[type="submit"]');
            });
        },
        // 登録できたことを確認して、次のエントリーの登録ページに移動（最後のときは、そのまま一覧で選択を始める）
        function() {
            test.assertText('div.alert-success', 'エントリーを登録しました。', 'エントリー ' + entry.code + ' を登録できていません。');
            test.assert(entryRow(entry.code).length === 1, '登録したエントリー ' + entry.code + ' が一覧にありません。');

            if (index < scenarioBulkEntries.length - 1) {
                test.click('a:contains("エントリー登録")');
            } else {
                // 何も選んでいないまま一括削除を送ると、対象が無い旨が出ることを確認する
                test.assert($('form.bulk input.bulk:checked').length === 0, '一括削除の対象が、選んでいないのに選択されています。前回のテストが中断した可能性があります。');

                test.click('form.bulk input[type="submit"]');
            }
        }
    ];
};

var registerScenario = [];
scenarioBulkEntries.forEach(function(entry, index) {
    registerScenario = registerScenario.concat(registerSteps(entry, index));
});

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
        scenarioBulkEntries.forEach(function(entry) {
            test.assert(entryRow(entry.code).length === 0, 'エントリー ' + entry.code + ' が残っています。前回のテストが中断した可能性があります。');
        });
        test.click('a:contains("エントリー登録")');
    }
].concat(registerScenario).concat([
    // 対象を選ばずに送ると、対象が無い旨が出ることを確認して、一覧に戻る
    function() {
        test.assertText('div.alert-danger', '一括削除対象が選択されていません。', '対象を選ばずに一括削除を送ったときの案内が表示されていません。');
        test.assertNotExists('form.delete', '対象を選んでいないのに、削除のフォームが表示されています。');

        test.click('a:contains("戻る")');
    },
    // 3件を選んで、再読み込みする
    function() {
        test.assert(entryRow(scenarioBulkKept).length === 1, '一覧に戻れていません。');

        bulkCheck(scenarioBulkEntries.map(function(entry) {
            return entry.code;
        }), true, function() {
            test.reload();
        });
    },
    // 再読み込みしても選択が残っていることを確認して、3件目の選択を外して再読み込みする
    function() {
        scenarioBulkEntries.forEach(function(entry) {
            test.assert(bulkCheckbox(entry.code).prop('checked') === true, '再読み込みすると、' + entry.code + ' の選択が外れています。選択がセッションに保存されていません。');
        });

        bulkCheck([scenarioBulkKept], false, function() {
            test.reload();
        });
    },
    // 外した選択が残っていないことを確認して、一括削除を送る
    function() {
        scenarioBulkDeleted.forEach(function(code) {
            test.assert(bulkCheckbox(code).prop('checked') === true, '再読み込みすると、' + code + ' の選択が外れています。');
        });
        test.assert(bulkCheckbox(scenarioBulkKept).prop('checked') === false, '選択を外した ' + scenarioBulkKept + ' が、再読み込みすると選択されています。');

        test.click('form.bulk input[type="submit"]');
    },
    // 確認画面に、選んだ2件だけが並ぶことを確認して削除する
    function() {
        var codes = $('table tbody tr').map(function() {
            return $(this).find('td:first').text().trim();
        }).get();
        var form  = $('form.delete');

        // ほかのシナリオの選択がセッションに残っていると、関係の無いエントリーまで消してしまうので、件数も確かめる
        test.assert(codes.length === scenarioBulkDeleted.length, '一括削除の確認画面に、選んでいないエントリーが含まれています（' + codes.join('、') + '）。');
        scenarioBulkDeleted.forEach(function(code) {
            test.assert(codes.indexOf(code) !== -1, '一括削除の確認画面に ' + code + ' がありません。');
        });
        test.assert(codes.indexOf(scenarioBulkKept) === -1, '一括削除の確認画面に、選択を外した ' + scenarioBulkKept + ' があります。');
        test.assert(form.find('input[name="list[]"]').length === scenarioBulkDeleted.length, '削除のフォームで送る対象の数が、選んだ数と違います。');

        form.off('submit');
        test.click('form.delete button[type="submit"]');
    },
    // 2件だけ削除できたことと、選択が消えたことを確認して、残した1件の編集ページに移動
    function() {
        test.assertText('div.alert-success', 'エントリーを削除しました。', 'エントリーを一括削除できていません。');
        scenarioBulkDeleted.forEach(function(code) {
            test.assert(entryRow(code).length === 0, '一括削除したエントリー ' + code + ' が一覧に残っています。');
        });
        test.assert(entryRow(scenarioBulkKept).length === 1, '選択を外したエントリー ' + scenarioBulkKept + ' まで削除されています。');
        test.assert($('form.bulk input.bulk:checked').length === 0, '一括削除の後も、選択が残っています。');

        test.click(entryRow(scenarioBulkKept).find('a:contains("編集")'), '一覧の ' + scenarioBulkKept + ' の編集リンクが見つかりません。');
    },
    // 残したエントリーを削除
    function() {
        var form = $('form.delete');

        test.assertValue('form.register input[name="code"]', scenarioBulkKept, '削除対象が ' + scenarioBulkKept + ' ではありません。');
        test.assert(form.length === 1, '削除フォームが表示されていません。');

        form.off('submit');
        test.click('form.delete button[type="submit"]');
    },
    // 削除できたことを確認してホームに移動
    function() {
        test.assertText('div.alert-success', 'エントリーを削除しました。', 'エントリーを削除できていません。');
        test.assert(entryRow(scenarioBulkKept).length === 0, '削除したエントリーが一覧に残っています。');

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
]);
