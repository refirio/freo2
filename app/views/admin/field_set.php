                                        <?php foreach ($_view['fields'] as $field) : ?>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold"><?php h($field['name']) ?><?php if ($field['validation'] === 'required') : ?> <span class="badge bg-danger">必須</span><?php endif ?></label>
                                            <div id="validate_field_sets_<?php t($field['id']) ?>">
                                                <?php if ($field['type'] === 'text' || $field['type'] === 'number' || $field['type'] === 'alphabet') : ?>
                                                <div class="field">
                                                    <input type="text" name="field_sets[<?php t($field['id']) ?>]" size="30" value="<?php t($_view[$field['target']]['field_sets'][$field['id']] ?? '') ?>" class="form-control">
                                                </div>
                                                <?php elseif ($field['type'] === 'textarea') : ?>
                                                <div class="field">
                                                    <textarea name="field_sets[<?php t($field['id']) ?>]" rows="5" cols="50" class="form-control"><?php t($_view[$field['target']]['field_sets'][$field['id']] ?? '') ?></textarea>
                                                </div>
                                                <?php elseif ($field['type'] === 'wysiwyg') : ?>
                                                <div class="field">
                                                    <textarea name="field_sets[<?php t($field['id']) ?>]" rows="5" cols="50" class="form-control editor"><?php t($_view[$field['target']]['field_sets'][$field['id']] ?? '') ?></textarea>
                                                </div>
                                                <?php elseif ($field['type'] === 'select') : ?>
                                                <div class="field">
                                                    <select name="field_sets[<?php t($field['id']) ?>]" class="form-select" style="width: 200px;">
                                                        <option value=""></option>
                                                        <?php foreach (explode("\n", $field['text']) as $value) : ?>
                                                        <option value="<?php t($value) ?>"<?php isset($_view[$field['target']]['field_sets'][$field['id']]) && $value == $_view[$field['target']]['field_sets'][$field['id']] ? e(' selected="selected"') : '' ?>><?php t($value) ?></option>
                                                        <?php endforeach ?>
                                                    </select>
                                                </div>
                                                <?php elseif ($field['type'] === 'radio') : ?>
                                                <?php foreach (explode("\n", $field['text']) as $value) : ?>
                                                <div class="field">
                                                    <label><input type="radio" name="field_sets[<?php t($field['id']) ?>]" value="<?php t($value) ?>" class="form-check-input"<?php (isset($_view[$field['target']]['field_sets'][$field['id']]) && in_array($value, explode("\n", $_view[$field['target']]['field_sets'][$field['id']]))) ? e(' checked="checked"') : '' ?>> <?php t($value) ?></label><br>
                                                </div>
                                                <?php endforeach ?>
                                                <?php elseif ($field['type'] === 'checkbox') : ?>
                                                <div class="field">
                                                    <?php foreach (explode("\n", $field['text']) as $value) : ?>
                                                    <label><input type="checkbox" name="field_sets[<?php t($field['id']) ?>][]" value="<?php t($value) ?>" class="form-check-input"<?php (isset($_view[$field['target']]['field_sets'][$field['id']]) && in_array($value, explode("\n", $_view[$field['target']]['field_sets'][$field['id']]))) ? e(' checked="checked"') : '' ?>> <?php t($value) ?></label><br>
                                                    <?php endforeach ?>
                                                </div>
                                                <?php endif ?>
                                            </div>
                                        </div>
                                        <?php endforeach ?>
