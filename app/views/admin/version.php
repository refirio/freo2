<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 mb-2 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h2 class="h3">
                            <svg class="bi flex-shrink-0 me-1 mb-1" width="24" height="24"><use xlink:href="#symbol-file-text"/></svg>
                            システム
                        </h2>
                        <nav style="--bs-breadcrumb-divider: '>';">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php t(MAIN_FILE) ?>/admin/">ホーム</a></li>
                                <li class="breadcrumb-item active"><?php h($_view['title']) ?></li>
                            </ol>
                        </nav>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading">バージョン情報</div>
                        <div class="card-body">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header">
                                    CMS
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-2">名称</dt>
                                        <dd class="col-sm-10">freo</dd>
                                        <dt class="col-sm-2">バージョン</dt>
                                        <dd class="col-sm-10"><?php h(APP_VERSION_NUMBER) ?></dd>
                                        <dt class="col-sm-2">更新日</dt>
                                        <dd class="col-sm-10"><?php h(localdate('Y/m/d', APP_VERSION_UPDATE)) ?></dd>
                                    </dl>
                                </div>
                            </div>
                            <div class="card shadow-sm mb-3">
                                <div class="card-header">
                                    フレームワーク
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-2">名称</dt>
                                        <dd class="col-sm-10">levis</dd>
                                        <dt class="col-sm-2">バージョン</dt>
                                        <dd class="col-sm-10"><?php h(VERSION_NUMBER) ?></dd>
                                        <dt class="col-sm-2">更新日</dt>
                                        <dd class="col-sm-10"><?php h(localdate('Y/m/d', VERSION_UPDATE)) ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
