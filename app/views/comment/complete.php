<?php import('app/views/header.php') ?>

            <div id="comment">
                <h2 class="h3 mb-3"><?php h($_view['title']) ?></h2>
                <?php e($GLOBALS['setting']['text_comment_complete']) ?>

                <?php if ($GLOBALS['setting']['comment_use_approve']) : ?>
                <div class="alert alert-warning">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                    コメントは管理者の承認後に表示されます。
                </div>
                <?php endif ?>

                <ul>
                    <li><a href="<?php t(MAIN_FILE) ?><?php t($_view['entry']['type_code'] === 'page' ? '/page/' : '/' . $_view['entry']['type_code'] . '/detail/') ?><?php t($_view['entry']['code']) ?>"><?php h($GLOBALS['string']['text_goto_entry']) ?></a></li>
                </ul>
            </div>

            <?php e($_view['widget_sets']['public_page']) ?>

<?php import('app/views/footer.php') ?>
