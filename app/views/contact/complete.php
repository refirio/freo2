<?php import('app/views/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-3 px-md-4">
                    <h2 class="h4 mb-3"><?php h($_view['title']) ?></h2>
                    <?php e($GLOBALS['setting']['text_contact_complete']) ?>
                    <ul>
                        <li><a href="<?php t(MAIN_FILE) ?>/">戻る</a></li>
                    </ul>
                    <?php e($_view['widget_sets']['public_page']) ?>
                </main>

<?php import('app/views/footer.php') ?>
