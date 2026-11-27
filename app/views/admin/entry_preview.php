<?php $data_type = app_data_type($_view['type']['code']) ?>
                <h3>確認</h3>
                <?php if (!empty($_view['preview']['warnings'])) : ?>
                <div class="alert alert-danger">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                    <?php foreach ($_view['preview']['warnings'] as $warning) : ?>
                    <?php h($warning) ?>
                    <?php endforeach ?>
                </div>
                <?php endif ?>
                <dl>
                    <?php if (!empty($_view['preview']['categories'])) : ?>
                    <dt>カテゴリー</dt>
                        <dd><?php h(implode('、', $_view['preview']['categories'])) ?></dd>
                    <?php endif ?>
                    <dt>公開</dt>
                        <dd><?php h($GLOBALS['config']['option']['entry']['public'][$_view['entry']['public']] ?? $_view['entry']['public']) ?></dd>
                    <?php if ($_view['entry']['public'] !== 'none' && !empty($_view['entry']['public_begin'])) : ?>
                    <dt>公開開始日時</dt>
                        <dd><?php h($_view['entry']['public_begin']) ?></dd>
                    <?php endif ?>
                    <?php if ($_view['entry']['public'] !== 'none' && !empty($_view['entry']['public_end'])) : ?>
                    <dt>公開終了日時</dt>
                        <dd><?php h($_view['entry']['public_end']) ?></dd>
                    <?php endif ?>
                    <?php if ($_view['entry']['public'] === 'password' && !empty($_view['entry']['password'])) : ?>
                    <dt>パスワード</dt>
                        <dd><?php h($_view['entry']['password']) ?></dd>
                    <?php endif ?>
                    <?php if (!empty($_view['preview']['attributes'])) : ?>
                    <dt>属性</dt>
                        <dd><?php h(implode('、', $_view['preview']['attributes'])) ?></dd>
                    <?php endif ?>
                    <dt>日時</dt>
                        <dd><?php h($_view['entry']['datetime']) ?></dd>
                    <dt>コード</dt>
                        <dd>
                            <?php h($_view['entry']['code']) ?>
                            <?php if ($_view['entry']['code'] !== '') : ?>
                            <br><small class="text-muted">URL: <?php h($GLOBALS['config']['http_url'] . $_view['preview']['url']) ?></small>
                            <?php endif ?>
                        </dd>
                    <dt>タイトル</dt>
                        <dd><?php h($_view['entry']['title']) ?></dd>
                    <?php if ($_view['entry']['text_type'] !== 'none') : ?>
                    <dt>本文</dt>
                        <dd><?php if ($_view['entry']['text_type'] === 'textarea') : ?><?php h($_view['entry']['text']) ?><?php else : ?><?php e($_view['entry']['text']) ?><?php endif ?></dd>
                    <?php endif ?>
                    <?php if ($_view['entry']['text_type'] !== app_data_setting($data_type, 'text_type')) : ?>
                    <dt>本文形式</dt>
                        <dd><?php h($GLOBALS['config']['option']['entry']['text_type'][$_view['entry']['text_type']] ?? $_view['entry']['text_type']) ?></dd>
                    <?php endif ?>
                    <?php if (app_data_setting($data_type, 'use_pictures') && !empty($_view['preview']['pictures'])) : ?>
                    <dt>画像</dt>
                        <dd>
                            <?php foreach ($_view['preview']['pictures'] as $picture) : ?>
                            <img src="<?php t($picture) ?>" class="img-thumbnail me-1 mb-1" style="max-width: 200px; max-height: 200px;">
                            <?php endforeach ?>
                        </dd>
                    <?php endif ?>
                    <?php if (app_data_setting($data_type, 'use_thumbnail') && $_view['preview']['thumbnail'] !== null) : ?>
                    <dt>サムネイル</dt>
                        <dd><img src="<?php t($_view['preview']['thumbnail']) ?>" class="img-thumbnail" style="max-width: 200px; max-height: 200px;"></dd>
                    <?php endif ?>
                    <?php foreach ($_view['fields'] as $field) : ?>
                    <?php if ($field['kind'] === 'image' || $field['kind'] === 'file') : ?>
                    <?php if (isset($_view['preview']['files'][$field['id']])) : ?>
                    <dt><?php h($field['name']) ?></dt>
                        <dd>
                            <?php if ($_view['preview']['files'][$field['id']]['url'] !== null) : ?>
                            <img src="<?php t($_view['preview']['files'][$field['id']]['url']) ?>" class="img-thumbnail" style="max-width: 200px; max-height: 200px;"><br>
                            <?php endif ?>
                            <?php h($_view['preview']['files'][$field['id']]['name']) ?>
                        </dd>
                    <?php endif ?>
                    <?php elseif (isset($_view['entry']['field_sets'][$field['id']]) && $_view['entry']['field_sets'][$field['id']] !== '') : ?>
                    <dt><?php h($field['name']) ?></dt>
                        <dd><?php if ($field['kind'] === 'html' || $field['kind'] === 'wysiwyg') : ?><?php e($_view['entry']['field_sets'][$field['id']]) ?><?php elseif ($field['kind'] === 'checkbox') : ?><?php h(implode('、', explode("\n", $_view['entry']['field_sets'][$field['id']]))) ?><?php else : ?><?php h($_view['entry']['field_sets'][$field['id']]) ?><?php endif ?></dd>
                    <?php endif ?>
                    <?php endforeach ?>
                    <dt>コメントの受付</dt>
                        <dd><?php h($GLOBALS['config']['option']['entry']['comment'][$_view['entry']['comment']] ?? $_view['entry']['comment']) ?></dd>
                </dl>
                <p><a href="#" class="close">閉じる</a></p>
