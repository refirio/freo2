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

                    <?php if (!empty($_view['entry']['text'])) : ?>
                    <div class="text">
                        <?php e($_view['entry']['text']) ?>
                    </div>
                    <?php endif ?>

                    <?php if ($_view['entry']['public'] === 'password' && empty($_SESSION['entry_passwords'][$_view['entry']['id']])) : ?>
                    <form action="<?php t(MAIN_FILE) ?>/entry/detail/<?php t($_view['entry']['code']) ?>" method="post">
                        <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                        <input type="hidden" name="view" value="">
                        <div class="form-group mb-2">
                            <label>パスワード</label>
                            <input type="password" name="password" value="" class="form-control">
                        </div>
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary px-4">認証</button>
                        </div>
                    </form>
                    <?php endif ?>

                    <?php if (!empty($_view['entry']['picture']) || !empty($_view['entry']['thumbnail'])) : ?>
                    <div class="images">
                        <?php if (!empty($_view['entry']['picture'])) : ?><div class="image mt-2 mb-2"><img src="<?php t($GLOBALS['config']['storage_url'] . $GLOBALS['config']['file_target']['entry'] . $_view['entry']['id'] . '/' . $_view['entry']['picture']) ?>" alt="" class="img-fluid"></div><?php endif ?>
                        <?php if (!empty($_view['entry']['thumbnail'])) : ?><div class="image mt-2 mb-2"><img src="<?php t($GLOBALS['config']['storage_url'] . $GLOBALS['config']['file_target']['entry'] . $_view['entry']['id'] . '/' . $_view['entry']['thumbnail']) ?>" alt="" class="img-fluid"></div><?php endif ?>
                    </div>
                    <?php endif ?>
                </main>

<?php import('app/views/footer.php') ?>
