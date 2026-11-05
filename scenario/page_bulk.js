/* テスト用ページ（test オブジェクトはページごとに作り直されるため、値は定数で持つ） */
var scenarioBulkParent = { code: 'scenariobulk', title: 'シナリオの一括削除の親' };
var scenarioBulkPages  = [
    { code: 'scenariobulk/1', title: 'シナリオの一括削除1' },
    { code: 'scenariobulk/2', title: 'シナリオの一括削除2' },
    { code: 'scenariobulk/3', title: 'シナリオの一括削除3' }
];

/* 一括削除するページ（3件目は選択を外して残す） */
var scenarioBulkDeleted = ['scenariobulk/1', 'scenariobulk/2'];
var scenarioBulkKept    = 'scenariobulk/3';

/* ページ一覧から、コードで対象の行を取得する */
var pageRow = function(code) {
    return $('table tbody tr').filter(function() {
        return $(this).find('td code').text().trim() === code;
    });
};

/* 一覧の行から、一括削除のチェックボックスを取得する */
var bulkCheckbox = function(code) {
    return pageRow(code).find('input.bulk');
};

/* チェックボックスを切り替えて、選択を控える Ajax が終わってからコールバックを実行する */
var bulkCheck = function(codes, checked, callback) {
    for (var i = 0; i < codes.length; i++) {
        if (bulkCheckbox(codes[i]).length !== 1) {
            return test.abort('一覧の ' + codes[i] + ' に一括削除のチェックボックスがありません。');
        }
        bulkCheckbox(codes[i]).prop('checked', checked).trigger('change');
    }

    // admin.js が change ごとに /admin/page_bulk へ送るので、すべて終わるまで待つ
    return test.wait(function() {
        return $.active === 0;
    }, callback, '一括削除の選択を保存する通信が終わりませんでした。');
};

