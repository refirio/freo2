<?php import('app/views/auth/header.php') ?>

    <main class="col-11 col-md-6 mx-auto my-4">
        <div class="mb-4 text-center">
            <h1 class="h3">
                <a href="<?php t(MAIN_FILE) ?>/auth/home"><?php h($GLOBALS['string']['heading_mypage']) ?></a>
            </h1>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header heading"><?php h($_view['title']) ?></div>
            <div class="card-body">
                <?php if (isset($_GET['ok'])) : ?>
                <div class="alert alert-success">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                    <?php if ($_GET['ok'] === 'post') : ?>
                    コメントを登録しました。
                    <?php elseif ($_GET['ok'] === 'delete') : ?>
                    コメントを削除しました。
                    <?php endif ?>
                </div>
                <?php elseif (isset($_GET['warning'])) : ?>
                <div class="alert alert-danger">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                    <?php if ($_GET['warning'] === 'delete') : ?>
                    削除対象が選択されていません。
                    <?php endif ?>
                </div>
                <?php endif ?>
                <?php e($GLOBALS['setting']['text_auth_comment']) ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-nowrap d-none d-md-table-cell">日時</th>
                            <th class="text-nowrap">コメント先</th>
                            <th class="text-nowrap">作業</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th class="text-nowrap d-none d-md-table-cell">日時</th>
                            <th class="text-nowrap">コメント先</th>
                            <th class="text-nowrap">作業</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php foreach ($_view['comments'] as $comment) : ?>
                        <tr>
                            <td class="d-none d-md-table-cell"><?php h(localdate('Ymd', $comment['created']) == localdate('Ymd') ? localdate('H:i:s', $comment['created']) : localdate('Y/m/d', $comment['created'])) ?></td>
                            <td><?php t(truncate($comment['entry_title'] ? $comment['entry_title'] : $comment['contact_subject'], 100)) ?></td>
                            <td>
                                <?php if ($comment['type_code'] === 'entry') : ?>
                                <a href="<?php t(MAIN_FILE) ?>/<?php t($comment['type_code']) ?>/detail/<?php t($comment['entry_code']) ?>" class="btn btn-primary btn-sm text-nowrap">表示</a>
                                <?php elseif ($comment['type_code'] === 'page') : ?>
                                <a href="<?php t(MAIN_FILE) ?>/<?php t($comment['type_code']) ?>/<?php t($comment['entry_code']) ?>" class="btn btn-primary btn-sm text-nowrap">表示</a>
                                <?php elseif ($comment['contact_id']) : ?>
                                <a href="<?php t(MAIN_FILE) ?>/auth/contact_view?id=<?php t($comment['contact_id']) ?>" class="btn btn-primary btn-sm text-nowrap">表示</a>
                                <?php endif ?>
                                <a href="<?php t(MAIN_FILE) ?>/auth/comment_form?id=<?php t($comment['id']) ?>" class="btn btn-primary btn-sm text-nowrap">編集</a>
                            </td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
                <?php if ($_view['comment_page'] > 1) : ?>
                    <ul class="pagination d-flex justify-content-end">
                        <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/auth/comment?page=1" class="page-link">&laquo;</a></li>
                        <?php for ($i = max(1, $_GET['page'] - floor($GLOBALS['setting']['number_width_comment'] / 2)); $i <= min($_view['comment_page'], $_GET['page'] + floor($GLOBALS['setting']['number_width_comment'] / 2)); $i++) : ?>
                        <li class="page-item<?php if ($i == $_GET['page']) : ?> active<?php endif ?>"><a href="<?php t(MAIN_FILE) ?>/admin/comment?page=<?php t($i) ?>" class="page-link"><?php t($i) ?></a></li>
                        <?php endfor ?>
                        <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/admin/comment?page=<?php t(ceil($_view['comment_count'] / $GLOBALS['setting']['number_limit_comment'])) ?>" class="page-link">&raquo;</a></li>
                    </ul>
                <?php endif ?>
            </div>
        </div>
        <?php e($_view['widget_sets']['auth_page']) ?>
    </main>
    <div class="my-4 text-center">
        <a href="<?php t(MAIN_FILE) ?>/auth/home"><?php h($GLOBALS['string']['text_goto_auth_home']) ?></a>
    </div>

<?php import('app/views/auth/footer.php') ?>
