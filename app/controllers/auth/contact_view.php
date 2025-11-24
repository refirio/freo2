<?php

// お問い合わせを取得
$contacts = model('select_contacts', [
    'where' => [
        'contacts.user_id = user_id AND contacts.id = :id',
        [
            'user_id' => $_SESSION['auth']['user']['id'],
            'id'      => $_GET['id'],
        ],
    ],
], [
    'associate' => true,
]);
if (empty($contacts)) {
    warning('表示データが見つかりません。');
} else {
    $_view['contact'] = $contacts[0];
}

// コメントを取得
$_view['comments'] = model('select_comments', [
    'where' => [
        'comments.contact_id = :id',
        [
            'id' => $_GET['id'],
        ],
    ],
    'order_by' => 'comments.id',
], [
    'associate' => true,
]);

// タイトル
$_view['title'] = 'お問い合わせ表示';
