<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-pencil-square"/></svg>
                            システム
                        </h2>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                            <?php if (isset($_view['warnings'])) : ?>
                            <div class="alert alert-danger">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php foreach ($_view['warnings'] as $warning) : ?>
                                <?php h($warning) ?>
                                <?php endforeach ?>
                            </div>
                            <?php endif ?>

                            <form action="<?php t(MAIN_FILE) ?>/admin/attribute_form<?php $_view['attribute']['id'] ? t('?id=' . $_view['attribute']['id']) : '' ?>" method="post" class="register validate">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="id" value="<?php t($_view['attribute']['id']) ?>">
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header">
                                        登録
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">名前 <span class="badge bg-danger">必須</span></label>
                                            <input type="text" name="name" size="30" value="<?php t($_view['attribute']['name']) ?>" class="form-control">
                                        </div>
                                        <div class="form-group mt-4">
                                            <button type="submit" class="btn btn-primary px-4">登録</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <?php if (!empty($_GET['id'])) : ?>
                            <form action="<?php t(MAIN_FILE) ?>/admin/attribute_delete" method="post" class="delete">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="id" value="<?php t($_view['attribute']['id']) ?>">
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header">
                                        削除
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-danger px-4">削除</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <?php endif ?>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
