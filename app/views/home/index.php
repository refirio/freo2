<?php import('app/views/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <?php if (!empty($_view['page'])) : ?>
                    <h2 class="h4 mb-3"><?php h($_view['page']['title']) ?></h2>

                    <div class="text">
                        <?php e($_view['page']['text']) ?>
                    </div>

                    <?php if ($_view['page']['picture'] || $_view['page']['thumbnail']) : ?>
                    <div class="images">
                        <?php if ($_view['page']['picture']) : ?><div class="image"><img src="<?php t($GLOBALS['config']['storage_url'] . $GLOBALS['config']['file_targets']['page'] . $_view['page']['id'] . '/' . $_view['page']['picture']) ?>" alt=""></div><?php endif ?>
                        <?php if ($_view['page']['thumbnail']) : ?><div class="image"><img src="<?php t($GLOBALS['config']['storage_url'] . $GLOBALS['config']['file_targets']['page'] . $_view['page']['id'] . '/' . $_view['page']['thumbnail']) ?>" alt=""></div><?php endif ?>
                    </div>
                    <?php endif ?>
                    <?php endif ?>
                    <h2 class="h4 mb-3">Entry</h2>
                    <ul class="headline">
                        <?php foreach ($_view['entries'] as $entry) : ?>
                        <li>
                            <time datetime="<?php h(localdate('Y-m-d', $entry['datetime'])) ?>" class="datetime"><?php h(localdate('Y.m.d', $entry['datetime'])) ?></time>
                            <a href="<?php t(MAIN_FILE) ?>/entry/detail/<?php h($entry['code']) ?>"><?php h($entry['title']) ?></a>
                            <span class="text"><?php h(truncate(strip_tags($entry['text']), 100)) ?></span>
                        </li>
                        <?php endforeach ?>
                    </ul>
                    <?php e($_view['widgets']['home']) ?>
                </main>

<?php import('app/views/footer.php') ?>
