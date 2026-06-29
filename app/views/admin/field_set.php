    <?php foreach ($_view['fields'] as $field) : ?>
    <div class="form-group mb-2">
        <label class="fw-bold"><?php h($field['name']) ?><?php if (!is_null($field['explanation'])) : ?> <span class="badge text-light bg-secondary" data-toggle="tooltip" title="<?php t($field['explanation']) ?>">？</span><?php endif ?><?php if ($field['validation'] === 'required') : ?> <span class="badge bg-danger">必須</span><?php endif ?></label>
        <div id="validate_field_sets_<?php t($field['id']) ?>">
            <?php if ($field['kind'] === 'text' || $field['kind'] === 'number' || $field['kind'] === 'alphabet') : ?>
            <div class="field">
                <input type="text" name="field_sets[<?php t($field['id']) ?>]" size="30" value="<?php t($_view['entry']['id'] ? ($_view['entry']['field_sets'][$field['id']] ?? '') : $field['initial']) ?>" class="form-control">
            </div>
            <?php elseif ($field['kind'] === 'textarea' || $field['kind'] === 'html') : ?>
            <div class="field">
                <textarea name="field_sets[<?php t($field['id']) ?>]" rows="5" cols="50" class="form-control"><?php t($_view['entry']['id'] ? ($_view['entry']['field_sets'][$field['id']] ?? '') : $field['initial']) ?></textarea>
            </div>
            <?php elseif ($field['kind'] === 'wysiwyg') : ?>
            <div class="field">
                <textarea name="field_sets[<?php t($field['id']) ?>]" rows="5" cols="50" class="form-control editor"><?php t($_view['entry']['id'] ? ($_view['entry']['field_sets'][$field['id']] ?? '') : $field['initial']) ?></textarea>
            </div>
            <?php elseif ($field['kind'] === 'select') : ?>
            <div class="field">
                <select name="field_sets[<?php t($field['id']) ?>]" class="form-select" style="width: 200px;">
                    <option value=""></option>
                    <?php foreach (explode("\n", $field['choices']) as $value) : ?>
                    <option value="<?php t($value) ?>"<?php !$_view['entry']['id'] && $value == $field['initial'] ? e(' selected="selected"') : '' ?><?php isset($_view['entry']['field_sets'][$field['id']]) && $value == $_view['entry']['field_sets'][$field['id']] ? e(' selected="selected"') : '' ?>><?php t($value) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <?php elseif ($field['kind'] === 'radio') : ?>
            <?php foreach (explode("\n", $field['choices']) as $value) : ?>
            <div class="field">
                <label><input type="radio" name="field_sets[<?php t($field['id']) ?>]" value="<?php t($value) ?>" class="form-check-input"<?php !$_view['entry']['id'] && in_array($value, $field['initial'] ? explode("\n", $field['initial']) : []) ? e(' checked="checked"') : '' ?><?php (isset($_view['entry']['field_sets'][$field['id']]) && in_array($value, explode("\n", $_view['entry']['field_sets'][$field['id']]))) ? e(' checked="checked"') : '' ?>> <?php t($value) ?></label><br>
            </div>
            <?php endforeach ?>
            <?php elseif ($field['kind'] === 'checkbox') : ?>
            <div class="field">
                <?php foreach (explode("\n", $field['choices']) as $value) : ?>
                <label><input type="checkbox" name="field_sets[<?php t($field['id']) ?>][]" value="<?php t($value) ?>" class="form-check-input"<?php !$_view['entry']['id'] && in_array($value, $field['initial'] ? explode("\n", $field['initial']) : []) ? e(' checked="checked"') : '' ?><?php (isset($_view['entry']['field_sets'][$field['id']]) && in_array($value, explode("\n", $_view['entry']['field_sets'][$field['id']]))) ? e(' checked="checked"') : '' ?>> <?php t($value) ?></label><br>
                <?php endforeach ?>
            </div>
            <?php elseif ($field['kind'] === 'image' || $field['kind'] === 'file') : ?>

            <div class="field upload" id="field_<?php t($_view['entry']['id'] . '_' . $field['id']) ?>" data-upload="<?php t(MAIN_FILE) ?>/admin/file_upload?_type=json&amp;target=field&amp;key=field_<?php t($_view['entry']['id'] . '_' . $field['id']) ?>&amp;format=<?php t($field['kind']) ?>">
                <button type="button">ファイル選択</button>
                <input type="file" name="field_<?php t($_view['entry']['id'] . '_' . $field['id']) ?>">
                <p><img src="<?php t(MAIN_FILE) ?>/admin/file?_type=file&amp;target=field&amp;key=field_<?php t($_view['entry']['id'] . '_' . $field['id']) ?>&amp;format=<?php t($field['kind']) ?><?php $_view['entry']['id'] ? t('&id=' . $_view['entry']['id']) : '' ?>"></p>
                <ul>
                    <li><a href="<?php t(MAIN_FILE) ?>/admin/file_delete?target=field&amp;key=field_<?php t($_view['entry']['id'] . '_' . $field['id']) ?>&amp;format=<?php t($field['kind']) ?><?php $_view['entry']['id'] ? t('&id=' . $_view['entry']['id']) : '' ?>" id="field_<?php t($_view['entry']['id'] . '_' . $field['id']) ?>_delete" class="token" data-token="<?php t($_view['token']) ?>">削除</a></li>
                </ul>
            </div>
            <?php endif ?>
        </div>
    </div>
    <?php endforeach ?>
