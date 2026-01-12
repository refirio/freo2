<?php import('app/views/header.php') ?>

            <?php if (!empty($GLOBALS['setting']['text_home_index'])) : ?>
            <div id="home">
                <?php e($GLOBALS['setting']['text_home_index']) ?>
            </div>
            <?php endif ?>

            <?php if (!empty($_view['page'])) : ?>
            <div id="page">
                <h2 class="h3 mb-3"><?php h($_view['page']['title']) ?></h2>

                <?php if (!empty($_view['page']['pictures']) && !empty($_view['page']['thumbnail'])) : ?>
                <div class="images">
                    <div class="image mt-2 mb-2"><a href="<?php t(MAIN_FILE) ?>/file/page/<?php t($_view['page']['code']) ?>"><img src="<?php t($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['entry'] . $_view['page']['id'] . '/' . $_view['page']['thumbnail']) ?>" alt="" class="img-fluid"></a></div>
                </div>
                <?php elseif (!empty($_view['page']['pictures']) || !empty($_view['page']['thumbnail'])) : ?>
                <div class="images">
                    <?php if (!empty($_view['page']['pictures'])) : ?>
                    <div class="image mt-2 mb-2">
                        <?php foreach ($_view['page']['pictures'] as $picture) : ?>
                        <img src="<?php t($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['entry'] . $_view['page']['id'] . '/' . $picture) ?>" alt="" class="img-fluid">
                        <?php endforeach ?>
                    </div>
                    <?php elseif (!empty($_view['page']['thumbnail'])) : ?>
                    <div class="image mt-2 mb-2">
                        <img src="<?php t($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['entry'] . $_view['page']['id'] . '/' . $_view['page']['thumbnail']) ?>" alt="" class="img-fluid">
                    </div>
                    <?php endif ?>
                </div>
                <?php endif ?>

                <div class="text">
                    <?php e($_view['page']['text']) ?>
                </div>
            </div>
            <?php endif ?>

            <?php if (!empty($_view['entries'])) : ?>
            <div id="entry">
                <h2 class="h3 mb-3"><?php h($GLOBALS['string']['heading_entry_recently']) ?></h2>
                <ul class="headline">
                    <?php foreach ($_view['entries'] as $entry) : ?>
                    <li>
                        <time datetime="<?php h(localdate('Y-m-d', $entry['datetime'])) ?>" class="datetime"><?php h(localdate('Y.m.d', $entry['datetime'])) ?></time>
                        <a href="<?php t(MAIN_FILE) ?>/entry/detail/<?php h($entry['code']) ?>" class="px-2"><?php h($entry['title']) ?></a>
                        <span class="text"><?php h(truncate(strip_tags($entry['text'] ?? ''), 100)) ?></span>
                    </li>
                    <?php endforeach ?>
                </ul>
            </div>
            <?php endif ?>

            <?php e($_view['widget_sets']['public_home']) ?>

<?php import('app/views/footer.php') ?>
