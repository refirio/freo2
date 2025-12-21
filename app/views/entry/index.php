<?php import('app/views/header.php') ?>

            <div id="entry">
                <h2 class="h3 mb-3"><?php h($GLOBALS['string']['heading_entry_list']) ?></h2>
                <?php e($GLOBALS['setting']['text_entry_index']) ?>

                <?php foreach ($_view['entries'] as $entry) : ?>
                <h3 class="h4"><time datetime="<?php h(localdate('Y-m-d', $entry['datetime'])) ?>"><?php h(localdate('Y/m/d', $entry['datetime'])) ?></time> <?php h($entry['title']) ?></h3>

                <?php if (!empty($entry['category_sets'])) : ?>
                <ul class="category">
                    <?php foreach ($entry['category_sets'] as $category_sets) : ?>
                    <li><?php h($category_sets['category_name']) ?></li>
                    <?php endforeach ?>
                </ul>
                <?php endif ?>

                <div class="text">
                    <?php if (!empty($entry['thumbnail'])) : ?>
                    <p class="mt-1"><img src="<?php t($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['entry'] . $entry['id'] . '/' . $entry['thumbnail']) ?>" alt="" class="img-fluid"></p>
                    <?php endif ?>

                    <?php if (!empty($entry['text'])) : ?>
                    <p class="mb-1"><?php h(truncate(strip_tags($entry['text'] ?? ''), 100)) ?></p>
                    <?php endif ?>
                    <p class="mt-1"><a href="<?php t(MAIN_FILE) ?>/entry/detail/<?php h($entry['code']) ?>"><?php h($GLOBALS['string']['text_entry_continue']) ?></a></p>
                </div>
                <?php endforeach ?>

                <?php if ($_view['entry_page'] > 1) : ?>
                <h3 class="h4"><?php h($GLOBALS['string']['heading_entry_page']) ?></h3>
                <ul>
                    <?php for ($i = 1; $i <= $_view['entry_page']; $i++) : ?>
                    <li><a href="<?php t(MAIN_FILE) ?>/entry/?<?php t(empty($_GET['category_sets']) ? '' : 'category_sets[]=' . $_GET['category_sets'][0] . '&') ?><?php t(empty($_GET['archive']) ? '' : 'archive=' . $_GET['archive'] . '&') ?>page=<?php t($i) ?>" class="<?php if ($i == $_GET['page']) : ?>selected<?php endif ?>"><?php t($i) ?></a></li>
                    <?php endfor ?>
                </ul>
                <?php endif ?>
            </div>

            <div id="category">
                <h3 class="h4"><?php h($GLOBALS['string']['heading_category_list']) ?></h3>
                <ul>
                    <li><a href="<?php t(MAIN_FILE) ?>/entry/" class="selected"><?php h($GLOBALS['string']['text_category_all']) ?></a></li>
                    <?php foreach ($_view['categories'] as $category) : ?>
                    <li><a href="<?php t(MAIN_FILE) ?>/entry/?category_sets[]=<?php t($category['id']) ?>"><?php h($category['name']) ?></a></li>
                    <?php endforeach ?>
                </ul>
            </div>

            <div id="archive">
                <h3 class="h4"><?php h($GLOBALS['string']['heading_archive_list']) ?></h3>
                <ul>
                    <?php foreach ($_view['entry_archives'] as $entry_archive) : ?>
                    <li><a href="<?php t(MAIN_FILE) ?>/entry/?archive=<?php t($entry_archive['month']) ?>"><?php h(localdate('Y年n月', $entry_archive['month'])) ?>（<?php h($entry_archive['count']) ?>）</a></li>
                    <?php endforeach ?>
                </ul>
            </div>

            <?php e($_view['widget_sets']['public_page']) ?>

<?php import('app/views/footer.php') ?>
