<?php

import('app/services/log.php');

/**
 * 設定の保存
 *
 * @param array $settings
 *
 * @return resource
 */
function service_setting_save($settings)
{
    // 操作ログの記録
    service_log_record(null, 'settings', 'update');

    // 設定を編集
    foreach ($settings as $id => $value) {
        $resource = model('update_settings', [
            'set'   => [
                'value' => $value,
            ],
            'where' => [
                'id = :id',
                [
                    'id' => $id,
                ],
            ],
        ]);
        if (!$resource) {
            error('データを編集できません。');
        }
    }

    return $resource;
}
