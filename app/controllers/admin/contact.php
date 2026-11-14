<?php

// 絞り込み条件を作成
$filters = model('filter_contacts', $_GET, [
    'associate' => true,
]);
if ($filters['where'] === '') {
    $filters['where'] = null;
}

// 絞り込み条件を引き継ぎ
$_view['contact_filter'] = $filters['pager'];

// 絞り込み条件を初期化
if (!isset($_GET['keyword'])) {
    $_GET['keyword'] = null;
}
if (!isset($_GET['status'])) {
    $_GET['status'] = null;
}

/*
// 表示していない状況を取得（状況を指定していないときは、対応の済んだものを表示しない）
// 一覧での案内をいったん非表示にしたので、あわせてコメントアウトしている（戻すときは views/admin/contact.php も戻す）
$_view['contact_status_hidden'] = [];
if (empty($_GET['status'])) {
    foreach ($GLOBALS['config']['contact_status_hidden'] as $status) {
        if (isset($GLOBALS['config']['option']['contact']['status'][$status])) {
            $_view['contact_status_hidden'][] = $GLOBALS['config']['option']['contact']['status'][$status];
        }
    }
}
*/

// ページを取得
if (isset($_GET['page'])) {
    $_GET['page'] = intval($_GET['page']);
} else {
    $_GET['page'] = 1;

    $_SESSION['bulk']['contacts'] = [];
}

// お問い合わせを取得
$_view['contacts'] = model('select_contacts', [
    'where'    => $filters['where'],
    'order_by' => 'contacts.id DESC',
    'limit'    => [
        ':offset, :limit',
        [
            'offset' => $GLOBALS['setting']['number_limit_admin_contact'] * ($_GET['page'] - 1),
            'limit'  => $GLOBALS['setting']['number_limit_admin_contact'],
        ],
    ],
], [
    'associate' => true,
]);

$contact_count = model('select_contacts', [
    'select' => 'COUNT(DISTINCT contacts.id) AS count',
    'where'  => $filters['where'],
], [
    'associate' => true,
]);
$_view['contact_count'] = $contact_count[0]['count'];
$_view['contact_page']  = ceil($contact_count[0]['count'] / $GLOBALS['setting']['number_limit_admin_contact']);

// タイトル
$_view['title'] = 'お問い合わせ管理';
