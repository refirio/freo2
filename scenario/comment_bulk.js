/* コメントの投稿先になるエントリー（test オブジェクトはページごとに作り直されるため、値は定数で持つ） */
var scenarioEntry = {
    code:  'scenario-comment-bulk',
    title: 'シナリオのコメント一括削除'
};

/* テスト用のコメント（一覧ではお名前で特定する） */
var scenarioBulkComments = [
    { name: 'シナリオ一括コメント1', message: 'シナリオの一括削除のコメント1です。' },
    { name: 'シナリオ一括コメント2', message: 'シナリオの一括削除のコメント2です。' },
    { name: 'シナリオ一括コメント3', message: 'シナリオの一括削除のコメント3です。' }
];

/* 一括削除するコメント（3件目は選択を外して残す） */
var scenarioBulkDeleted = ['シナリオ一括コメント1', 'シナリオ一括コメント2'];
var scenarioBulkKept    = 'シナリオ一括コメント3';

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

/* 一覧の行から、一括削除のチェックボックスを取得する */
var bulkCheckbox = function(name) {
    return commentRow(name).find('input.bulk');
};

/* チェックボックスを切り替えて、選択を控える Ajax が終わってからコールバックを実行する */
var bulkCheck = function(names, checked, callback) {
    for (var i = 0; i < names.length; i++) {
        if (bulkCheckbox(names[i]).length !== 1) {
            return test.abort('一覧の ' + names[i] + ' に一括削除のチェックボックスがありません。');
        }
        bulkCheckbox(names[i]).prop('checked', checked).trigger('change');
    }

    // admin.js が change ごとに /admin/comment_bulk へ送るので、すべて終わるまで待つ
    return test.wait(function() {
        return $.active === 0;
    }, callback, '一括削除の選択を保存する通信が終わりませんでした。');
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

/* WYSIWYGエディタの準備を待ってからコールバックを実行する（本文は空のまま登録する） */
var waitEditor = function(callback) {
    if (!$('form.register textarea[name="text"]').hasClass('editor')) {
        return callback();
    }

    return test.wait(function() {
        return window.text != null && typeof window.text.setData === 'function';
    }, callback, 'WYSIWYGエディタ（CKEditor）が初期化されませんでした。CDNから読み込めているか確認してください。');
};

/* コメントを1件投稿するステップを作る（エントリー詳細 → 確認 → 完了） */
var postSteps = function(comment, index) {
    return [
        // ゲストとしてコメントを入力して確認画面に進む
        function() {
            var form = $('form.register');

            test.assertExists('#comment_form', 'エントリー詳細にコメントフォームが表示されていません。');
            test.assertNotExists('form.register input[name="name"][readonly]', 'ログインしたまま投稿しようとしています。');

            form.find('input[name="name"]').val(comment.name);
            form.find('input[name="url"]').val('');
            form.find('textarea[name="message"]').val(comment.message);
            test.click('form.register button[type="submit"]');
        },
        // 確認画面から投稿する
        function() {
            test.assertText('#comment dl', comment.name, '確認画面にお名前が表示されていません。');

            test.click('#comment form button[type="submit"]', '確認画面に投稿ボタンがありません。');
        },
        // 投稿できたことを確認して、次のコメントを投稿する（最後のときは、ログインページに移動）
        function() {
            test.assertText('#comment', 'コメントを投稿しました。', 'コメント ' + comment.name + ' を投稿できていません。');

            if (index < scenarioBulkComments.length - 1) {
                test.visit('/entry/detail/' + scenarioEntry.code);
            } else {
                test.visit('/auth/');
            }
        }
    ];
};

var postScenario = [];
scenarioBulkComments.forEach(function(comment, index) {
    postScenario = postScenario.concat(postSteps(comment, index));
});

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
    // ログインできたことを確認してコメント管理ページに移動
    function() {
        test.assertText('body', '管理者さん', 'ログインできていません。');
        test.click('a:contains("コメント管理")');
    },
    // 前回のコメントが残っていないことを確認してエントリー管理ページに移動
    function() {
        scenarioBulkComments.forEach(function(comment) {
            test.assert(commentRow(comment.name).length === 0, 'コメント ' + comment.name + ' が残っています。前回のテストが中断した可能性があります。');
        });
        test.click('a:contains("エントリー管理")');
    },
    // 前回のエントリーが残っていないことを確認してエントリー登録ページに移動
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

        waitEditor(function() {
            test.click('form.register button[type="submit"]');
        });
    },
    // 登録できたことを確認してログアウト（コメントはゲストとして投稿する）
    function() {
        test.assertText('div.alert-success', 'エントリーを登録しました。', 'エントリーを登録できていません。');
        test.assert(codeRow(scenarioEntry.code).length === 1, '登録したエントリーが一覧にありません。');

        logout();
    },
    // ログアウトできたことを確認して、公開側のエントリー詳細を開く
    function() {
        test.assertExists('input[name="username"]', 'ログアウトできていません。');
        test.visit('/entry/detail/' + scenarioEntry.code);
    }
].concat(postScenario).concat([
    // 管理者用ページにログイン
    function() {
        login('admin', 'abcd1234');
    },
    // ログインできたことを確認してコメント管理ページに移動
    function() {
        test.assertText('body', '管理者さん', 'ログインできていません。');
        test.click('a:contains("コメント管理")');
    },
    // 投稿した3件が並ぶことを確認して、何も選ばずに一括削除を送る
    function() {
        scenarioBulkComments.forEach(function(comment) {
            test.assert(commentRow(comment.name).length === 1, '投稿したコメント ' + comment.name + ' が管理画面の一覧にありません。');
        });
        test.assert($('form.bulk input.bulk:checked').length === 0, '一括削除の対象が、選んでいないのに選択されています。前回のテストが中断した可能性があります。');

        test.click('form.bulk input[type="submit"]');
    },
    // 対象を選ばずに送ると、対象が無い旨が出ることを確認して、一覧に戻る
    function() {
        test.assertText('div.alert-danger', '一括削除対象が選択されていません。', '対象を選ばずに一括削除を送ったときの案内が表示されていません。');
        test.assertNotExists('form.delete', '対象を選んでいないのに、削除のフォームが表示されています。');

        test.click('a:contains("戻る")');
    },
    // 3件を選んで、再読み込みする
    function() {
        test.assert(commentRow(scenarioBulkKept).length === 1, '一覧に戻れていません。');

        bulkCheck(scenarioBulkComments.map(function(comment) {
            return comment.name;
        }), true, function() {
            test.reload();
        });
    },
    // 再読み込みしても選択が残っていることを確認して、3件目の選択を外して再読み込みする
    function() {
        scenarioBulkComments.forEach(function(comment) {
            test.assert(bulkCheckbox(comment.name).prop('checked') === true, '再読み込みすると、' + comment.name + ' の選択が外れています。選択がセッションに保存されていません。');
        });

        bulkCheck([scenarioBulkKept], false, function() {
            test.reload();
        });
    },
    // 外した選択が残っていないことを確認して、一括削除を送る
    function() {
        scenarioBulkDeleted.forEach(function(name) {
            test.assert(bulkCheckbox(name).prop('checked') === true, '再読み込みすると、' + name + ' の選択が外れています。');
        });
        test.assert(bulkCheckbox(scenarioBulkKept).prop('checked') === false, '選択を外した ' + scenarioBulkKept + ' が、再読み込みすると選択されています。');

        test.click('form.bulk input[type="submit"]');
    },
    // 確認画面に、選んだ2件だけが並ぶことを確認して削除する
    function() {
        // 確認画面の列は ID・日時・名前の順
        var names = $('table tbody tr').map(function() {
            return $(this).find('td').eq(2).text().trim();
        }).get();
        var form  = $('form.delete');

        // ほかのシナリオの選択がセッションに残っていると、関係の無いコメントまで消してしまうので、件数も確かめる
        test.assert(names.length === scenarioBulkDeleted.length, '一括削除の確認画面に、選んでいないコメントが含まれています（' + names.join('、') + '）。');
        scenarioBulkDeleted.forEach(function(name) {
            test.assert(names.indexOf(name) !== -1, '一括削除の確認画面に ' + name + ' がありません。');
        });
        test.assert(names.indexOf(scenarioBulkKept) === -1, '一括削除の確認画面に、選択を外した ' + scenarioBulkKept + ' があります。');
        test.assert(form.find('input[name="list[]"]').length === scenarioBulkDeleted.length, '削除のフォームで送る対象の数が、選んだ数と違います。');

        form.off('submit');
        test.click('form.delete button[type="submit"]');
    },
    // 2件だけ削除できたことと、選択が消えたことを確認して、残した1件の編集ページに移動
    function() {
        test.assertText('div.alert-success', 'コメントを削除しました。', 'コメントを一括削除できていません。');
        scenarioBulkDeleted.forEach(function(name) {
            test.assert(commentRow(name).length === 0, '一括削除したコメント ' + name + ' が一覧に残っています。');
        });
        test.assert(commentRow(scenarioBulkKept).length === 1, '選択を外したコメント ' + scenarioBulkKept + ' まで削除されています。');
        test.assert($('form.bulk input.bulk:checked').length === 0, '一括削除の後も、選択が残っています。');

        test.click(commentRow(scenarioBulkKept).find('a:contains("編集")'), '一覧の ' + scenarioBulkKept + ' の編集リンクが見つかりません。');
    },
    // 残したコメントを削除
    function() {
        var form = $('form.delete');

        test.assertValue('form.register [name="name"]', scenarioBulkKept, '削除対象が ' + scenarioBulkKept + ' ではありません。');
        test.assert(form.length === 1, '削除フォームが表示されていません。');

        form.off('submit');
        test.click('form.delete button[type="submit"]');
    },
    // 削除できたことを確認してエントリー管理ページに移動
    function() {
        test.assertText('div.alert-success', 'コメントを削除しました。', 'コメントを削除できていません。');
        test.assert(commentRow(scenarioBulkKept).length === 0, '削除したコメントが一覧に残っています。');

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
]);
