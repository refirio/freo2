<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php isset($_view['title']) ? h($_view['title'] . ' | ') : '' ?>管理者用 | <?php h($GLOBALS['setting']['title']) ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <link href="<?php t($GLOBALS['config']['http_path']) ?><?php t(loader_css('login.css')) ?>" rel="stylesheet">
        <?php isset($_view['link']) ? e($_view['link']) : '' ?>
    </head>
    <body>
        <main class="form-login text-center">
            <form action="<?php t(MAIN_FILE) ?>/admin/<?php empty($_GET['referer']) ? '' : t('?referer=' . rawurlencode($_GET['referer'])) ?>" method="post">
                <h1 class="mb-4"><?php h($GLOBALS['setting']['title']) ?></h1>

                <?php if (isset($_view['warnings'])) : ?>
                <div class="alert alert-danger">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                    <?php foreach ($_view['warnings'] as $warning) : ?>
                    <?php h($warning) ?>
                    <?php endforeach ?>
                </div>
                <?php endif ?>

                <div class="form-floating">
                    <input type="text" name="username" size="30" value="<?php t($_view['user']['username']) ?>" class="form-control" id="username" placeholder="ユーザ名">
                    <label for="loginUsername">ユーザ名</label>
                </div>
                <div class="form-floating">
                    <input type="password" name="password" size="30" value="<?php t($_view['user']['password']) ?>" class="form-control" id="password" placeholder="パスワード">
                    <label for="loginPassword">パスワード</label>
                </div>
                <div class="form-group mt-2">
                    <label><input type="checkbox" name="session" value="keep" class="form-check-input"<?php isset($_view['user']['session']) ? e('checked="checked"') : '' ?>> ログイン状態を記憶する</label>
                </div>
                <div class="form-group mt-2">
                    <button class="w-100 btn btn-lg btn-primary" type="submit">ログイン</button>
                </div>
                <div class="form-group mt-4">
                    <a href="<?php t(MAIN_FILE) ?>/">ユーザ側ページ</a>
                </div>
            </form>
        </main>

        <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
            <symbol id="symbol-exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
            </symbol>
        </svg>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        <?php isset($_view['script']) ? e($_view['script']) : '' ?>
    </body>
</html>
