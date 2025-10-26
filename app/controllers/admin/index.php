<?php

// エントリー数を取得
$entry_count = model('select_entries', [
    'select' => 'COUNT(DISTINCT entries.id) AS count',
    'where'  => 'types.code = ' . db_escape('entry'),
], [
    'associate' => true,
]);
$_view['entry_count'] = $entry_count[0]['count'];

// ページ数を取得
$page_count = model('select_entries', [
    'select' => 'COUNT(DISTINCT entries.id) AS count',
    'where'  => 'types.code = ' . db_escape('page'),
], [
    'associate' => true,
]);
$_view['page_count'] = $page_count[0]['count'];

// お問い合わせ数を取得
$contact_count = model('select_contacts', [
    'select' => 'COUNT(DISTINCT contacts.id) AS count',
], [
    'associate' => true,
]);
$_view['contact_count'] = $contact_count[0]['count'];