/* 親ページの一覧を表示していることを確認する */
var assertChildList = function(message) {
    test.assertText('ol.breadcrumb li.active', scenarioBulkParent.title, message);
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

/* ページを登録する */
var registerPage = function(page) {
    var form = $('form.register');

    test.assert(form.find('input[name="code"]').length === 1, 'ページ登録フォームが表示されていません。');

    form.find('input[name="code"]').val(page.code);
    form.find('input[name="title"]').val(page.title);

    waitEditor(function() {
        test.click('form.register button[type="submit"]');
    });
};

/* 編集画面で対象を確認して、ページを削除する */
var deletePage = function(code) {
    var form = $('form.delete');

    test.assertValue('form.register input[name="code"]', code, '削除対象が ' + code + ' ではありません。');
    test.assert(form.length === 1, '削除フォームが表示されていません。');

    form.off('submit');
    test.click('form.delete button[type="submit"]');
};

/* 子ページを1件登録し、親ページの一覧に戻ったことを確認するステップを作る */
var registerSteps = function(page, index) {
    return [
        // 子ページを登録
        function() {
            registerPage(page);
        },
        // 親ページの一覧に戻ったことを確認して、次の子ページの登録ページに移動（最後のときは、そのまま一覧で選択を始める）
        function() {
            test.assertText('div.alert-success', 'ページを登録しました。', 'ページ ' + page.code + ' を登録できていません。');
            assertChildList('登録した後に、親ページの一覧に戻っていません。');
            test.assert(pageRow(page.code).length === 1, '登録したページ ' + page.code + ' が一覧にありません。');

            if (index < scenarioBulkPages.length - 1) {
                test.click('a:contains("ページ登録")');
            } else {
                // 何も選んでいないまま一括削除を送ると、対象が無い旨が出ることを確認する
                test.assert($('form.bulk input.bulk:checked').length === 0, '一括削除の対象が、選んでいないのに選択されています。前回のテストが中断した可能性があります。');

                test.click('form.bulk input[type="submit"]');
            }
        }
    ];
};

var registerScenario = [];
scenarioBulkPages.forEach(function(page, index) {
    registerScenario = registerScenario.concat(registerSteps(page, index));
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
    // ログインできたことを確認してページ管理ページに移動
    function() {
        test.assertText('body', '管理者さん', 'ログインできていません。');
        test.click('a:contains("ページ管理")');
    },
    // 前回のテストデータが残っていないことを確認してページ登録ページに移動
    function() {
        test.assert(pageRow(scenarioBulkParent.code).length === 0, 'ページ ' + scenarioBulkParent.code + ' が残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("ページ登録")');
    },
    // 親ページを登録
    function() {
        registerPage(scenarioBulkParent);
    },
    // 登録できたことを確認して、親ページの下に子ページを登録する画面を開く
    function() {
        test.assertText('div.alert-success', 'ページを登録しました。', '親ページを登録できていません。');
        test.assert(pageRow(scenarioBulkParent.code).length === 1, '親ページが一覧にありません。');

        test.visit('/admin/page_form?parent=' + encodeURIComponent(scenarioBulkParent.code));
    }
].concat(registerScenario).concat([
    // 対象を選ばずに送ると、対象が無い旨が出ることを確認して、親ページの一覧に戻る
    function() {
        test.assertText('div.alert-danger', '一括削除対象が選択されていません。', '対象を選ばずに一括削除を送ったときの案内が表示されていません。');
        test.assertNotExists('form.delete', '対象を選んでいないのに、削除のフォームが表示されています。');

        test.click('a:contains("戻る")');
    },
    // 3件を選んで、再読み込みする
    function() {
        assertChildList('一括削除の画面の「戻る」で、親ページの一覧に戻っていません。');

        bulkCheck(scenarioBulkPages.map(function(page) {
            return page.code;
        }), true, function() {
            test.reload();
        });
    },
    // 再読み込みしても選択が残っていることを確認して、3件目の選択を外して再読み込みする
    function() {
        scenarioBulkPages.forEach(function(page) {
            test.assert(bulkCheckbox(page.code).prop('checked') === true, '再読み込みすると、' + page.code + ' の選択が外れています。選択がセッションに保存されていません。');
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

        // ほかのシナリオの選択がセッションに残っていると、関係の無いページまで消してしまうので、件数も確かめる
        test.assert(codes.length === scenarioBulkDeleted.length, '一括削除の確認画面に、選んでいないページが含まれています（' + codes.join('、') + '）。');
        scenarioBulkDeleted.forEach(function(code) {
            test.assert(codes.indexOf(code) !== -1, '一括削除の確認画面に ' + code + ' がありません。');
        });
        test.assert(codes.indexOf(scenarioBulkKept) === -1, '一括削除の確認画面に、選択を外した ' + scenarioBulkKept + ' があります。');
        test.assert(form.find('input[name="list[]"]').length === scenarioBulkDeleted.length, '削除のフォームで送る対象の数が、選んだ数と違います。');
        test.assertValue('form.delete input[name="parent"]', scenarioBulkParent.code, '削除のフォームで、操作した一覧の親ページを送っていません。');

        form.off('submit');
        test.click('form.delete button[type="submit"]');
    },
    // 2件だけ削除され、親ページの一覧に戻り、選択も消えたことを確認して、残した1件の編集ページに移動
    function() {
        test.assertText('div.alert-success', 'ページを削除しました。', 'ページを一括削除できていません。');
        assertChildList('一括削除した後に、親ページの一覧に戻っていません。');
        scenarioBulkDeleted.forEach(function(code) {
            test.assert(pageRow(code).length === 0, '一括削除したページ ' + code + ' が一覧に残っています。');
        });
        test.assert(pageRow(scenarioBulkKept).length === 1, '選択を外したページ ' + scenarioBulkKept + ' まで削除されています。');
        test.assert($('form.bulk input.bulk:checked').length === 0, '一括削除の後も、選択が残っています。');

        test.click(pageRow(scenarioBulkKept).find('a:contains("編集")'), '一覧の ' + scenarioBulkKept + ' の編集リンクが見つかりません。');
    },
    // 残した子ページを削除
    function() {
        deletePage(scenarioBulkKept);
    },
    // 削除できたことを確認して、一覧のトップに移動
    function() {
        test.assertText('div.alert-success', 'ページを削除しました。', 'ページを削除できていません。');
        test.assert(pageRow(scenarioBulkKept).length === 0, '削除したページが一覧に残っています。');

        test.visit('/admin/page');
    },
    // 親ページの編集ページに移動
    function() {
        test.click(pageRow(scenarioBulkParent.code).find('a:contains("編集")'), '一覧の ' + scenarioBulkParent.code + ' の編集リンクが見つかりません。');
    },
    // 親ページを削除
    function() {
        deletePage(scenarioBulkParent.code);
    },
    // 削除できたことを確認してホームに移動
    function() {
        test.assertText('div.alert-success', 'ページを削除しました。', '親ページを削除できていません。');
        test.assert(pageRow(scenarioBulkParent.code).length === 0, '削除した親ページが一覧に残っています。');

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
