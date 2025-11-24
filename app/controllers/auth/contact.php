<?php

// ページを取得
if (isset($_GET['page'])) {
    $_GET['page'] = intval($_GET['page']);
} else {
    $_GET['page'] = 1;
}

// お問い合わせを取得
$_view['contacts'] = model('select_contacts', [
    'where'    => [
        'contacts.user_id = :user_id',
        [
            'user_id' => $_SESSION['auth']['user']['id'],
        ],
    ],
    'order_by' => 'contacts.id DESC',
    'limit'    => [
        ':offset, :limit',
        [
            'offset' => $GLOBALS['config']['limit']['contact'] * ($_GET['page'] - 1),
            'limit'  => $GLOBALS['config']['limit']['contact'],
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
$_view['contact_page']  = ceil($contact_count[0]['count'] / $GLOBALS['config']['limit']['contact']);

// タイトル
$_view['title'] = 'お問い合わせ履歴';
