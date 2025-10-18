<?php import('app/views/admin/header.php') ?>

                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 mb-2">
                        <h1 class="h3">
                            <svg class="bi flex-shrink-0" width="24" height="24" style="margin: 0 2px 4px 0;"><use xlink:href="#symbol-file-text"/></svg>
                            システム
                        </h1>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header heading">バージョン情報</div>
                        <div class="card-body">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header">
                                    CMS
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-2">名称</dt>
                                        <dd class="col-sm-10">freo</dd>
                                        <dt class="col-sm-2">バージョン</dt>
                                        <dd class="col-sm-10"><?php t(APP_VERSION_NUMBER) ?></dd>
                                        <dt class="col-sm-2">更新日</dt>
                                        <dd class="col-sm-10"><?php t(APP_VERSION_UPDATE) ?></dd>
                                    </dl>
                                </div>
                            </div>
                            <div class="card shadow-sm mb-3">
                                <div class="card-header">
                                    フレームワーク
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-2">名称</dt>
                                        <dd class="col-sm-10">levis</dd>
                                        <dt class="col-sm-2">バージョン</dt>
                                        <dd class="col-sm-10"><?php t(VERSION_NUMBER) ?></dd>
                                        <dt class="col-sm-2">更新日</dt>
                                        <dd class="col-sm-10"><?php t(VERSION_UPDATE) ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
