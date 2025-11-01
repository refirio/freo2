<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
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

                            <form action="<?php t(MAIN_FILE) ?>/admin/user_form<?php $_view['user']['id'] ? t('?id=' . $_view['user']['id']) : '' ?>" method="post" class="register validate">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="id" value="<?php t($_view['user']['id']) ?>">
                                <input type="hidden" name="view" value="">
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header">
                                        登録
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">ユーザー名 <span class="badge bg-danger">必須</span></label>
                                            <input type="text" name="username" size="30" value="<?php t($_view['user']['username']) ?>" class="form-control">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">パスワード<?php if (empty($_GET['id'])) : ?> <span class="badge bg-danger">必須</span><?php else : ?>（変更したい場合のみ入力）<?php endif ?></label>
                                            <input type="password" name="password" size="30" value="" class="form-control">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">パスワード確認（同じものをもう一度入力）</label>
                                            <input type="password" name="password_confirm" size="30" value="" class="form-control">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">名前</label>
                                            <input type="text" name="name" size="30" value="<?php t($_view['user']['name']) ?>" class="form-control">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">メールアドレス <span class="badge bg-danger">必須</span></label>
                                            <input type="text" name="email" size="30" value="<?php t($_view['user']['email']) ?>" class="form-control">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">URL</label>
                                            <input type="text" name="url" size="30" value="<?php t($_view['user']['url']) ?>" class="form-control">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">自己紹介</label>
                                            <textarea name="text" rows="10" cols="20" class="form-control"><?php t($_view['user']['text']) ?></textarea>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">メモ</label>
                                            <textarea name="memo" rows="10" cols="50" class="form-control"><?php t($_view['user']['memo']) ?></textarea>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">権限 <span class="badge bg-danger">必須</span></label>
                                            <select name="authority_id" class="form-select" style="width: 200px;">
                                                <option value=""></option>
                                                <?php foreach ($_view['authorities'] as $authority) : ?>
                                                <option value="<?php t($authority['id']) ?>"<?php $authority['id'] == $_view['user']['authority_id'] ? e(' selected="selected"') : '' ?>><?php t($authority['name']) ?></option>
                                                <?php endforeach ?>
                                            </select>
                                        </div>
                                        <?php if (!empty($_view['attributes'])) : ?>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">属性</label>
                                            <div id="validate_attribute_sets">
                                                <?php foreach ($_view['attributes'] as $attribute) : ?>
                                                <label><input type="checkbox" name="attribute_sets[]" value="<?php t($attribute['id']) ?>" class="form-check-input"<?php in_array($attribute['id'], array_column($_view['user']['attribute_sets'], 'attribute_id')) ? e(' checked="checked"') : '' ?>> <?php t($attribute['name']) ?></label><br>
                                                <?php endforeach ?>
                                            </div>
                                        </div>
                                        <?php endif ?>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold">有効 <span class="badge bg-danger">必須</span></label>
                                            <select name="enabled" class="form-select" style="width: 200px;">
                                                <?php foreach ($GLOBALS['config']['option']['user']['enabled'] as $key => $value) : ?>
                                                <option value="<?php t($key) ?>"<?php $key == $_view['user']['enabled'] ? e(' selected="selected"') : '' ?>><?php t($value) ?></option>
                                                <?php endforeach ?>
                                            </select>
                                        </div>
                                        <div class="form-group mt-4">
                                            <button type="submit" class="btn btn-primary px-4">登録</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <?php if (!empty($_GET['id'])) : ?>
                            <form action="<?php t(MAIN_FILE) ?>/admin/user_delete" method="post" class="delete">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <input type="hidden" name="id" value="<?php t($_view['user']['id']) ?>">
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
