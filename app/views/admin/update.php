<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="<?php t(MAIN_CHARSET) ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <title><?php h($_view['title']) ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    </head>
    <body>
        <main class="container py-4" style="max-width: 720px;">
            <h1 class="h3 mb-4"><?php h($_view['title']) ?></h1>

            <table class="table table-sm mb-4">
                <tr>
                    <th class="w-25">バージョン</th>
                    <td><?php h(APP_VERSION_NUMBER) ?>（<?php h(APP_VERSION_UPDATE) ?>）</td>
                </tr>
            </table>

            <?php if (empty($_view['update_targets'])) : ?>
            <div class="alert alert-success">データベースの更新は必要ありません。</div>
            <p><a href="<?php t(MAIN_FILE) ?>/admin/" class="btn btn-secondary">管理画面へ</a></p>
            <?php else : ?>
            <div class="alert alert-warning">
                プログラムの更新に合わせて、データベースの更新が必要です。更新が終わるまで、サイトはメンテナンス中と表示されます。
            </div>
            <h2 class="h5">実行する更新</h2>
            <ul>
                <?php foreach ($_view['update_targets'] as $update_target) : ?>
                <li><code><?php h($update_target) ?></code></li>
                <?php endforeach ?>
            </ul>
            <?php endif ?>
        </main>
    </body>
</html>
