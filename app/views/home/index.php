<?php import('app/views/header.php') ?>

                    <div id="home">
                        <?php e($GLOBALS['setting']['text_home_index']) ?>
                    </div>

                    <div id="page">
                        <?php if (!empty($_view['page'])) : ?>
                        <h2 class="h4 mb-3"><?php h($_view['page']['title']) ?></h2>

                        <div class="text">
                            <?php e($_view['page']['text']) ?>
                        </div>

                        <?php if (!empty($_view['page']['picture']) && !empty($_view['page']['thumbnail'])) : ?>
                        <div class="images">
                            <div class="image mt-2 mb-2"><a href="<?php t($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['entry'] . $_view['page']['id'] . '/' . $_view['page']['picture']) ?>"><img src="<?php t($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['entry'] . $_view['page']['id'] . '/' . $_view['page']['thumbnail']) ?>" alt="" class="img-fluid"></a></div>
                        </div>
                        <?php elseif (!empty($_view['page']['picture']) || !empty($_view['page']['thumbnail'])) : ?>
                        <div class="images">
                            <?php if (!empty($_view['page']['picture'])) : ?><div class="image mt-2 mb-2"><img src="<?php t($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['entry'] . $_view['page']['id'] . '/' . $_view['page']['picture']) ?>" alt="" class="img-fluid"></div><?php endif ?>
                            <?php if (!empty($_view['page']['thumbnail'])) : ?><div class="image mt-2 mb-2"><img src="<?php t($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['entry'] . $_view['page']['id'] . '/' . $_view['page']['thumbnail']) ?>" alt="" class="img-fluid"></div><?php endif ?>
                        </div>
                        <?php endif ?>

                        <?php endif ?>
                    </div>

                    <div id="entry">
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
                    </div>

                    <?php e($_view['widget_sets']['public_home']) ?>

<?php import('app/views/footer.php') ?>
