<?php import('app/views/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <h2 class="h4 mb-3">Entry</h2>
                    <h3 class="h5"><time datetime="<?php h(localdate('Y-m-d', $_view['entry']['datetime'])) ?>"><?php h(localdate('Y/m/d', $_view['entry']['datetime'])) ?></time> <?php h($_view['entry']['title']) ?></h3>

                    <?php if (!empty($_view['entry']['category_sets'])) : ?>
                    <ul class="category">
                        <?php foreach ($_view['entry']['category_sets'] as $category_sets) : ?>
                        <li><?php h($category_sets['category_name']) ?></li>
                        <?php endforeach ?>
                    </ul>
                    <?php endif ?>

                    <div class="text">
                        <?php e($_view['entry']['text']) ?>
                    </div>

                    <?php if ($_view['entry']['picture'] || $_view['entry']['thumbnail']) : ?>
                    <div class="images">
                        <?php if ($_view['entry']['picture']) : ?><div class="image"><img src="<?php t($GLOBALS['config']['storage_url'] . $GLOBALS['config']['file_targets']['entry'] . $_view['entry']['id'] . '/' . $_view['entry']['picture']) ?>" alt=""></div><?php endif ?>
                        <?php if ($_view['entry']['thumbnail']) : ?><div class="image"><img src="<?php t($GLOBALS['config']['storage_url'] . $GLOBALS['config']['file_targets']['entry'] . $_view['entry']['id'] . '/' . $_view['entry']['thumbnail']) ?>" alt=""></div><?php endif ?>
                    </div>
                    <?php endif ?>
                </main>

<?php import('app/views/footer.php') ?>
