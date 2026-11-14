<?php

// コンテンツの件数を取得（データの定義のうち、型を持つもの。承認を使う型は未承認、使わない型は非公開の件数も出す）
$_view['contents'] = [];
foreach (app_data() as $data) {
    if (empty($data['type'])) {
        continue;
    }

    $count = model('select_entries', [
        'select' => 'COUNT(DISTINCT entries.id) AS count',
        'where'  => [
            'types.code = :type',
            [
                'type' => $data['type'],
            ],
        ],
    ], [
        'associate' => true,
    ]);

    $approve = (bool)app_data_setting($data, 'use_approve', false);
    $other   = model('select_entries', [
        'select' => 'COUNT(DISTINCT entries.id) AS count',
        'where'  => [
            'types.code = :type AND ' . ($approve ? 'entries.approved = 0' : 'entries.public = ' . db_escape('none')),
            [
                'type' => $data['type'],
            ],
        ],
    ], [
        'associate' => true,
    ]);

    $_view['contents'][] = [
        'name'    => $data['name'],
        'list'    => isset($data['list']) ? $data['list'] : null,
        'count'   => $count[0]['count'],
        'approve' => $approve,
        'other'   => $other[0]['count'],
    ];
}

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
