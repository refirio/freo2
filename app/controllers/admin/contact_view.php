<?php

// お問い合わせを取得
$contacts = model('select_contacts', [
    'where' => [
        'contacts.id = :id',
        [
            'id' => $_GET['id'],
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

// タイトル
$_view['title'] = 'お問い合わせ表示';

