/* テスト用ページ（test オブジェクトはページごとに作り直されるため、値は定数で持つ） */
var treePages = {
    parent:     { code: 'scenariotree',             title: 'ツリーの親' },
    child:      { code: 'scenariotree/child',       title: 'ツリーの子' },
    grandchild: { code: 'scenariotree/child/grand', title: 'ツリーの孫' },
    orphan:     { code: 'scenariotree/none/grand',  title: 'ツリーの離れた孫' }
};

/* ページ一覧から、コードで対象の行を取得する */
var pageRow = function(code) {
    return $('table tbody tr').filter(function() {
        return $(this).find('td code').text().trim() === code;
    });
};

/* 子ページの一覧へのリンク（タイトル）を取得する */
var childrenLink = function(code) {
    return pageRow(code).find('a[href*="parent="]');
};

/* テスト用ページがどれも一覧に無いことを確認する */
var assertNoTreePages = function(message) {
    for (var key in treePages) {
        test.assert(pageRow(treePages[key].code).length === 0, 'ページ ' + treePages[key].code + ' が' + message);
    }
};

/* 本文を入力してからコールバックを実行する */
var fillText = function(html, callback) {
    var textarea = $('form.register textarea[name="text"]');

    if (textarea.length === 0 || !textarea.hasClass('editor')) {
        textarea.val(html);

        return callback();
    }

    // WYSIWYGエディタは非同期に作られるので、admin.js がインスタンスを保持するまで待つ
    return test.wait(function() {
        return window.text != null && typeof window.text.setData === 'function';
    }, function() {
        window.text.setData(html);

        callback();
    }, 'WYSIWYGエディタ（CKEditor）が初期化されませんでした。CDNから読み込めているか確認してください。');
};

/* ページを登録する */
var registerPage = function(page) {
    var form = $('form.register');

    test.assert(form.find('input[name="code"]').length === 1, 'ページ登録フォームが表示されていません。');

    form.find('input[name="code"]').val(page.code);
    form.find('input[name="title"]').val(page.title);

    fillText('<p>' + page.title + 'の本文です。</p>', function() {
        test.click('form.register button[type="submit"]');
    });
};

/* 編集画面で対象を確認して、ページを削除する */
var deletePage = function(page) {
    var form = $('form.delete');

    test.assertValue('form.register input[name="code"]', page.code, '削除対象が ' + page.code + ' ではありません。');
    test.assert(form.length === 1, '削除フォームが表示されていません。');

    form.off('submit');
    test.click('form.delete button[type="submit"]');
};

/* パンくずの末尾（表示中の階層）を確認する */
var assertBreadcrumb = function(title) {
    test.assertText('ol.breadcrumb li.active', title, 'パンくずの末尾が「' + title + '」になっていません。');
};

/* パンくずに、親ページの一覧へのリンクが並んでいることを確認する */
var assertBreadcrumbLinks = function(pages) {
    var links = $('ol.breadcrumb a[href*="parent="]').map(function() {
        return $(this).text().trim();
    }).get();
    var titles = pages.map(function(page) {
        return page.title;
    });

    test.assert(links.join(' > ') === titles.join(' > '), 'パンくずの親ページが「' + titles.join(' > ') + '」になっていません。（' + links.join(' > ') + '）');
};

