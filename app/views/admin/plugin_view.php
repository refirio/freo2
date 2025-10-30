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
                            <dl class="row">
                                <dt class="col-sm-2">ID</dt>
                                <dd class="col-sm-10"><?php h($_view['plugin']['id']) ?></dd>
                                <dt class="col-sm-2">名前</dt>
                                <dd class="col-sm-10"><?php h($_view['plugin']['name']) ?></dd>
                                <dt class="col-sm-2">概要</dt>
                                <dd class="col-sm-10"><?php h($_view['plugin']['description']) ?></dd>
                                <dt class="col-sm-2">製作者</dt>
                                <dd class="col-sm-10"><?php h($_view['plugin']['author']) ?></dd>
                                <dt class="col-sm-2">URL</dt>
                                <dd class="col-sm-10"><?php h($_view['plugin']['link']) ?></dd>
                                <dt class="col-sm-2">バージョン</dt>
                                <dd class="col-sm-10"><?php h($_view['plugin']['version']) ?></dd>
                                <dt class="col-sm-2">更新日</dt>
                                <dd class="col-sm-10"><?php h($_view['plugin']['updated']) ?></dd>
                            </dl>
                        </div>
                    </div>
                </main>

<?php import('app/views/admin/footer.php') ?>
