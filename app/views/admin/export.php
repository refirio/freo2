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
                    <li class="breadcrumb-item active"><?php h($_view['title']) ?></li>
                </ol>
            </nav>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header heading">HTML出力（サーバーに出力）</div>
            <div class="card-body">
                <p>公開側のページをHTMLファイルにして、このサーバーの <code><?php h($GLOBALS['config']['export_directory']) ?></code> に出力します。出力したページは、PHPを通さないので表示が速くなります。出力していないページは、今までどおりPHPで表示します。<br>ページを1つずつ取得するので、ページ数が多いと時間がかかります。</p>

                <?php if (!empty($_view['directory_errors'])) : ?>
                <div class="alert alert-danger">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                    <?php foreach ($_view['directory_errors'] as $error) : ?>
                    <?php h($error) ?><br>
                    <?php endforeach ?>
                </div>
                <?php endif ?>

                <?php if (!empty($_view['directory_notices'])) : ?>
                <div class="card shadow-sm mb-3">
                    <div class="card-header">注意</div>
                    <div class="card-body">
                        <ul class="mb-0" id="export_directory_notices">
                            <?php foreach ($_view['directory_notices'] as $notice) : ?>
                            <li><?php h($notice) ?></li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                </div>
                <?php endif ?>

                <?php if (empty($_view['directory_errors'])) : ?>
                <div class="export" data-target="directory">
                    <p>
                        <button type="button" class="btn btn-primary px-4 export-start">出力する</button>
                        <button type="button" class="btn btn-danger px-4 export-clear<?php if (!$_view['published']) : ?> d-none<?php endif ?>">出力したページを削除する</button>
                    </p>
                    <div class="progress mb-3 d-none export-progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="d-none export-status"></p>
                    <div class="alert alert-info d-none export-skips"></div>
                    <div class="alert alert-warning d-none export-failures"></div>
                </div>
                <?php endif ?>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header heading">HTML出力（ZIPファイルに出力）</div>
            <div class="card-body">
                <p>公開側のページをHTMLファイルに出力し、CSS・画像などと一緒にZIPファイルにまとめてダウンロードします。<br>ページを1つずつ取得するので、ページ数が多いと時間がかかります。</p>

                <?php if (!empty($_view['zip_errors'])) : ?>
                <div class="alert alert-danger">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                    <?php foreach ($_view['zip_errors'] as $error) : ?>
                    <?php h($error) ?><br>
                    <?php endforeach ?>
                </div>
                <?php endif ?>

                <?php if (!empty($_view['zip_notices'])) : ?>
                <div class="card shadow-sm mb-3">
                    <div class="card-header">注意</div>
                    <div class="card-body">
                        <ul class="mb-0" id="export_zip_notices">
                            <?php foreach ($_view['zip_notices'] as $notice) : ?>
                            <li><?php h($notice) ?></li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                </div>
                <?php endif ?>

                <?php if (empty($_view['zip_errors'])) : ?>
                <div class="export" data-target="zip">
                    <p><button type="button" class="btn btn-primary px-4 export-start">出力する</button></p>
                    <div class="progress mb-3 d-none export-progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="d-none export-status"></p>
                    <div class="alert alert-warning d-none export-failures"></div>
                    <p class="d-none export-download"><a href="<?php t(MAIN_FILE) ?>/admin/export_download" class="btn btn-success px-4">ZIPファイルをダウンロード</a></p>
                </div>
                <?php endif ?>
            </div>
        </div>
    </main>

<?php
$_view['script'] = ($_view['script'] ?? '') . '<script>
            $(document).ready(function() {
                var token = ' . json_encode($_view['token'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';
                var url   = ' . json_encode(MAIN_FILE . '/admin/export', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';

                // 出力の操作（同時には1つだけ）
                var buttons = function(disabled) {
                    $(".export-start, .export-clear").prop("disabled", disabled);
                };

                var request = function(area, data, callback) {
                    data._token = token;
                    data._type  = "json";

                    $.post(url, data, function(response) {
                        if (response.status === "OK") {
                            token = response.values.token;
                            callback(response.values);
                        } else {
                            fail(area, response.messages ? response.messages : [response.message]);
                        }
                    }, "json").fail(function() {
                        fail(area, ["通信に失敗しました。"]);
                    });
                };

                // 失敗したら、理由を出して止める
                var fail = function(area, messages) {
                    area.find(".export-status").removeClass("d-none").text("処理できませんでした。" + messages.join(" "));
                    buttons(false);
                };

                // URLの一覧を出す
                var list = function(element, message, urls) {
                    if (urls.length) {
                        element.removeClass("d-none").html(message + "<br>").append($("<ul class=\"mb-0\">").append($.map(urls, function(url) {
                            return $("<li>").text(url);
                        })));
                    }
                };

                var progress = function(area, done, total) {
                    var percent = total ? Math.floor(done / total * 100) : 0;

                    area.find(".progress-bar").css("width", percent + "%").attr("aria-valuenow", percent);
                    area.find(".export-status").text(done + " / " + total + " ページを出力しました。");
                };

                var step = function(area, offset, total) {
                    request(area, {exec: "step", offset: offset}, function(values) {
                        progress(area, values.done, values.total);

                        if (values.done < values.total) {
                            step(area, values.done, values.total);
                            return;
                        }

                        var directory = area.data("target") === "directory";

                        area.find(".export-status").text(directory ? "サーバーに出力しています。" : "CSS・画像などを複製して、ZIPファイルにまとめています。");

                        request(area, {exec: "finish"}, function(values) {
                            var count = total - values.failures.length - values.skips.length;

                            area.find(".export-status").text(count + " ページを出力しました。");
                            list(area.find(".export-skips"), "フォームがあるので、出力せずにPHPで表示するページです。", values.skips);
                            list(area.find(".export-failures"), "出力できなかったページがあります。", values.failures);

                            if (directory) {
                                area.find(".export-clear").toggleClass("d-none", count === 0);
                            } else {
                                area.find(".export-download").removeClass("d-none");
                            }
                            buttons(false);
                        });
                    });
                };

                $(".export-start").on("click", function() {
                    var area = $(this).closest(".export");

                    buttons(true);
                    area.find(".export-progress, .export-status").removeClass("d-none");
                    area.find(".export-skips, .export-failures, .export-download").addClass("d-none");
                    area.find(".export-status").text("出力するページを調べています。");

                    request(area, {exec: "start", target: area.data("target")}, function(values) {
                        progress(area, 0, values.total);
                        step(area, 0, values.total);
                    });
                });

                $(".export-clear").on("click", function() {
                    var area = $(this).closest(".export");

                    buttons(true);
                    area.find(".export-progress, .export-skips, .export-failures").addClass("d-none");

                    request(area, {exec: "clear"}, function(values) {
                        area.find(".export-status").removeClass("d-none").text("出力したページを削除しました。");
                        area.find(".export-clear").addClass("d-none");
                        buttons(false);
                    });
                });
            });
        </script>' . "\n";
?>

<?php import('app/views/admin/footer.php') ?>
