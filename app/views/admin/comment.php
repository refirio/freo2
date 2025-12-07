<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-list-ul"/></svg>
                            コミュニケーション
                        </h2>
                        <nav style="--bs-breadcrumb-divider: '>';">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/">ホーム</a></li>
                                <li class="breadcrumb-item active"><?php h($_view['title']) ?></li>
                            </ol>
                        </nav>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                            <?php if (isset($_GET['ok'])) : ?>
                            <div class="alert alert-success">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['ok'] === 'post') : ?>
                                コメントを登録しました。
                                <?php elseif ($_GET['ok'] === 'approve') : ?>
                                コメントの承認を変更しました。
                                <?php elseif ($_GET['ok'] === 'delete') : ?>
                                コメントを削除しました。
                                <?php endif ?>
                            </div>
                            <?php elseif (isset($_GET['warning'])) : ?>
                            <div class="alert alert-danger">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['warning'] === 'approve') : ?>
                                承認対象が選択されていません。
                                <?php elseif ($_GET['warning'] === 'delete') : ?>
                                削除対象が選択されていません。
                                <?php endif ?>
                            </div>
                            <?php endif ?>

                            <form action="<?php t(MAIN_FILE) ?>/admin/comment_bulk" method="post" class="bulk">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="page" value="<?php t($_GET['page']) ?>">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <?php if ($GLOBALS['authority']['power'] >= 2) : ?>
                                            <th class="text-nowrap"><label><input type="checkbox" name="" value="" class="bulks"></label></th>
                                            <?php endif ?>
                                            <th class="text-nowrap d-none d-md-table-cell">日時</th>
                                            <th class="text-nowrap">ユーザー名</th>
                                            <th class="text-nowrap">名前</th>
                                            <th class="text-nowrap">対象</th>
                                            <?php if ($GLOBALS['setting']['comment_use_approve']) : ?>
                                            <th class="text-nowrap">承認</th>
                                            <?php endif ?>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <?php if ($GLOBALS['authority']['power'] >= 2) : ?>
                                            <th class="text-nowrap"><label><input type="checkbox" name="" value="" class="bulks"></label></th>
                                            <?php endif ?>
                                            <th class="text-nowrap d-none d-md-table-cell">日時</th>
                                            <th class="text-nowrap">ユーザー名</th>
                                            <th class="text-nowrap">名前</th>
                                            <th class="text-nowrap">対象</th>
                                            <?php if ($GLOBALS['setting']['comment_use_approve']) : ?>
                                            <th class="text-nowrap">承認</th>
                                            <?php endif ?>
                                            <th class="text-nowrap">作業</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <?php foreach ($_view['comments'] as $comment) : ?>
                                        <tr>
                                            <?php if ($GLOBALS['authority']['power'] >= 2) : ?>
                                            <td><input type="checkbox" name="bulks[]" value="<?php h($comment['id']) ?>"<?php isset($_SESSION['bulk']['comment'][$comment['id']]) ? e('checked="checked"') : '' ?> class="bulk"></td>
                                            <?php endif ?>
                                            <td class="d-none d-md-table-cell"><?php h(localdate('Ymd', $comment['created']) == localdate('Ymd') ? localdate('H:i:s', $comment['created']) : localdate('Y/m/d', $comment['created'])) ?></td>
                                            <td><?php if (!preg_match('/^DELETED /', $comment['user_username'])) :?><code class="text-dark"><?php h($comment['user_id'] ? truncate($comment['user_username'], 50) : '') ?></code><?php endif ?></td>
                                            <td><?php h(truncate($comment['name'], 50)) ?></td>
                                            <td>
                                                <?php if ($comment['type_code'] === 'entry') : ?>
                                                <a href="<?php t(MAIN_FILE) ?>/<?php t($comment['type_code']) ?>/detail/<?php t($comment['entry_code']) ?>">エントリー</a>
                                                <?php elseif ($comment['type_code'] === 'page') : ?>
                                                <a href="<?php t(MAIN_FILE) ?>/<?php t($comment['type_code']) ?>/<?php t($comment['entry_code']) ?>">ページ</a>
                                                <?php elseif ($comment['contact_id']) : ?>
                                                <a href="<?php t(MAIN_FILE) ?>/admin/contact_view?id=<?php t($comment['contact_id']) ?>">お問い合わせ</a>
                                                <?php endif ?>
                                            </td>
                                            <?php if ($GLOBALS['setting']['comment_use_approve']) : ?>
                                            <td><span class="badge <?php t(app_badge('approved', $comment['approved'])) ?>"><?php h($GLOBALS['config']['option']['comment']['approved'][$comment['approved']]) ?></span></td>
                                            <?php endif ?>
                                            <td>
                                                <a href="<?php t(MAIN_FILE) ?>/admin/comment_view?id=<?php t($comment['id']) ?>" class="btn btn-primary text-nowrap">表示</a>
                                                <?php if ($GLOBALS['authority']['power'] >= 2) : ?>
                                                <a href="<?php t(MAIN_FILE) ?>/admin/comment_form?id=<?php t($comment['id']) ?>" class="btn btn-primary text-nowrap">編集</a>
                                                <?php endif ?>
                                            </td>
                                        </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                                <?php if ($GLOBALS['authority']['power'] >= 2) : ?>
                                <p><input type="submit" value="一括削除" class="btn btn-danger"></p>
                                <?php endif ?>
                                <?php if ($_view['comment_page'] > 1) : ?>
                                    <ul class="pagination d-flex justify-content-end">
                                        <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/admin/comment?page=1" class="page-link">&laquo;</a></li>
                                        <?php for ($i = max(1, $_GET['page'] - floor($GLOBALS['config']['pager']['admin_comment'] / 2)); $i <= min($_view['comment_page'], $_GET['page'] + floor($GLOBALS['config']['pager']['admin_comment'] / 2)); $i++) : ?>
                                        <li class="page-item<?php if ($i == $_GET['page']) : ?> active<?php endif ?>"><a href="<?php t(MAIN_FILE) ?>/admin/comment?page=<?php t($i) ?>" class="page-link"><?php t($i) ?></a></li>
                                        <?php endfor ?>
                                        <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/admin/comment?page=<?php t(ceil($_view['comment_count'] / $GLOBALS['config']['limit']['admin_comment'])) ?>" class="page-link">&raquo;</a></li>
                                    </ul>
                                <?php endif ?>
                            </form>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
