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
            <h1 class="h3 mb-2"><?php h($_view['title']) ?></h1>
            <p class="text-muted mb-4">新しいバージョンのファイルをアップロードした後に、データベースの更新と不要なファイルの削除を行います。</p>

            <table class="table table-sm mb-4">
                <tr>
                    <th class="w-25">バージョン</th>
                    <td><?php h(APP_VERSION_NUMBER) ?>（<?php h(localdate('Y/m/d', APP_VERSION_UPDATE)) ?>）</td>
                </tr>
            </table>

            <?php if (isset($_GET['ok']) && $_GET['ok'] === 'migrate') : ?>
            <div class="alert alert-success">データベースを更新しました。</div>
            <?php endif ?>

            <?php if (!empty($_view['update_results'])) : ?>
            <div class="alert alert-danger">
                データベースの更新に失敗しました。サイトはメンテナンス中のままです。<br>
                下のエラーの内容を控えてから、更新の前に取ったバックアップでデータベースを元に戻してください。
            </div>
            <h2 class="h5">更新の結果</h2>
            <ul class="mb-4">
                <?php foreach ($_view['update_results'] as $update_result) : ?>
                <li>
                    <code><?php h($update_result['target']) ?></code>
                    <?php if ($update_result['error'] === null) : ?>
                    <span class="badge bg-success">成功</span>
                    <?php else : ?>
                    <span class="badge bg-danger">失敗</span>
                    <?php $update_migration = $update_result['migrations'][count($update_result['migrations']) - 1] ?>
                    <pre class="bg-light border p-2 mt-2 mb-1" style="white-space: pre-wrap;"><?php h($update_migration['sql']) ?></pre>
                    <pre class="text-danger mb-0" style="white-space: pre-wrap;"><?php h($update_result['error']) ?></pre>
                    <?php endif ?>
                </li>
                <?php endforeach ?>
            </ul>
            <?php endif ?>

            <?php if (isset($_GET['ok']) && $_GET['ok'] === 'remove') : ?>
            <div class="alert alert-success">不要なファイルを削除しました。</div>
            <?php endif ?>

            <?php if (empty($_view['update_targets'])) : ?>
            <div class="alert alert-secondary">データベースの更新は必要ありません。</div>

            <?php if (!empty($_view['update_remove_results']['failed'])) : ?>
            <div class="alert alert-danger">
                次のファイルは、書き込みの権限が無いなどの理由で削除できませんでした。FTP などで削除してください。
            </div>
            <ul class="mb-4">
                <?php foreach ($_view['update_remove_results']['failed'] as $update_removed) : ?>
                <li><code><?php h($update_removed['path']) ?></code></li>
                <?php endforeach ?>
            </ul>
            <?php endif ?>

            <?php if (!empty($_view['update_removed']['delete'])) : ?>
            <h2 class="h5">不要なファイル</h2>
            <p>以前のバージョンにあり、今のバージョンでは使わなくなったファイルです。内容が以前のバージョンのものと一致するので、削除して問題ありません。</p>
            <ul class="mb-4">
                <?php foreach ($_view['update_removed']['delete'] as $update_removed) : ?>
                <li><code><?php h($update_removed['path']) ?></code></li>
                <?php endforeach ?>
            </ul>
            <form action="<?php t(MAIN_FILE) ?>/admin/update" method="post" id="update_remove" class="mb-4">
                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>">
                <input type="hidden" name="exec" value="remove">
                <button type="submit" class="btn btn-primary">不要なファイルを削除する</button>
            </form>
            <script>
                document.getElementById('update_remove').addEventListener('submit', function () {
                    var button = this.querySelector('button[type=submit]');

                    button.disabled    = true;
                    button.textContent = '削除しています…';
                });
            </script>
            <?php endif ?>

            <?php if (!empty($_view['update_removed']['keep'])) : ?>
            <h2 class="h5">確認が必要なファイル</h2>
            <p>以前のバージョンにあったファイルと同じ名前ですが、内容が違うため削除していません。編集したファイルや、自分で作ったファイルの可能性があります。不要であれば、FTP などで削除してください。</p>
            <ul class="mb-4">
                <?php foreach ($_view['update_removed']['keep'] as $update_removed) : ?>
                <li><code><?php h($update_removed['path']) ?></code></li>
                <?php endforeach ?>
            </ul>
            <?php endif ?>

            <p><a href="<?php t(MAIN_FILE) ?>/admin/" class="btn btn-secondary">管理画面へ</a></p>
            <?php else : ?>
            <?php if (empty($_view['update_results'])) : ?>
            <div class="alert alert-warning">
                プログラムの更新に合わせて、データベースの更新が必要です。更新が終わるまで、サイトはメンテナンス中と表示されます。
            </div>
            <?php endif ?>
            <h2 class="h5">実行する更新</h2>
            <ul class="mb-4">
                <?php foreach ($_view['update_targets'] as $update_target) : ?>
                <li><code><?php h($update_target) ?></code></li>
                <?php endforeach ?>
            </ul>
            <?php if (empty($_view['update_results'])) : ?>
            <h2 class="h5">データベースのバックアップ</h2>
            <p>更新に失敗したときに、元に戻すためのファイルです。データベースの内容をすべて含むので、大切に保管してください。データが多くてダウンロードできないときは、サーバーのデータベース管理ツール（phpMyAdmin など）でバックアップを取ってください。</p>
            <form action="<?php t(MAIN_FILE) ?>/admin/update" method="post" class="mb-4">
                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>">
                <input type="hidden" name="exec" value="backup">
                <button type="submit" class="btn btn-outline-secondary">バックアップをダウンロード</button>
            </form>
            <div class="alert alert-info">
                実行する前に、次の2つを確認してください。
                <ul class="mb-0">
                    <li>データベースのバックアップを取ってあること（失敗したときに、元に戻すために使います）</li>
                    <li>新しいプログラムのアップロードがすべて終わっていること</li>
                </ul>
            </div>
            <form action="<?php t(MAIN_FILE) ?>/admin/update" method="post" id="update_migrate">
                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>">
                <input type="hidden" name="exec" value="migrate">
                <button type="submit" class="btn btn-primary">データベースを更新する</button>
            </form>
            <script>
                document.getElementById('update_migrate').addEventListener('submit', function () {
                    var button = this.querySelector('button[type=submit]');

                    button.disabled    = true;
                    button.textContent = '更新しています…';
                });
            </script>
            <?php endif ?>
            <?php endif ?>
        </main>
    </body>
</html>
