/* テスト用のお問い合わせ（test オブジェクトはページごとに作り直されるため、値は定数で持つ。一覧では件名、確認画面ではお名前で特定する） */
var scenarioBulkContacts = [
    { name: 'シナリオ一括太郎1', subject: 'シナリオの一括削除のお問い合わせ1' },
    { name: 'シナリオ一括太郎2', subject: 'シナリオの一括削除のお問い合わせ2' },
    { name: 'シナリオ一括太郎3', subject: 'シナリオの一括削除のお問い合わせ3' }
];
var scenarioBulkEmail   = 'scenario-contact-bulk@example.com';
var scenarioBulkMessage = 'シナリオから一括削除のために送信したお問い合わせです。';

/* 一括削除するお問い合わせ（3件目は選択を外して残す） */
var scenarioBulkDeleted = [scenarioBulkContacts[0], scenarioBulkContacts[1]];
var scenarioBulkKept    = scenarioBulkContacts[2];

/* お問い合わせ一覧から、件名で対象の行を取得する */
var contactRow = function(subject) {
    return $('table tbody tr').filter(function() {
        return $(this).text().indexOf(subject) !== -1;
    });
};

/* 一覧の行から、一括削除のチェックボックスを取得する */
var bulkCheckbox = function(contact) {
    return contactRow(contact.subject).find('input.bulk');
};

/* チェックボックスを切り替えて、選択を控える Ajax が終わってからコールバックを実行する */
var bulkCheck = function(contacts, checked, callback) {
    for (var i = 0; i < contacts.length; i++) {
        if (bulkCheckbox(contacts[i]).length !== 1) {
            return test.abort('一覧の ' + contacts[i].subject + ' に一括削除のチェックボックスがありません。');
        }
        bulkCheckbox(contacts[i]).prop('checked', checked).trigger('change');
    }

    // admin.js が change ごとに /admin/contact_bulk へ送るので、すべて終わるまで待つ
    return test.wait(function() {
        return $.active === 0;
    }, callback, '一括削除の選択を保存する通信が終わりませんでした。');
};

/* お問い合わせを1件送信するステップを作る（入力 → 確認 → 完了） */
var sendSteps = function(contact, index) {
    return [
        // ゲストとして入力して確認画面に進む
        function() {
            var form = $('form.register');

            test.assert(form.length === 1, 'お問い合わせフォームが表示されていません。');

            form.find('input[name="name"]').val(contact.name);
            form.find('input[name="email"]').val(scenarioBulkEmail);
            form.find('input[name="subject"]').val(contact.subject);
            form.find('textarea[name="message"]').val(scenarioBulkMessage);
            test.click('form.register button[type="submit"]');
        },
        // 確認画面から送信する
        function() {
            test.assertText('#contact dl', contact.subject, '確認画面にお問い合わせ件名が表示されていません。');

            test.click('form button[type="submit"]', '確認画面に送信ボタンがありません。');
        },
        // 送信できたことを確認して、次のお問い合わせを送信する（最後のときは、ログインページに移動）
        function() {
            test.assertText('#contact', 'お問い合わせを送信しました。', 'お問い合わせ ' + contact.subject + ' を送信できていません。');

            if (index < scenarioBulkContacts.length - 1) {
                test.visit('/contact/');
            } else {
                test.visit('/auth/');
            }
        }
    ];
};

var sendScenario = [];
scenarioBulkContacts.forEach(function(contact, index) {
    sendScenario = sendScenario.concat(sendSteps(contact, index));
});

