<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-list-ul"/></svg>
                            システム
                        </h2>
                        <nav style="--bs-breadcrumb-divider: '>';">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/">ホーム</a></li>
                                <?php if (!empty($_view['contents'])) : ?>
                                <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/setting">設定</a></li>
                                <?php endif ?>
                                <li class="breadcrumb-item active"><?php h($_view['title']) ?></li>
                            </ol>
                        </nav>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading"><?php h($_view['title']) ?></div>
                        <div class="card-body">
                            <?php if (isset($_GET['ok'])) : ?>
                            <div class="alert alert-success">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php if ($_GET['ok'] === 'post') : ?>
                                設定を登録しました。
                                <?php endif ?>
                            </div>
                            <?php elseif (isset($_GET['warning'])) : ?>
                            <div class="alert alert-danger">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                                <?php foreach ($_view['warnings'] as $warning) : ?>
                                <?php h($warning) ?>
                                <?php endforeach ?>
                            </div>
                            <?php endif ?>

                            <?php if (empty($_view['contents'])) : ?>
                            <ul>
                                <?php foreach ($GLOBALS['setting_title'] as $key => $title) : ?>
                                <li><a href="<?php t(MAIN_FILE) ?>/admin/setting?target=<?php t($key) ?>"><?php h($title) ?></a></li>
                                <?php endforeach ?>
                            </ul>
                            <?php else : ?>
                            <form action="<?php t(MAIN_FILE) ?>/admin/setting?target=<?php t($_GET['target']) ?>" method="post" class="register validate">
                                <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
                                <div class="card shadow-sm mb-3">
                                    <div class="card-header">
                                        設定
                                    </div>
                                    <div class="card-body">
                                        <?php foreach ($_view['contents'] as $key => $data) : ?>
                                        <div class="form-group mb-2">
                                            <label class="fw-bold"><?php t($data['name']) ?><?php if ($data['required']) : ?> <span class="badge bg-danger">必須</span><?php endif ?></label>
                                            <?php if ($data['type'] == 'text') : ?>
                                            <input type="text" name="<?php t($key) ?>" size="30" value="<?php t($_view['setting_sets'][$key]) ?>" class="form-control">
                                            <?php elseif ($data['type'] == 'textarea') : ?>
                                            <textarea name="<?php t($key) ?>" rows="5" cols="50" class="form-control"><?php t($_view['setting_sets'][$key]) ?></textarea>
                                            <?php elseif ($data['type'] == 'boolean') : ?>
                                            <div><label><input type="checkbox" name="<?php t($key) ?>" value="1" class="form-check-input"<?php $_view['setting_sets'][$key] ? e(' checked="checked"') : '' ?>> ON</label></div>
                                            <?php elseif ($data['type'] == 'select') : ?>
                                            <select name="<?php t($key) ?>" class="form-select" style="width: 200px;">
                                                <?php foreach ($GLOBALS['setting_contents'][$_GET['target']][$key]['kind'] as $kind_key => $kind_value) : ?>
                                                <option value="<?php t($kind_key) ?>"<?php $kind_key == $_view['setting_sets'][$key] ? e(' selected="selected"') : '' ?>><?php t($kind_value) ?></option>
                                                <?php endforeach ?>
                                            </select>
                                            <?php endif ?>
                                        </div>
                                        <?php endforeach ?>
                                        <div class="form-group mt-4">
                                            <button type="submit" class="btn btn-primary px-4">登録</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <?php endif ?>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
