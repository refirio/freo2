<?php import('app/views/header.php') ?>

    <div id="entry">
        <h2 class="h3 mb-3"><?php h($_view['entry']['title']) ?></h2>

        <?php if (!empty($_view['entry']['pictures'])) : ?>
        <div class="images">
            <div class="image my-3">
                <?php foreach ($_view['entry']['pictures'] as $picture) : ?>
                <img src="<?php t($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['entry'] . $_view['entry']['id'] . '/' . $picture) ?>" alt="" class="img-fluid rounded mx-auto d-block mb-3">
                <?php endforeach ?>
            </div>
        </div>
        <?php endif ?>

        <p class="my-4 text-center"><a href="<?php t(MAIN_FILE) ?><?php t($_view['entry']['type_code'] === 'page' ? '/page/' : '/' . $_view['entry']['type_code'] . '/detail/') ?><?php t($_view['entry']['code']) ?>" class="btn btn-secondary"><?php h($GLOBALS['string']['text_goto_entry']) ?></a></p>
    </div>

    <?php e($_view['widget_sets']['public_page']) ?>

<?php import('app/views/footer.php') ?>
