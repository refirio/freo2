<?php import('app/views/header.php') ?>

            <div id="entry">
                <h2 class="h3 mb-3"><?php h($_view['entry']['title']) ?></h2>

                <?php if (!empty($_view['entry']['pictures'])) : ?>
                <div class="images">
                    <div class="image mt-2 mb-2">
                        <?php foreach ($_view['entry']['pictures'] as $picture) : ?>
                        <img src="<?php t($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['entry'] . $_view['entry']['id'] . '/' . $picture) ?>" alt="" class="img-fluid">
                        <?php endforeach ?>
                    </div>
                </div>
                <?php endif ?>
            </div>

            <?php e($_view['widget_sets']['public_page']) ?>

<?php import('app/views/footer.php') ?>