/* 登録画面のコードに、親ページのコードが入っていることを確認する */
var assertCodePrefilled = function(page) {
    test.assertValue('form.register input[name="code"]', page.code + '/', 'コードに親ページのコード ' + page.code + '/ が入っていません。');
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
    // ログインできたことを確認してページ管理ページに移動
    function() {
        test.assertText('body', '管理者さん', 'ログインできていません。');
        test.click('a:contains("ページ管理")');
    },
    // 前回のテストデータが残っていないことを確認してページ登録ページに移動
    function() {
        assertNoTreePages('残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("ページ登録")');
    },
    // 末尾がスラッシュのコードは入力エラーになることを確認して、フォームを戻す
    function() {
        var form = $('form.register');

        test.assert(form.find('input[name="code"]').length === 1, 'ページ登録フォームが表示されていません。');

        form.find('input[name="code"]').val(treePages.parent.code + '/');
        form.find('input[name="title"]').val(treePages.parent.title);

        $('div.warning').remove();
        test.click('form.register button[type="submit"]');
        test.wait(function() {
            return $('form.register div.warning').length > 0;
        }, function() {
            test.assertText('form.register div.warning', '「/」', '末尾のスラッシュの入力エラーが表示されていません。');
            test.assert($('form.register div.warning').length === 1, 'コード以外にも入力エラーが表示されています。（' + $('form.register div.warning').text() + '）');

            test.reload();
        }, '入力エラーが表示されませんでした。');
    },
    // 親ページを登録
    function() {
        registerPage(treePages.parent);
    },
    // 登録できたことと、パンくずに親ページが無いことを確認して、一覧のトップを開く
    function() {
        test.assertText('div.alert-success', 'ページを登録しました。', '親ページを登録できていません。');
        test.assertValue('form.register input[name="code"]', treePages.parent.code, '登録した後に、親ページの編集画面が表示されていません。');
        assertBreadcrumbLinks([]);

        test.visit('/admin/page');
    },
    // 一覧のトップに親ページがあることを確認して、（まだ子の無い）親ページの一覧を開く
    function() {
        test.assert(pageRow(treePages.parent.code).length === 1, '親ページが一覧にありません。');
        test.assert(childrenLink(treePages.parent.code).length === 0, '子ページが無いのに、子ページの一覧へのリンクがあります。');

        test.visit('/admin/page?parent=' + encodeURIComponent(treePages.parent.code));
    },
    // 空の一覧が表示されることを確認して、ページ登録ページに移動
    function() {
        assertBreadcrumb(treePages.parent.title);
        test.assert($('table tbody tr').length === 0, '子ページの無い親ページの一覧に、行が表示されています。');

        test.click('a:contains("ページ登録")');
    },
    // コードに親ページのコードが入り、パンくずに親ページが出ることを確認して、子ページを登録
    function() {
        assertCodePrefilled(treePages.parent);
        assertBreadcrumbLinks([treePages.parent]);

        registerPage(treePages.child);
    },
    // 登録できたことと、パンくずに親ページが出ることを確認して、親ページの一覧を開く
    function() {
        test.assertText('div.alert-success', 'ページを登録しました。', '子ページを登録できていません。');
        test.assertValue('form.register input[name="code"]', treePages.child.code, '登録した後に、子ページの編集画面が表示されていません。');
        assertBreadcrumbLinks([treePages.parent]);

        test.visit('/admin/page?parent=' + encodeURIComponent(treePages.parent.code));
    },
    // 親ページの一覧に子ページがあることを確認して、途中の階層が無いページを登録
    function() {
        assertBreadcrumb(treePages.parent.title);
        test.assert(pageRow(treePages.child.code).length === 1, '子ページが親ページの一覧にありません。');

        test.click('a:contains("ページ登録")');
    },
    function() {
        assertCodePrefilled(treePages.parent);

        registerPage(treePages.orphan);
    },
    // パンくずの親が、最も近い祖先（親ページ）になっていることを確認して、（まだ子の無い）子ページの一覧を開く
    function() {
        test.assertText('div.alert-success', 'ページを登録しました。', '途中の階層が無いページを登録できていません。');
        test.assertValue('form.register input[name="code"]', treePages.orphan.code, '登録した後に、途中の階層が無いページの編集画面が表示されていません。');
        assertBreadcrumbLinks([treePages.parent]);

        test.visit('/admin/page?parent=' + encodeURIComponent(treePages.child.code));
    },
    // 子ページの一覧からページ登録ページに移動
    function() {
        assertBreadcrumb(treePages.child.title);
        test.click('a:contains("ページ登録")');
    },
    // コードに子ページのコードが入り、パンくずに祖先が並ぶことを確認して、孫ページを登録
    function() {
        assertCodePrefilled(treePages.child);
        assertBreadcrumbLinks([treePages.parent, treePages.child]);

        registerPage(treePages.grandchild);
    },
    // パンくずに祖先が並ぶことを確認して、パンくずから親ページの一覧に移動
    function() {
        test.assertText('div.alert-success', 'ページを登録しました。', '孫ページを登録できていません。');
        test.assertValue('form.register input[name="code"]', treePages.grandchild.code, '登録した後に、孫ページの編集画面が表示されていません。');
        assertBreadcrumbLinks([treePages.parent, treePages.child]);
        test.assertExists('ol.breadcrumb a:contains("ページ管理")', 'パンくずに一覧のトップへのリンクがありません。');

        test.click('ol.breadcrumb a:contains("' + treePages.parent.title + '")');
    },
    // 親ページの一覧には、子と途中の階層が無いページだけがあることを確認して、一覧のトップに戻る
    function() {
        assertBreadcrumb(treePages.parent.title);
        test.assert(pageRow(treePages.child.code).length === 1, '親ページの一覧に子ページがありません。');
        test.assert(pageRow(treePages.orphan.code).length === 1, '親ページの一覧に途中の階層が無いページがありません。');
        test.assert(pageRow(treePages.grandchild.code).length === 0, '親ページの一覧に孫ページが出ています。');
        test.assert(pageRow(treePages.parent.code).length === 0, '親ページの一覧に親ページ自身が出ています。');
        test.assert(childrenLink(treePages.child.code).length === 1, '孫ページのある子ページのタイトルに、子ページの一覧へのリンクがありません。');
        test.assert(childrenLink(treePages.orphan.code).length === 0, '子の無いページのタイトルに、子ページの一覧へのリンクがあります。');

        test.click('ol.breadcrumb a:contains("ページ管理")');
    },
    // 一覧のトップには親ページだけがあることを確認して、タイトルから子ページの一覧に移動
    function() {
        test.assert(pageRow(treePages.parent.code).length === 1, '一覧のトップに親ページがありません。');
        test.assert(pageRow(treePages.child.code).length === 0, '一覧のトップに子ページが出ています。');
        test.assert(pageRow(treePages.orphan.code).length === 0, '一覧のトップに途中の階層が無いページが出ています。');
        test.assert(pageRow(treePages.grandchild.code).length === 0, '一覧のトップに孫ページが出ています。');

        test.click(childrenLink(treePages.parent.code), '親ページのタイトルに、子ページの一覧へのリンクがありません。');
    },
    // 親ページの一覧から、さらに子ページの一覧に移動
    function() {
        assertBreadcrumb(treePages.parent.title);
        test.click(childrenLink(treePages.child.code), '子ページのタイトルに、子ページの一覧へのリンクがありません。');
    },
    // 孫ページの編集ページに移動
    function() {
        assertBreadcrumb(treePages.child.title);
        test.click(pageRow(treePages.grandchild.code).find('a:contains("編集")'), '一覧の ' + treePages.grandchild.code + ' の編集リンクが見つかりません。');
    },
    // 編集画面のパンくずに祖先が並ぶことを確認して、孫ページを削除
    function() {
        assertBreadcrumbLinks([treePages.parent, treePages.child]);

        deletePage(treePages.grandchild);
    },
    // 子ページの一覧に戻ったことを確認して、存在しない親ページの一覧を開く
    function() {
        test.assertText('div.alert-success', 'ページを削除しました。', '孫ページを削除できていません。');
        assertBreadcrumb(treePages.child.title);
        test.assert(pageRow(treePages.grandchild.code).length === 0, '削除した孫ページが一覧に残っています。');

        test.visit('/admin/page?parent=' + encodeURIComponent('scenariotree/none'));
    },
    // エラーになることを確認して、一覧のトップに移動
    function() {
        test.assertText('div.alert-danger', '親ページが見つかりません。', '存在しない親ページの一覧がエラーになっていません。');
        test.visit('/admin/page');
    },
    // 親ページの編集ページに移動
    function() {
        test.click(pageRow(treePages.parent.code).find('a:contains("編集")'), '一覧の ' + treePages.parent.code + ' の編集リンクが見つかりません。');
    },
    // 親ページを削除
    function() {
        deletePage(treePages.parent);
    },
    // 親ページが無くなると、子ページが一覧のトップに出ることを確認して、子ページの編集ページに移動
    function() {
        test.assertText('div.alert-success', 'ページを削除しました。', '親ページを削除できていません。');
        assertBreadcrumb('ページ管理');
        test.assert(pageRow(treePages.parent.code).length === 0, '削除した親ページが一覧に残っています。');
        test.assert(pageRow(treePages.child.code).length === 1, '親ページを削除した後、子ページが一覧のトップに出ていません。');
        test.assert(pageRow(treePages.orphan.code).length === 1, '親ページを削除した後、途中の階層が無いページが一覧のトップに出ていません。');

        test.click(pageRow(treePages.child.code).find('a:contains("編集")'), '一覧の ' + treePages.child.code + ' の編集リンクが見つかりません。');
    },
    // 子ページを削除
    function() {
        deletePage(treePages.child);
    },
    // 途中の階層が無いページの編集ページに移動
    function() {
        test.assertText('div.alert-success', 'ページを削除しました。', '子ページを削除できていません。');
        test.click(pageRow(treePages.orphan.code).find('a:contains("編集")'), '一覧の ' + treePages.orphan.code + ' の編集リンクが見つかりません。');
    },
    // 途中の階層が無いページを削除
    function() {
        deletePage(treePages.orphan);
    },
    // すべて削除できたことを確認して、管理者用ページのホームに移動
    function() {
        test.assertText('div.alert-success', 'ページを削除しました。', '途中の階層が無いページを削除できていません。');
        assertNoTreePages('一覧に残っています。');

        test.visit('/admin/');
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
