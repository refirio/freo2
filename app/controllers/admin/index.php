<?php

// エントリー数を取得
$entry_count = model('select_entries', [
    'select' => 'COUNT(DISTINCT entries.id) AS count',
    'where'  => 'types.code = ' . db_escape('entry'),
], [
    'associate' => true,
]);
$_view['entry_count'] = $entry_count[0]['count'];

// エントリー（未承認）数を取得
$entry_not_approved_count = model('select_entries', [
    'select' => 'COUNT(DISTINCT entries.id) AS count',
    'where'  => 'types.code = ' . db_escape('entry') . ' AND entries.approved = 0',
], [
    'associate' => true,
]);
$_view['entry_not_approved_count'] = $entry_not_approved_count[0]['count'];

// エントリー（非公開）数を取得
$entry_public_none_count = model('select_entries', [
    'select' => 'COUNT(DISTINCT entries.id) AS count',
    'where'  => 'types.code = ' . db_escape('entry') . ' AND entries.public = ' . db_escape('none'),
], [
    'associate' => true,
]);
$_view['entry_public_none_count'] = $entry_public_none_count[0]['count'];

// ページ数を取得
$page_count = model('select_entries', [
    'select' => 'COUNT(DISTINCT entries.id) AS count',
    'where'  => 'types.code = ' . db_escape('page'),
], [
    'associate' => true,
]);
$_view['page_count'] = $page_count[0]['count'];

// ページ数（未承認）を取得
$page_not_approved_count = model('select_entries', [
    'select' => 'COUNT(DISTINCT entries.id) AS count',
    'where'  => 'types.code = ' . db_escape('page') . ' AND entries.approved = 0',
], [
    'associate' => true,
]);
$_view['page_not_approved_count'] = $page_not_approved_count[0]['count'];

// ページ数（非公開）を取得
$page_public_none_count = model('select_entries', [
    'select' => 'COUNT(DISTINCT entries.id) AS count',
    'where'  => 'types.code = ' . db_escape('page') . ' AND entries.public = ' . db_escape('none'),
], [
    'associate' => true,
]);
$_view['page_public_none_count'] = $page_public_none_count[0]['count'];

// お問い合わせ数を取得
$contact_count = model('select_contacts', [
    'select' => 'COUNT(DISTINCT contacts.id) AS count',
], [
    'associate' => true,
]);
$_view['contact_count'] = $contact_count[0]['count'];

// お問い合わせ数（未対応）を取得
$contact_status_opened_count = model('select_contacts', [
    'select' => 'COUNT(DISTINCT contacts.id) AS count',
    'where'  => 'contacts.status = ' . db_escape('opened'),
], [
    'associate' => true,
]);
$_view['contact_status_opened_count'] = $contact_status_opened_count[0]['count'];

// ユーザー数を取得
$user_count = model('select_users', [
    'select' => 'COUNT(DISTINCT users.id) AS count',
], [
    'associate' => true,
]);
$_view['user_count'] = $user_count[0]['count'];

// ユーザー数（無効）を取得
$user_enabled_count = model('select_users', [
    'select' => 'COUNT(DISTINCT users.id) AS count',
    'where'  => 'users.enabled = 0',
], [
    'associate' => true,
]);
$_view['user_enabled_count'] = $user_enabled_count[0]['count'];
