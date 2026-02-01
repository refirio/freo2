                <?php if (!empty($_view['entry']['field_sets'])) : ?>
                <table class="table table-bordered">
                    <?php foreach ($_view['fields'] as $field) : if (isset($_view['entry']['field_sets'][$field['id']])) : ?>
                    <tr>
                        <th><?php h($field['name']) ?></th>
                        <td>
                            <?php if ($field['kind'] === 'text' || $field['kind'] === 'number' || $field['kind'] === 'alphabet' || $field['kind'] === 'textarea' || $field['kind'] === 'select' || $field['kind'] === 'radio' || $field['kind'] === 'checkbox') : ?>
                            <?php h($_view['entry']['field_sets'][$field['id']]) ?>
                            <?php elseif ($field['kind'] === 'html' || $field['kind'] === 'wysiwyg') : ?>
                            <?php e($_view['entry']['field_sets'][$field['id']]) ?>
                            <?php elseif ($field['kind'] === 'image' || $field['kind'] === 'file') : ?>
                            <a href="<?php t($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['field'] . $_view['entry']['id'] . '_' . $field['id'] . '/' . $_view['entry']['field_sets'][$field['id']]) ?>"><?php h($_view['entry']['field_sets'][$field['id']]) ?></a>
                            <?php endif ?>
                        </td>
                    </tr>
                    <?php endif; endforeach ?>
                </table>
                <?php endif ?>
