<?php import('app/views/auth/header.php') ?>

        <main class="col-11 col-md-6 mx-auto my-4">
            <div class="mb-4 text-center">
                <h1 class="h3">
                    ユーザー情報
                </h1>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header heading"><?php h($_view['title']) ?></div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-nowrap d-none d-md-table-cell">日時</th>
                                <th class="text-nowrap">内容</th>
                                <th class="text-nowrap">作業</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th class="text-nowrap d-none d-md-table-cell">日時</th>
                                <th class="text-nowrap">内容</th>
                                <th class="text-nowrap">作業</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?php foreach ($_view['comments'] as $comment) : ?>
                            <tr>
                                <td class="d-none d-md-table-cell"><?php h(localdate('Ymd', $comment['created']) == localdate('Ymd') ? localdate('H:i:s', $comment['created']) : localdate('Y/m/d', $comment['created'])) ?></td>
                                <td><?php t(truncate($comment['message'], 100)) ?></td>
                                <td>
                                    <?php if ($comment['type_code'] === 'entry') : ?>
                                    <a href="<?php t(MAIN_FILE) ?>/<?php t($comment['type_code']) ?>/detail/<?php t($comment['entry_code']) ?>" class="btn btn-primary text-nowrap">表示</a>
                                    <?php elseif ($comment['type_code'] === 'page') : ?>
                                    <a href="<?php t(MAIN_FILE) ?>/<?php t($comment['type_code']) ?>/<?php t($comment['entry_code']) ?>" class="btn btn-primary text-nowrap">表示</a>
                                    <?php elseif ($comment['contact_id']) : ?>
                                    <a href="<?php t(MAIN_FILE) ?>/auth/contact_view?id=<?php t($comment['contact_id']) ?>" class="btn btn-primary text-nowrap">表示</a>
                                    <?php endif ?>
                                </td>
                            </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                    <?php if ($_view['comment_page'] > 1) : ?>
                        <ul class="pagination d-flex justify-content-end">
                            <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/auth/comment?page=1" class="page-link">&laquo;</a></li>
                            <?php for ($i = 1; $i <= $_view['comment_page']; $i++) : ?>
                            <li class="page-item<?php if ($i == $_GET['page']) : ?> active<?php endif ?>"><a href="<?php t(MAIN_FILE) ?>/admin/comment?page=<?php t($i) ?>" class="page-link"><?php t($i) ?></a></li>
                            <?php endfor ?>
                            <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/admin/comment?page=<?php t(ceil($_view['comment_count'] / $GLOBALS['config']['limit']['comment'])) ?>" class="page-link">&raquo;</a></li>
                        </ul>
                    <?php endif ?>
                </div>
            </div>
        </main>
        <div class="my-4 text-center">
            <?php if ($GLOBALS['authority']['power'] >= 1) : ?>
            <a href="<?php t(MAIN_FILE) ?>/admin/home">ホームに戻る</a>
            <?php else : ?>
            <a href="<?php t(MAIN_FILE) ?>/auth/home">ホームに戻る</a>
            <?php endif ?>
        </div>

<?php import('app/views/auth/footer.php') ?>
