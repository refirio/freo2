<?php import('app/views/header.php') ?>

            <div id="page-<?php h($_view['entry']['code']) ?>">
                <h2 class="h3 mb-3"><?php h($_view['entry']['title']) ?></h2>
                <?php e($GLOBALS['setting']['text_page_index']) ?>

                <?php if (!empty($_view['entry']['category_sets'])) : ?>
                <ul class="category">
                    <?php foreach ($_view['entry']['category_sets'] as $category_sets) : ?>
                    <li><?php h($category_sets['category_name']) ?></li>
                    <?php endforeach ?>
                </ul>
                <?php endif ?>

                <?php if (!empty($_view['entry']['pictures']) && !empty($_view['entry']['thumbnail'])) : ?>
                <div class="images">
                    <div class="image my-3"><a href="<?php t(MAIN_FILE) ?>/file/page/<?php t($_view['entry']['code']) ?>"><img src="<?php t($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['entry'] . $_view['entry']['id'] . '/' . $_view['entry']['thumbnail']) ?>" alt="" class="img-fluid"></a></div>
                </div>
                <?php elseif (!empty($_view['entry']['pictures']) || !empty($_view['entry']['thumbnail'])) : ?>
                <div class="images">
                    <?php if (!empty($_view['entry']['pictures'])) : ?>
                    <div class="image my-3">
                        <?php foreach ($_view['entry']['pictures'] as $picture) : ?>
                        <img src="<?php t($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['entry'] . $_view['entry']['id'] . '/' . $picture) ?>" alt="" class="img-fluid">
                        <?php endforeach ?>
                    </div>
                    <?php elseif (!empty($_view['entry']['thumbnail'])) : ?>
                    <div class="image my-3">
                        <img src="<?php t($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['entry'] . $_view['entry']['id'] . '/' . $_view['entry']['thumbnail']) ?>" alt="" class="img-fluid">
                    </div>
                    <?php endif ?>
                </div>
                <?php endif ?>

                <?php if (!empty($_view['entry']['text'])) : ?>
                <div class="text">
                    <?php e($_view['entry']['text']) ?>
                </div>
                <?php endif ?>

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

                <?php if ($_view['entry']['public'] === 'password' && empty($_SESSION['entry_passwords'][$_view['entry']['id']])) : ?>
                <form action="<?php t(MAIN_FILE) ?><?php t($_view['entry']['type_code'] === 'page' ? '/page/' : '/' . $_view['entry']['type_code'] . '/detail/') ?><?php t($_view['entry']['code']) ?>" method="post">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <input type="hidden" name="exec" value="password">
                    <div class="form-group mb-2">
                        <label>パスワード</label>
                        <input type="password" name="password" value="" class="form-control">
                    </div>
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary px-4">認証</button>
                    </div>
                </form>
                <?php endif ?>
            </div>

            <?php if (!empty($_view['comments'])) : ?>
            <div id="comment">
                <h3 class="h4 mt-4"><?php h($GLOBALS['string']['heading_comment_list']) ?></h3>

                <?php foreach ($_view['comments'] as $comment) : ?>
                <div class="comment">
                    <h4 class="h6 mt-4"><?php if ($comment['url']) : ?><a href="<?php t($comment['url']) ?>" target="_blank"><?php endif ?><?php h($comment['name']) ?><?php if ($comment['url']) : ?></a><?php endif ?>（<time datetime="<?php h(localdate('Y-m-d H:i:s', $comment['created'])) ?>"><?php h(localdate('Y/m/d H:i', $comment['created'])) ?></time>）</h4>
                    <p><?php h($comment['message']) ?></p>
                </div>
                <?php endforeach ?>
            </div>
            <?php endif ?>

            <?php if ($_view['entry']['comment'] === 'opened' || ($_view['entry']['comment'] === 'user' && !empty($_SESSION['auth']['user']['id']))) : ?>
            <div id="comment_form">
                <h3 class="h4 mt-4"><?php h($GLOBALS['string']['heading_comment_form']) ?></h3>
                <?php e($GLOBALS['setting']['text_comment_form']) ?>

                <?php if (isset($_view['warnings'])) : ?>
                <div class="alert alert-danger">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                    <?php foreach ($_view['warnings'] as $warning) : ?>
                    <?php h($warning) ?>
                    <?php endforeach ?>
                </div>
                <?php endif ?>

                <form action="<?php t(MAIN_FILE) ?><?php t($_view['entry']['type_code'] === 'page' ? '/page/' : '/' . $_view['entry']['type_code'] . '/detail/') ?><?php t($_view['entry']['code']) ?>" method="post">
                    <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                    <input type="hidden" name="exec" value="comment">
                    <input type="hidden" name="entry_id" value="<?php t($_view['entry']['id']) ?>">
                    <?php if (empty($_SESSION['auth']['user']['id'])) : ?>
                    <div class="form-group mb-2">
                        <label>お名前 <span class="badge bg-danger">必須</span></label>
                        <input type="text" name="name" value="<?php t($_view['comment']['name']) ?>" class="form-control">
                    </div>
                    <div class="form-group mb-2">
                        <label>URL</label>
                        <input type="text" name="url" value="<?php t($_view['comment']['url']) ?>" class="form-control">
                    </div>
                    <?php else : ?>
                    <input type="hidden" name="name" value="<?php t($_view['comment']['name']) ?>">
                    <input type="hidden" name="url" value="<?php t($_view['comment']['url']) ?>">
                    <div class="form-group mb-2">
                        <label>お名前</label>
                        <input type="text" value="<?php t($_view['comment']['name']) ?>" readonly class="form-control">
                    </div>
                    <div class="form-group mb-2">
                        <label>URL</label>
                        <input type="url" value="<?php t($_view['comment']['url']) ?>" readonly class="form-control">
                    </div>
                    <?php endif ?>
                    <div class="form-group mb-2">
                        <label>コメント内容 <span class="badge bg-danger">必須</span></label>
                        <textarea name="message" rows="10" cols="50" class="form-control"><?php t($_view['comment']['message']) ?></textarea>
                    </div>
                    <div class="form-group mt-4">
                        <?php if ($GLOBALS['config']['recaptcha_enable'] == true) : ?>
                        <?php recaptcha_input($GLOBALS['config']['recaptcha_site_key']) ?>
                        <?php endif ?>
                        <button type="submit" class="btn btn-primary px-4">投稿</button>
                    </div>
                </form>

                <?php if ($GLOBALS['config']['recaptcha_enable'] == true) : ?>
                <?php recaptcha_import($GLOBALS['config']['recaptcha_site_key']) ?>
                <?php endif ?>
            </div>
            <?php endif ?>

            <?php e($_view['widget_sets']['public_page']) ?>

<?php import('app/views/footer.php') ?>
