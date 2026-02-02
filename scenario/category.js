test.scenario = [
    // 初期ページからログインページに移動
    function() {
        $('a:contains("ログイン")')[0].click();
    },
    // 管理者用ページにログイン
    function() {
        var form = $('form:eq(0)');

        form.find('input[name="username"]').val('admin');
        form.find('input[name="password"]').val('abcd1234');
        form.find('button[type="submit"]').click();
    },
    // カテゴリー管理ページに移動
    function() {
        $('a:contains("カテゴリー管理")')[0].click();
    },
    // カテゴリー登録ページに移動
    function() {
        $('a:contains("カテゴリー登録")')[0].click();
    },
    // カテゴリーを登録
    function() {
        var form = $('form:eq(0)');

        form.find('input[name="code"]').val('test1');
        form.find('input[name="name"]').val('テスト1');
        form.find('select[name="type_id"]').val('1');
        form.find('button[type="submit"]').click();
    },
    // カテゴリー管理ページに移動
    function() {
        $('a:contains("カテゴリー管理")')[0].click();
    },
    // カテゴリー編集ページに移動
    function() {
        $('table:eq(0) a:eq(0)')[0].click();
    },
    // カテゴリーを編集
    function() {
        var form = $('form:eq(0)');

        form.find('input[name="code"]').val('test2');
        form.find('input[name="name"]').val('テスト2');
        form.find('button[type="submit"]').click();
    },
    // カテゴリー管理ページに移動
    function() {
        $('a:contains("カテゴリー管理")')[0].click();
    },
    // カテゴリー編集ページに移動
    function() {
        $('table:eq(0) a:eq(0)')[0].click();
    },
    // カテゴリーを削除
    function() {
        var form = $('form:eq(1)');

        form.off('submit');
        form.find('button[type="submit"]').click();
    },
    // ホームに移動
    function() {
        $('a:contains("ホーム")')[0].click();
    },
    // 管理者用ページからログアウト
    function() {
        $('a:contains("管理者さん")')[0].click();
        setTimeout(function() {
            $('a:contains("ログアウト")')[0].click();
        }, 500);
    },
    // 初期ページに戻る
    function() {
        $('a:contains("ホームページへ戻る")')[0].click();
    }
];
