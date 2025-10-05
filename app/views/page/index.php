<?php import('app/views/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" id="page-<?php h($_view['page']['code']) ?>">
                    <h2 class="h4 mb-3">Page</h2>
                    <h3 class="h5"><?php h($_view['page']['title']) ?></h3>

                    <!--<p><time datetime="<?php h(localdate('Y-m-d', $_view['page']['datetime'])) ?>"><?php h(localdate('Y/m/d', $_view['page']['datetime'])) ?></time></p>-->

                    <?php if (!empty($_view['page']['text'])) : ?>
                    <div class="text">
                        <?php e($_view['page']['text']) ?>
                    </div>
                    <?php endif ?>

                    <?php if ($_view['page']['public'] === 'password' && empty($_SESSION['entry_passwords'][$_view['page']['id']])) : ?>
                    <form action="<?php t(MAIN_FILE) ?>/page/<?php t($_view['page']['code']) ?>" method="post">
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

                    <?php if (!empty($_view['page']['picture']) || !empty($_view['page']['thumbnail'])) : ?>
                    <div class="images">
                        <?php if (!empty($_view['page']['picture'])) : ?><div class="image mt-2 mb-2"><img src="<?php t($GLOBALS['config']['storage_url'] . $GLOBALS['config']['file_target']['entry'] . $_view['page']['id'] . '/' . $_view['page']['picture']) ?>" alt="" class="img-fluid"></div><?php endif ?>
                        <?php if (!empty($_view['page']['thumbnail'])) : ?><div class="image mt-2 mb-2"><img src="<?php t($GLOBALS['config']['storage_url'] . $GLOBALS['config']['file_target']['entry'] . $_view['page']['id'] . '/' . $_view['page']['thumbnail']) ?>" alt="" class="img-fluid"></div><?php endif ?>
                    </div>
                    <?php endif ?>

                    <?php e($_view['widgets']['page']) ?>
                </main>

<?php import('app/views/footer.php') ?>
