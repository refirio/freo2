<?php

// エントリーのIDと型コードを取得
$entries = model('select_entries', [
    'select' => 'entries.id AS id, types.code AS type_code',
    'where'  => [
        'entries.id = :entry_id',
        [
            'entry_id' => $_GET['entry_id'],
        ],
    ],
], [
    'associate' => true,
]);
$entry = $entries[0];

// エントリーを取得
$entries = service_entry_select_published($entry['type_code'], [
    'where' => [
        'entries.id = :id',
        [
            'id' => $entry['id'],
        ],
    ],
]);
if (empty($entries)) {
    warning('エントリーが見つかりません。');
} else {
    $_view['entry'] = $entries[0];
}

// タイトル
$_view['title'] = $GLOBALS['string']['heading_comment_form'];
