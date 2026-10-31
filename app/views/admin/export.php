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
            <div class="card-header heading">HTML出力</div>
            <div class="card-body">
                <p>公開側のページをHTMLファイルに出力し、CSS・画像などと一緒に ZIP ファイルにまとめてダウンロードします。ページを1つずつ取得するので、ページ数が多いと時間がかかります。</p>

                <?php if (!empty($_view['errors'])) : ?>
                <div class="alert alert-danger">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#symbol-exclamation-triangle-fill"/></svg>
                    <?php foreach ($_view['errors'] as $error) : ?>
                    <?php h($error) ?><br>
                    <?php endforeach ?>
                </div>
                <?php endif ?>

                <?php if (!empty($_view['notices'])) : ?>
                <div class="card shadow-sm mb-3">
                    <div class="card-header">HTMLでは使えない機能</div>
                    <div class="card-body">
                        <ul class="mb-0" id="export_notices">
                            <?php foreach ($_view['notices'] as $notice) : ?>
                            <li><?php h($notice) ?></li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                </div>
                <?php endif ?>

                <?php if (empty($_view['errors'])) : ?>
                <div id="export">
                    <p><button type="button" class="btn btn-primary px-4" id="export_start">出力する</button></p>
                    <div class="progress mb-3 d-none" id="export_progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="d-none" id="export_status"></p>
                    <div class="alert alert-warning d-none" id="export_failures"></div>
                    <p class="d-none" id="export_download"><a href="<?php t(MAIN_FILE) ?>/admin/export_download" class="btn btn-success px-4">ZIP ファイルをダウンロード</a></p>
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

                // 失敗したら、理由を出して止める
                var fail = function(messages) {
                    $("#export_status").text("出力できませんでした。" + messages.join(" "));
                    $("#export_start").prop("disabled", false);
                };

                var request = function(data, callback) {
                    data._token = token;
                    data._type  = "json";

                    $.post(url, data, function(response) {
                        if (response.status === "OK") {
                            token = response.values.token;
                            callback(response.values);
                        } else {
                            fail(response.messages ? response.messages : [response.message]);
                        }
                    }, "json").fail(function() {
                        fail(["通信に失敗しました。"]);
                    });
                };

                var progress = function(done, total) {
                    var percent = total ? Math.floor(done / total * 100) : 0;

                    $("#export_progress .progress-bar").css("width", percent + "%").attr("aria-valuenow", percent);
                    $("#export_status").text(done + " / " + total + " ページを出力しました。");
                };

                var step = function(offset, total) {
                    request({exec: "step", offset: offset}, function(values) {
                        progress(values.done, values.total);

                        if (values.done < values.total) {
                            step(values.done, values.total);
                        } else {
                            $("#export_status").text("CSS・画像などを複製して、ZIP ファイルにまとめています。");

                            request({exec: "finish"}, function(values) {
                                $("#export_status").text(total + " ページを出力しました。");
                                if (values.failures.length) {
                                    $("#export_failures").removeClass("d-none").html("出力できなかったページがあります。<br>").append($("<ul class=\"mb-0\">").append($.map(values.failures, function(failure) {
                                        return $("<li>").text(failure);
                                    })));
                                }
                                $("#export_download").removeClass("d-none");
                                $("#export_start").prop("disabled", false);
                            });
                        }
                    });
                };

                $("#export_start").on("click", function() {
                    $(this).prop("disabled", true);
                    $("#export_progress, #export_status").removeClass("d-none");
                    $("#export_failures, #export_download").addClass("d-none");
                    $("#export_status").text("出力するページを調べています。");

                    request({exec: "start"}, function(values) {
                        progress(0, values.total);
                        step(0, values.total);
                    });
                });
            });
        </script>' . "\n";
?>

<?php import('app/views/admin/footer.php') ?>
