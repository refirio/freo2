<?php import('app/views/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-3 px-md-4">
                    <h2 class="h4 mb-3"><?php h($_view['title']) ?></h2>
                    <p>コメントを投稿しました。<?php if ($GLOBALS['setting']['comment_use_approve']) : ?><br>コメントは管理者の承認後に表示されます。<?php endif ?></p>
                    <ul>
                        <li><a href="<?php t(MAIN_FILE) ?><?php t($_view['entry']['type_code'] === 'entry' ? '/entry/detail/' : '/page/') ?><?php t($_view['entry']['code']) ?>">戻る</a></li>
                    </ul>
                </main>

<?php import('app/views/footer.php') ?>
