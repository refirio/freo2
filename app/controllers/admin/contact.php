<?php

// ページを取得
if (isset($_GET['page'])) {
    $_GET['page'] = intval($_GET['page']);
} else {
    $_GET['page'] = 1;

    $_SESSION['bulk']['contact'] = [];
}

// お問い合わせを取得
$_view['contacts'] = model('select_contacts', [
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
], [
    'associate' => true,
]);
$_view['contact_count'] = $contact_count[0]['count'];
$_view['contact_page']  = ceil($contact_count[0]['count'] / $GLOBALS['setting']['number_limit_admin_contact']);

// タイトル
$_view['title'] = 'お問い合わせ管理';
