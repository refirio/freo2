<?php

/**
 * ダッシュボードのボックスを取得
 *
 * 本体のボックスのあとに、有効なプラグインの dashboard（ボックスの配列を返す関数の名前）のボックスを並べる
 * 段（group）ごとに、見られる権限の無い段は除く
 *
 * @param int $power ログインユーザーの権限の強さ
 *
 * @return array 段のコードをキーにした配列（name と boxes。ボックスの無い段は含めない）
 */
function service_dashboard_boxes($power)
{
    // 段と、見られる権限
    $groups = [
        'content'       => ['name' => 'コンテンツ',         'power' => 2],
        'communication' => ['name' => 'コミュニケーション', 'power' => 1],
        'system'        => ['name' => 'システム',           'power' => 3],
    ];

    // ボックスを返す関数
    $functions = [
        'service_dashboard_content',
        'service_dashboard_contact',
        'service_dashboard_user',
    ];
    if (isset($GLOBALS['plugin']) && is_array($GLOBALS['plugin'])) {
        foreach ($GLOBALS['plugin'] as $plugin) {
            if (!empty($plugin['dashboard']) && is_string($plugin['dashboard'])) {
                $functions[] = $plugin['dashboard'];
            }
        }
    }

    $results = [];
    foreach ($functions as $function) {
        if (!function_exists($function)) {
            continue;
        }
        $boxes = call_user_func($function);
        if (!is_array($boxes)) {
            continue;
        }

        foreach ($boxes as $box) {
            $box = service_dashboard_normalize($box);
            if ($box === null || !isset($groups[$box['group']]) || $power < $groups[$box['group']]['power']) {
                continue;
            }
            if (!isset($results[$box['group']])) {
                $results[$box['group']] = [
                    'name'  => $groups[$box['group']]['name'],
                    'boxes' => [],
                ];
            }
            $results[$box['group']]['boxes'][] = $box;
        }
    }

    // 段の並びをそろえる
    $sorted = [];
    foreach (array_keys($groups) as $group) {
        if (isset($results[$group])) {
            $sorted[$group] = $results[$group];
        }
    }

    return $sorted;
}

/**
 * ダッシュボードのボックスを整える
 *
 * @param array $box group（省略時は content）・name・count・list・label・other
 *
 * @return array|null 名前の無いボックスは null
 */
function service_dashboard_normalize($box)
{
    if (!is_array($box) || !isset($box['name']) || $box['name'] === '') {
        return null;
    }

    return [
        'group' => isset($box['group']) ? $box['group'] : 'content',
        'name'  => $box['name'],
        'count' => isset($box['count']) ? $box['count'] : 0,
        'list'  => isset($box['list']) ? $box['list'] : null,
        'label' => isset($box['label']) ? $box['label'] : null,
        'other' => isset($box['other']) ? $box['other'] : null,
    ];
}

/**
 * コンテンツのボックス（データの定義のうち、型を持つもの）
 *
 * 承認を使う型は未承認、使わない型は非公開の件数も出す
 *
 * @return array
 */
function service_dashboard_content()
{
    $boxes = [];
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

        $boxes[] = [
            'group' => 'content',
            'name'  => $data['name'],
            'count' => $count[0]['count'],
            'list'  => isset($data['list']) ? $data['list'] : null,
            'label' => $approve ? '未承認' : '非公開',
            'other' => $other[0]['count'],
        ];
    }

    return $boxes;
}

/**
 * お問い合わせのボックス
 *
 * @return array
 */
function service_dashboard_contact()
{
    $count = model('select_contacts', [
        'select' => 'COUNT(DISTINCT contacts.id) AS count',
    ], [
        'associate' => true,
    ]);
    $opened = model('select_contacts', [
        'select' => 'COUNT(DISTINCT contacts.id) AS count',
        'where'  => 'contacts.status = ' . db_escape('opened'),
    ], [
        'associate' => true,
    ]);

    return [
        [
            'group' => 'communication',
            'name'  => 'お問い合わせ',
            'count' => $count[0]['count'],
            'list'  => '/admin/contact',
            'label' => '未対応',
            'other' => $opened[0]['count'],
        ],
    ];
}

/**
 * ユーザーのボックス
 *
 * @return array
 */
function service_dashboard_user()
{
    $count = model('select_users', [
        'select' => 'COUNT(DISTINCT users.id) AS count',
    ], [
        'associate' => true,
    ]);
    $disabled = model('select_users', [
        'select' => 'COUNT(DISTINCT users.id) AS count',
        'where'  => 'users.enabled = 0',
    ], [
        'associate' => true,
    ]);

    return [
        [
            'group' => 'system',
            'name'  => 'ユーザー',
            'count' => $count[0]['count'],
            'list'  => '/admin/user',
            'label' => '無効',
            'other' => $disabled[0]['count'],
        ],
    ];
}
