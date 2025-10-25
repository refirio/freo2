<?php import('app/views/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-3 px-md-4">
                    <h2 class="h4 mb-3">Entry</h2>
                    <ul class="headline">
                        <?php foreach ($_view['entries'] as $entry) : ?>
                        <li>
                            <time datetime="<?php h(localdate('Y-m-d', $entry['datetime'])) ?>" class="datetime"><?php h(localdate('Y.m.d', $entry['datetime'])) ?></time>
                            <a href="<?php t(MAIN_FILE) ?>/entry/detail/<?php h($entry['code']) ?>" class="px-2"><?php h($entry['title']) ?></a>
                            <span class="text"><?php h(truncate(strip_tags($entry['text'] ?? ''), 100)) ?></span>
                        </li>
                        <?php endforeach ?>
                    </ul>

                    <?php if ($_view['entry_page'] > 1) : ?>
                    <h3 class="h5">ページ</h3>
                    <ul>
                        <?php for ($i = 1; $i <= $_view['entry_page']; $i++) : ?>
                        <li><a href="<?php t(MAIN_FILE) ?>/entry/?<?php t(empty($_GET['category_sets']) ? '' : 'category_sets[]=' . $_GET['category_sets'][0] . '&') ?><?php t(empty($_GET['archive']) ? '' : 'archive=' . $_GET['archive'] . '&') ?>page=<?php t($i) ?>" class="<?php if ($i == $_GET['page']) : ?>selected<?php endif ?>"><?php t($i) ?></a></li>
                        <?php endfor ?>
                    </ul>
                    <?php endif ?>

                    <h3 class="h5">カテゴリ</h3>
                    <ul>
                        <li><a href="<?php t(MAIN_FILE) ?>/entry/" class="selected">全て</a></li>
                        <?php foreach ($_view['categories'] as $category) : ?>
                        <li><a href="<?php t(MAIN_FILE) ?>/entry/?category_sets[]=<?php t($category['id']) ?>"><?php h($category['name']) ?></a></li>
                        <?php endforeach ?>
                    </ul>

                    <h3 class="h5">アーカイブ</h3>
                    <ul>
                        <?php foreach ($_view['entry_archives'] as $entry_archive) : ?>
                        <li><a href="<?php t(MAIN_FILE) ?>/entry/?archive=<?php t($entry_archive['month']) ?>"><?php h(localdate('Y年n月', $entry_archive['month'])) ?>（<?php h($entry_archive['count']) ?>）</a></li>
                        <?php endforeach ?>
                    </ul>
                </main>

<?php import('app/views/footer.php') ?>