test.scenario = [
    // 初期ページからお問い合わせページに移動
    function() {
        test.assertExists('a:contains("お問い合わせ")', '初期ページにお問い合わせへのリンクがありません。');
        test.click('a:contains("お問い合わせ")');
    }
].concat(sendScenario).concat([
    // 管理者用ページにログイン
    function() {
        var form = $('form:eq(0)');

        test.assert(form.find('input[name="username"]').length === 1, 'ログインフォームが表示されていません。');

        form.find('input[name="username"]').val('admin');
        form.find('input[name="password"]').val('abcd1234');
        test.click('form:eq(0) button[type="submit"]');
    },
    // ログインできたことを確認してお問い合わせ管理ページに移動
    function() {
        test.assertText('body', '管理者さん', 'ログインできていません。');
        test.click('a:contains("お問い合わせ管理")');
    },
    // 送信した3件が1件ずつ並ぶことを確認して（前回の残りがあれば2件になる）、何も選ばずに一括削除を送る
    function() {
        scenarioBulkContacts.forEach(function(contact) {
            test.assert(contactRow(contact.subject).length === 1, 'お問い合わせ ' + contact.subject + ' が一覧に1件だけになっていません。前回のテストが中断した可能性があります。');
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
        test.assert(contactRow(scenarioBulkKept.subject).length === 1, '一覧に戻れていません。');

        bulkCheck(scenarioBulkContacts, true, function() {
            test.reload();
        });
    },
    // 再読み込みしても選択が残っていることを確認して、3件目の選択を外して再読み込みする
    function() {
        scenarioBulkContacts.forEach(function(contact) {
            test.assert(bulkCheckbox(contact).prop('checked') === true, '再読み込みすると、' + contact.subject + ' の選択が外れています。選択がセッションに保存されていません。');
        });

        bulkCheck([scenarioBulkKept], false, function() {
            test.reload();
        });
    },
    // 外した選択が残っていないことを確認して、一括削除を送る
    function() {
        scenarioBulkDeleted.forEach(function(contact) {
            test.assert(bulkCheckbox(contact).prop('checked') === true, '再読み込みすると、' + contact.subject + ' の選択が外れています。');
        });
        test.assert(bulkCheckbox(scenarioBulkKept).prop('checked') === false, '選択を外した ' + scenarioBulkKept.subject + ' が、再読み込みすると選択されています。');

        test.click('form.bulk input[type="submit"]');
    },
    // 確認画面に、選んだ2件だけが並ぶことを確認して削除する
    function() {
        // 確認画面の列は ID・日時・名前の順
        var names = $('table tbody tr').map(function() {
            return $(this).find('td').eq(2).text().trim();
        }).get();
        var form  = $('form.delete');

        // ほかのシナリオの選択がセッションに残っていると、関係の無いお問い合わせまで消してしまうので、件数も確かめる
        test.assert(names.length === scenarioBulkDeleted.length, '一括削除の確認画面に、選んでいないお問い合わせが含まれています（' + names.join('、') + '）。');
        scenarioBulkDeleted.forEach(function(contact) {
            test.assert(names.indexOf(contact.name) !== -1, '一括削除の確認画面に ' + contact.name + ' がありません。');
        });
        test.assert(names.indexOf(scenarioBulkKept.name) === -1, '一括削除の確認画面に、選択を外した ' + scenarioBulkKept.name + ' があります。');
        test.assert(form.find('input[name="list[]"]').length === scenarioBulkDeleted.length, '削除のフォームで送る対象の数が、選んだ数と違います。');

        form.off('submit');
        test.click('form.delete button[type="submit"]');
    },
    // 2件だけ削除できたことと、選択が消えたことを確認して、残した1件の編集ページに移動
    function() {
        test.assertText('div.alert-success', 'お問い合わせを削除しました。', 'お問い合わせを一括削除できていません。');
        scenarioBulkDeleted.forEach(function(contact) {
            test.assert(contactRow(contact.subject).length === 0, '一括削除したお問い合わせ ' + contact.subject + ' が一覧に残っています。');
        });
        test.assert(contactRow(scenarioBulkKept.subject).length === 1, '選択を外したお問い合わせ ' + scenarioBulkKept.subject + ' まで削除されています。');
        test.assert($('form.bulk input.bulk:checked').length === 0, '一括削除の後も、選択が残っています。');

        test.click(contactRow(scenarioBulkKept.subject).find('a:contains("編集")'), '一覧の ' + scenarioBulkKept.subject + ' の編集リンクが見つかりません。');
    },
    // 残したお問い合わせを削除
    function() {
        var form = $('form.delete');

        test.assertValue('form.register input[name="subject"]', scenarioBulkKept.subject, '削除対象が ' + scenarioBulkKept.subject + ' ではありません。');
        test.assert(form.length === 1, '削除フォームが表示されていません。');

        form.off('submit');
        test.click('form.delete button[type="submit"]');
    },
    // 削除できたことを確認してホームに移動
    function() {
        test.assertText('div.alert-success', 'お問い合わせを削除しました。', 'お問い合わせを削除できていません。');
        test.assert(contactRow(scenarioBulkKept.subject).length === 0, '削除したお問い合わせが一覧に残っています。');

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
