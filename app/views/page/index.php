<?php import('app/views/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <h2 class="h4 mb-3">Page</h2>
                    <h3 class="h5"><?php h($_view['page']['title']) ?></h3>

                    <!--<p><time datetime="<?php h(localdate('Y-m-d', $_view['page']['datetime'])) ?>"><?php h(localdate('Y/m/d', $_view['page']['datetime'])) ?></time></p>-->

                    <div class="text">
                        <?php e($_view['page']['text']) ?>
                    </div>

                    <?php if ($_view['page']['picture'] || $_view['page']['thumbnail']) : ?>
                    <div class="images">
                        <?php if ($_view['page']['picture']) : ?><div class="image"><img src="<?php t($GLOBALS['config']['storage_url'] . $GLOBALS['config']['file_targets']['page'] . $_view['page']['id'] . '/' . $_view['page']['picture']) ?>" alt=""></div><?php endif ?>
                        <?php if ($_view['page']['thumbnail']) : ?><div class="image"><img src="<?php t($GLOBALS['config']['storage_url'] . $GLOBALS['config']['file_targets']['page'] . $_view['page']['id'] . '/' . $_view['page']['thumbnail']) ?>" alt=""></div><?php endif ?>
                    </div>
                    <?php endif ?>

                    <?php e($_view['widgets']['page']) ?>
                </main>

<?php import('app/views/footer.php') ?>
