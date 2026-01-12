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
                    <?php e($GLOBALS['setting']['text_auth_contact']) ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-nowrap d-none d-md-table-cell">日時</th>
                                <th class="text-nowrap">件名</th>
                                <th class="text-nowrap">作業</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th class="text-nowrap d-none d-md-table-cell">日時</th>
                                <th class="text-nowrap">件名</th>
                                <th class="text-nowrap">作業</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?php foreach ($_view['contacts'] as $contact) : ?>
                            <tr>
                                <td class="d-none d-md-table-cell"><?php h(localdate('Ymd', $contact['created']) == localdate('Ymd') ? localdate('H:i:s', $contact['created']) : localdate('Y/m/d', $contact['created'])) ?></td>
                                <td><?php t(truncate($contact['subject'], 100)) ?></td>
                                <td><a href="<?php t(MAIN_FILE) ?>/auth/contact_view?id=<?php t($contact['id']) ?>" class="btn btn-primary text-nowrap">表示</a></td>
                            </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                    <?php if ($_view['contact_page'] > 1) : ?>
                        <ul class="pagination d-flex justify-content-end">
                            <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/auth/contact?page=1" class="page-link">&laquo;</a></li>
                            <?php for ($i = max(1, $_GET['page'] - floor($GLOBALS['setting']['number_width_contact'] / 2)); $i <= min($_view['contact_page'], $_GET['page'] + floor($GLOBALS['setting']['number_width_contact'] / 2)); $i++) : ?>
                            <li class="page-item<?php if ($i == $_GET['page']) : ?> active<?php endif ?>"><a href="<?php t(MAIN_FILE) ?>/admin/contact?page=<?php t($i) ?>" class="page-link"><?php t($i) ?></a></li>
                            <?php endfor ?>
                            <li class="page-item"><a href="<?php t(MAIN_FILE) ?>/admin/contact?page=<?php t(ceil($_view['contact_count'] / $GLOBALS['setting']['number_limit_contact'])) ?>" class="page-link">&raquo;</a></li>
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
