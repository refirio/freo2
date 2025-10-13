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
    // カテゴリ管理ページに移動
    function() {
        $('a:contains("カテゴリ管理")')[0].click();
    },
    // カテゴリ登録ページに移動
    function() {
        $('a:contains("カテゴリ登録")')[0].click();
    },
    // カテゴリを登録
    function() {
        var form = $('form:eq(0)');

        form.find('input[name="code"]').val('test1');
        form.find('input[name="name"]').val('テスト1');
        form.find('button[type="submit"]').click();
    },
    // カテゴリ編集ページに移動
    function() {
        $('table:eq(0) a:eq(0)')[0].click();
    },
    // カテゴリを編集
    function() {
        var form = $('form:eq(0)');

        form.find('input[name="code"]').val('test2');
        form.find('input[name="name"]').val('テスト2');
        form.find('button[type="submit"]').click();
    },
    // カテゴリ編集ページに移動
    function() {
        $('table:eq(0) a:eq(0)')[0].click();
    },
    // カテゴリを削除
    function() {
        var form = $('form:eq(1)');

        form.off('submit');
        form.find('button[type="submit"]').click();
    },
    // 管理者用ページからログアウト
    function() {
        $('a:contains("ログアウト")')[0].click();
    },
    // 初期ページに戻る
    function() {
        $('a:contains("トップページへ戻る")')[0].click();
    }
];
