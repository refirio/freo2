<?php

if (DEBUG_LEVEL) {
    /* テスト */
    if ($_REQUEST['_mode'] === 'home' && $_REQUEST['_work'] === 'index' && isset($_GET['test'])) {
        if ($_GET['test'] === 'start') {
            // テスト開始
            if (isset($_GET['test_target']) && preg_match('/^[\w\-]+$/', $_GET['test_target'])) {
                // 個別テスト
                $_SESSION['test'] = array(
                    'target'  => $_GET['test_target'],
                    'session' => 0,
                    'start'   => localdate(),
                    'bulk'    => false,
                );
            } else {
                // 一括テスト
                if (empty($_SESSION['test'])) {
                    $flag = true;
                } else {
                    $flag = false;
                }

                $target = null;
                if ($dh = opendir('scenario/')) {
                    while (($entry = readdir($dh)) !== false) {
                        if (!is_file('scenario/' . $entry)) {
                            continue;
                        }

                        if (preg_match('/^([\w\-]+)\.js$/', $entry, $matches)) {
                            if (isset($_SESSION['test']) && $_SESSION['test']['target'] === $matches[1] && $flag === false) {
                                $flag = true;
                            } elseif ($flag === true) {
                                $target = $matches[1];

                                break;
                            }
                        }
                    }
                    closedir($dh);
                } else {
                    echo '<div class="error">テストシナリオ格納ディレクトリを開けません。</div>';
                    exit;
                }

                if ($target === null) {
                    if (empty($_SESSION['test'])) {
                        echo '<div class="error">テストシナリオが見つかりません。</div>';
                        exit;
                    } else {
                        unset($_SESSION['test']);

                        echo "<script>\n";
                        echo "window.alert('一括テストが終了しました。');\n";
                        echo "window.location.href = '" . t(MAIN_FILE, true) . "/?test=end&complete=" . localdate('YmdHis') . "';\n";
                        echo "</script>\n";
                    }
                }

                $_SESSION['test'] = array(
                    'target'  => $target,
                    'session' => 0,
                    'start'   => localdate(),
                    'bulk'    => true,
                );
            }
        } elseif ($_GET['test'] === 'end') {
            // テスト終了
            unset($_SESSION['test']);

            echo "<script>\n";
            echo "window.location.href = '" . t(MAIN_FILE, true) . "/tool/test';\n";
            echo "</script>\n";
        }
    }

    if (isset($_SESSION['test'])) {
        // テスト実行

?>
<style>
dl#test {
    z-index: 9999;
    position:  absolute;
    bottom: 0;
    left: 10px;
    padding: 5px 10px;
    border: 1px solid #CCCCCC;
    background-color: #FFFFFF;
}
dl#test dt {
    clear: left;
    float: left;
}
dl#test dt:after {
    content: ":";
}
dl#test dd {
    margin-left: 5em;
}
</style>
<dl id="test">
    <dt>target</dt>
        <dd><?php t($_SESSION['test']['target']) ?></dd>
    <dt>session</dt>
        <dd><?php t($_SESSION['test']['session']) ?></dd>
    <dt>start</dt>
        <dd><?php t($_SESSION['test']['start']) ?></dd>
    <dt>bulk</dt>
        <dd><?php t($_SESSION['test']['bulk'] ? 'true' : 'false') ?></dd>
    <dt>datetime</dt>
        <dd><?php t(localdate('Ymd His')) ?></dd>
</dl>
<script src="<?php t($GLOBALS['config']['http_path']) ?><?php t(loader_js('html2canvas.js')) ?>"></script>
<script>
/* テスト初期化 */
var test = {
    target: '<?php t($_SESSION['test']['target']) ?>',
    session: '<?php t($_SESSION['test']['session']) ?>',
    start: '<?php t($_SESSION['test']['start']) ?>',
    bulk: '<?php t($_SESSION['test']['bulk']) ?>',
    date: '<?php t(localdate('Ymd')) ?>',
    time: '<?php t(localdate('His')) ?>',
    executable: true,
    timeout: 10,
    scenario_dir: 'scenario/',
    config: {
        main_file: '<?php t(MAIN_FILE) ?>',
        http_path: '<?php t($GLOBALS['config']['http_path']) ?>'
    },
    data: {},
    scenario: [],
    loadScenario: function(file) {
        test.executable = false;

        var script = document.createElement('script');
        script.src = test.config.http_path + test.scenario_dir + file + '.js?' + test.start;
        script.onload = function() {
            test.executable = true;
        };
        document.body.appendChild(script);
    },
    saveScreenshot: function(name, callback) {
        html2canvas(document.body, {
            onrendered: function(canvas) {
                $.post(
                    test.config.main_file + '/tool/test/image',
                    {
                        name: name,
                        image: canvas.toDataURL('image/png')
                    },
                    callback()
                );
            }
        });
    }
};
/* テスト実行 */
$(window).on('load', function() {
    var script = document.createElement('script');

    // シナリオを読み込む
    script.src = test.config.http_path + test.scenario_dir + test.target + '.js?' + test.start;
    script.onload = function() {
        setInterval(function() {
            // 実行可能なシナリオがあるか確認する
            if (test.executable) {
                var session = test.session;

                // シナリオを完了しているか調べる
                if (session < test.scenario.length) {
                    // 完了していなければタイムアウトを設定する
                    setTimeout(function() {
                        window.alert('テスト ' + test.target + ' がタイムアウトしました。');
                        window.location.href = test.config.main_file + '/?test=end&error=' + test.date + test.time;
                    }, 10000);

                    // シナリオを進める
                    test.scenario[session]();
                } else {
                    // 完了していれば一括テストかどうか調べる
                    if (test.bulk) {
                        // 一括テストなら次のシナリオへ
                        window.location.href = test.config.main_file + '/?test=start';
                    } else {
                        // 個別テストなら終了する
                        setTimeout(function() {
                            window.alert('テスト ' + test.target + ' が終了しました。');
                            window.location.href = test.config.main_file + '/?test=end&complete=' + test.date + test.time;
                        }, 100);
                    }
                }

                test.executable = false;
            }
        }, 100);
    };

    document.body.appendChild(script);
});
</script>
<?php

        $_SESSION['test']['session']++;
    }
}
