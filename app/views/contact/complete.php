<?php import('app/views/header.php') ?>

            <div id="contact">
                <h2 class="h3 mb-3"><?php h($_view['title']) ?></h2>
                <?php e($GLOBALS['setting']['text_contact_complete']) ?>
                <ul>
                    <li><a href="<?php t(MAIN_FILE) ?>/"><?php h($GLOBALS['string']['text_goto_home']) ?></a></li>
                </ul>
            </div>

            <?php e($_view['widget_sets']['public_page']) ?>

<?php import('app/views/footer.php') ?>
