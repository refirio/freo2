<?php import('app/views/header.php') ?>

            <div id="page-<?php h($_view['entry']['code']) ?>">
                <h2 class="h3 mb-3"><?php h($_view['entry']['title']) ?></h2>
                <?php e($GLOBALS['setting']['text_page_index']) ?>

                <?php if (!empty($_view['entry']['category_sets'])) : ?>
                <ul class="category">
                    <?php foreach ($_view['entry']['category_sets'] as $category_sets) : ?>
                    <li><?php h($category_sets['category_name']) ?></li>
                    <?php endforeach ?>
                </ul>
                <?php endif ?>

                <?php if (!empty($_view['entry']['pictures']) && !empty($_view['entry']['thumbnail'])) : ?>
                <div class="images">
                    <div class="image my-3"><a href="<?php t(MAIN_FILE) ?>/file/page/<?php t($_view['entry']['code']) ?>"><img src="<?php t($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['entry'] . $_view['entry']['id'] . '/' . $_view['entry']['thumbnail']) ?>" alt="" class="img-fluid"></a></div>
                </div>
                <?php elseif (!empty($_view['entry']['pictures']) || !empty($_view['entry']['thumbnail'])) : ?>
                <div class="images">
                    <?php if (!empty($_view['entry']['pictures'])) : ?>
                    <div class="image my-3">
                        <?php foreach ($_view['entry']['pictures'] as $picture) : ?>
                        <img src="<?php t($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['entry'] . $_view['entry']['id'] . '/' . $picture) ?>" alt="" class="img-fluid">
                        <?php endforeach ?>
                    </div>
                    <?php elseif (!empty($_view['entry']['thumbnail'])) : ?>
                    <div class="image my-3">
                        <img src="<?php t($GLOBALS['config']['storage_url'] . '/' . $GLOBALS['config']['file_target']['entry'] . $_view['entry']['id'] . '/' . $_view['entry']['thumbnail']) ?>" alt="" class="img-fluid">
                    </div>
                    <?php endif ?>
                </div>
                <?php endif ?>

                <?php if (!empty($_view['entry']['text'])) : ?>
                <div class="text">
                    <?php e($_view['entry']['text']) ?>
                </div>
                <?php endif ?>

                <?php import('app/views/field.php') ?>

                <?php import('app/views/password_form.php') ?>
            </div>

            <?php import('app/views/comment.php') ?>

            <?php import('app/views/comment_form.php') ?>

            <?php e($_view['widget_sets']['public_page']) ?>

<?php import('app/views/footer.php') ?>
