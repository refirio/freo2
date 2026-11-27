/* テスト用エントリー（test オブジェクトはページごとに作り直されるため、値は定数で持つ） */
var scenarioEntry = {
    code:  'scenario-period',
    title: 'シナリオの公開期間'
};

/* 公開期間に使う日時（datetimepicker と同じ Y-m-d H:i 形式） */
var periodPast   = '2000-01-01 00:00';
var periodFuture = '2100-01-01 00:00';

/* エントリー一覧から、コードで対象の行を取得する */
var codeRow = function(code) {
    return $('table tbody tr').filter(function() {
        return $(this).find('td code').text().trim() === code;
    });
};

/* 公開期間を入力して送信する */
var submitPeriod = function(begin, end) {
    var form = $('form.register');

    form.find('input[name="public_begin"]').val(begin);
    form.find('input[name="public_end"]').val(end);

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
    // ログインできたことを確認してエントリー管理ページに移動
    function() {
        test.assertText('body', '管理者さん', 'ログインできていません。');
        test.click('a:contains("エントリー管理")');
    },
    // 前回のテストデータが残っていないことを確認してエントリー登録ページに移動
    function() {
        test.assert(codeRow(scenarioEntry.code).length === 0, 'エントリー ' + scenarioEntry.code + ' が残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("エントリー登録")');
    },
    // 公開期間の中に入るエントリーを登録
    function() {
        var form = $('form.register');

        test.assert(form.find('input[name="public_begin"]').length === 1, 'エントリー登録フォームが表示されていません。');
        test.assertValue('form.register select[name="public"]', 'all', '公開の初期値が「公開」になっていません。');
        test.assert($('.for-public').is(':visible'), '公開が「公開」のときに公開期間の欄が表示されていません。');

        form.find('input[name="code"]').val(scenarioEntry.code);
        form.find('input[name="title"]').val(scenarioEntry.title);
        submitPeriod(periodPast, periodFuture);
    },
    // 登録できたことを確認して、公開側の詳細を開く
    function() {
        test.assertText('div.alert-success', 'エントリーを登録しました。', 'エントリーを登録できていません。');
        test.assertValue('form.register input[name="code"]', scenarioEntry.code, '登録した後に、登録したエントリーの編集画面が表示されていません。');

        test.visit('/entry/detail/' + scenarioEntry.code);
    },
    // 公開期間の中なので表示されることを確認して、管理画面に戻る
    function() {
        test.assertText('#entry h2', scenarioEntry.title, '公開期間の中のエントリーが公開側に表示されていません。');

        test.visit('/admin/entry');
    },
    // エントリー編集ページに移動
    function() {
        test.click(codeRow(scenarioEntry.code).find('a:contains("編集")'), '一覧の ' + scenarioEntry.code + ' の編集リンクが見つかりません。');
    },
    // 公開開始日時を未来にする
    function() {
        test.assertValue('form.register input[name="code"]', scenarioEntry.code, '編集対象が ' + scenarioEntry.code + ' ではありません。');
        test.assertValue('form.register input[name="public_begin"]', periodPast, '登録した公開開始日時が編集画面に復元されていません。');
        test.assertValue('form.register input[name="public_end"]', periodFuture, '登録した公開終了日時が編集画面に復元されていません。');

        submitPeriod(periodFuture, periodFuture);
    },
    // 編集できたことを確認して、公開側の詳細を開く
    function() {
        test.assertText('div.alert-success', 'エントリーを登録しました。', 'エントリーを編集できていません。');

        test.visit('/entry/detail/' + scenarioEntry.code);
    },
    // 公開開始前なので表示されないことを確認して、管理画面に戻る
    function() {
        test.assertText('div.alert-danger', 'エントリーが見つかりません。', '公開開始前のエントリーが公開側に表示されています。');
        test.assertNoText('body', scenarioEntry.title, '公開開始前のエントリーのタイトルが公開側に表示されています。');

        test.visit('/admin/entry');
    },
    // エントリー編集ページに移動
    function() {
        test.click(codeRow(scenarioEntry.code).find('a:contains("編集")'), '一覧の ' + scenarioEntry.code + ' の編集リンクが見つかりません。');
    },
    // 公開開始日時を空にして、公開終了日時を過去にする
    function() {
        test.assertValue('form.register input[name="public_begin"]', periodFuture, '編集した公開開始日時が編集画面に復元されていません。');

        submitPeriod('', periodPast);
    },
    // 編集できたことを確認して、公開側の詳細を開く
    function() {
        test.assertText('div.alert-success', 'エントリーを登録しました。', 'エントリーを編集できていません。');

        test.visit('/entry/detail/' + scenarioEntry.code);
    },
    // 公開終了後なので表示されないことを確認して、管理画面に戻る
    function() {
        test.assertText('div.alert-danger', 'エントリーが見つかりません。', '公開終了後のエントリーが公開側に表示されています。');
        test.assertNoText('body', scenarioEntry.title, '公開終了後のエントリーのタイトルが公開側に表示されています。');

        test.visit('/admin/entry');
    },
    // エントリー編集ページに移動
    function() {
        test.click(codeRow(scenarioEntry.code).find('a:contains("編集")'), '一覧の ' + scenarioEntry.code + ' の編集リンクが見つかりません。');
    },
    // エントリーを削除
    function() {
        var form = $('form.delete');

        test.assertValue('form.register input[name="code"]', scenarioEntry.code, '削除対象が ' + scenarioEntry.code + ' ではありません。');
        test.assertValue('form.register input[name="public_begin"]', '', '空にした公開開始日時が編集画面に残っています。');
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
