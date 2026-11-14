<?php

import('app/services/log.php');
import('app/services/entry.php');

/**
 * コメントの対象を取得
 *
 * エントリーへのコメントは型の名前（データの定義）と公開側のURL、お問い合わせのやりとりは管理画面の詳細のURL
 * 型がデータの定義に無いとき（無効にしたプラグインの型など）は、型のコードだけでURLは無し
 *
 * @param array $comment associate 付きで取得したコメント
 *
 * @return array|null name と url（url は MAIN_FILE を付けずに返す。対象が無ければ null）
 */
function service_comment_target($comment)
{
    if (!empty($comment['entry_id']) && $comment['type_code'] !== null) {
        $data = app_data_type($comment['type_code']);
        if ($data === null) {
            return [
                'name' => $comment['type_code'],
                'url'  => null,
            ];
        }

        return [
            'name' => $data['name'],
            'url'  => service_entry_url([
                'type_code' => $comment['type_code'],
                'code'      => $comment['entry_code'],
            ]),
        ];
    }

    if (!empty($comment['contact_id'])) {
        return [
            'name' => 'お問い合わせ',
            'url'  => '/admin/contact_view?id=' . intval($comment['contact_id']),
        ];
    }

    return null;
}

/**
 * コメントを受け付けるか
 *
 * @param array $entry
 *
 * @return bool
 */
function service_comment_acceptable($entry)
{
    if ($entry['comment'] === 'opened') {
        return true;
    }
    if ($entry['comment'] === 'user' && !empty($_SESSION['auth']['user']['id'])) {
        return true;
    }

    return false;
}

/**
 * 公開側の投稿データを整理
 *
 * コメント先は、送信された値ではなく表示しているエントリーにする
 *
 * @param array $data
 * @param array $entry
 *
 * @return array
 */
function service_comment_normalize($data, $entry)
{
    return model('normalize_comments', [
        'entry_id' => $entry['id'],
        'name'     => isset($data['name'])    ? $data['name']    : '',
        'url'      => isset($data['url'])     ? $data['url']     : '',
        'message'  => isset($data['message']) ? $data['message'] : '',
    ]);
}

/**
 * 公開側の入力フォームの初期値を取得
 *
 * ログインしていれば、お名前とURLにユーザーの情報を入れる
 *
 * @return array
 */
function service_comment_default()
{
    $comment = model('default_comments');

    if (!empty($_SESSION['auth']['user']['id'])) {
        $users = model('select_users', [
            'where' => [
                'id = :id AND enabled = 1',
                [
                    'id' => $_SESSION['auth']['user']['id'],
                ],
            ],
        ]);
        if (!empty($users)) {
            $comment['name'] = $users[0]['name'];
            $comment['url']  = $users[0]['url'];
        }
    }

    return $comment;
}

/**
 * エントリーの承認済みコメントを取得
 *
 * @param int $entry_id
 *
 * @return array
 */
function service_comment_select_approved($entry_id)
{
    return model('select_comments', [
        'where'    => [
            'comments.approved = 1 AND comments.entry_id = :entry_id',
            [
                'entry_id' => $entry_id,
            ],
        ],
        'order_by' => 'comments.id',
    ], [
        'associate' => true,
    ]);
}

/**
 * コメントの登録
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_comment_insert($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'comments', 'insert');

    // ユーザー
    if (!empty($_SESSION['auth']['user']['id'])) {
        $queries['values']['user_id'] = $_SESSION['auth']['user']['id'];
    }

    // コメントを登録
    $resource = model('insert_comments', $queries, $options);
    if (!$resource) {
        error('データを登録できません。');
    }

    return $resource;
}

/**
 * コメントの編集
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_comment_update($queries, $options = [])
{
    $options = [
        'id'     => isset($options['id'])     ? $options['id']     : null,
        'update' => isset($options['update']) ? $options['update'] : null,
    ];

    // 最終編集日時を確認
    if (isset($options['id']) && isset($options['update']) && (!isset($queries['set']['modified']) || $queries['set']['modified'] !== false)) {
        $comments = model('select_comments', [
            'where' => [
                'id = :id AND modified > :update',
                [
                    'id'     => $options['id'],
                    'update' => $options['update'],
                ],
            ],
        ]);
        if (!empty($comments)) {
            error('編集開始後にデータが更新されています。');
        }
    }

    // 操作ログの記録
    service_log_record(null, 'comments', 'update');

    // コメントを編集
    $resource = model('update_comments', $queries, $options);
    if (!$resource) {
        error('データを編集できません。');
    }

    return $resource;
}

/**
 * コメントの削除
 *
 * @param array $queries
 * @param array $options
 *
 * @return resource
 */
function service_comment_delete($queries, $options = [])
{
    // 操作ログの記録
    service_log_record(null, 'comments', 'delete');

    // コメントを削除
    $resource = model('delete_comments', $queries, $options);
    if (!$resource) {
        error('データを削除できません。');
    }

    return $resource;
}
