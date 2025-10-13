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
    // 管理者用ページからログアウト
    function() {
        $('a:contains("ログアウト")')[0].click();
    },
    // 初期ページに戻る
    function() {
        //test.saveScreenshot('login_' + test.date + test.time + '.png', function() {
        //    $('a:contains("トップページへ戻る")')[0].click();
        //});
        $('a:contains("トップページへ戻る")')[0].click();
    }
];
