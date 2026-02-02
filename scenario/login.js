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
        //test.saveScreenshot('login_' + test.date + test.time + '.png', function() {
        //    $('a:contains("トップページへ戻る")')[0].click();
        //});
        $('a:contains("ホームページへ戻る")')[0].click();
    }
];
