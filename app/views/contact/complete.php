<?php import('app/views/header.php') ?>

                    <div id="contact">
                        <h2 class="h4 mb-3"><?php h($_view['title']) ?></h2>
                        <?php e($GLOBALS['setting']['text_contact_complete']) ?>
                        <ul>
                            <li><a href="<?php t(MAIN_FILE) ?>/">戻る</a></li>
                        </ul>
                    </div>

                    <?php e($_view['widget_sets']['public_page']) ?>

<?php import('app/views/footer.php') ?>
