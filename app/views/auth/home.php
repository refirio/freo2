<?php import('app/views/auth/header.php') ?>

        <main class="col-11 col-md-6 mx-auto my-4">
            <div class="mb-4 text-center">
                <h1 class="h3">
                    <?php h($GLOBALS['string']['heading_mypage']) ?>
                </h1>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header heading"><?php h($_view['title']) ?></div>
                <div class="card-body">
                    <?php if (isset($_GET['verify']) && $_GET['verify'] === 'send') : ?>
                    <div class="alert alert-success">
                        <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                        登録されたメールアドレスに、メールアドレスの存在確認が送信されました。
                    </div>
                    <?php endif ?>
                    <?php if (!$_view['_user']['email_verified']) : ?>
                    <div class="alert alert-warning">
                        <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                        <a href="<?php t(MAIN_FILE) ?>/auth/email_send">メールアドレスの存在を確認してください。</a>
                    </div>
                    <?php endif ?>
                    <?php e($GLOBALS['setting']['text_auth_home']) ?>
                    <p>ようこそ、<?php h($_view['_user']['name'] ? $_view['_user']['name'] : $_view['_user']['username']) ?>さん</p>
                    <ul>
                        <?php foreach ($GLOBALS['menu_contents']['auth']['home'] as $work_key => $work_value) : if ($work_value['show']) : ?>
                        <li><a href="<?php t(MAIN_FILE . $work_value['link']) ?>"><?php t($work_value['name']) ?></a></li>
                        <?php endif; endforeach ?>
                    </ul>
                    <?php e($_view['widget_sets']['auth_menu']) ?>
                </div>
            </div>
            <?php e($_view['widget_sets']['auth_home']) ?>
        </main>
        <div class="my-4 text-center">
            <a href="<?php t(MAIN_FILE) ?>/"><?php h($GLOBALS['string']['text_goto_top']) ?></a>
        </div>

<?php import('app/views/auth/footer.php') ?>
