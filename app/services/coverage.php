<?php

/**
 * コードカバレッジを記録開始
 *
 * @return void
 */
function service_coverage_start()
{
    if (!function_exists('xdebug_start_code_coverage')) {
        return;
    }

    xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);

    return;
}

/**
 * コードカバレッジを記録終了
 *
 * @return array
 */
function service_coverage_end()
{
    if (!function_exists('xdebug_start_code_coverage')) {
        return [];
    }

    $coverages = xdebug_get_code_coverage();
    xdebug_stop_code_coverage();

    return $coverages;
}

/**
 * コードカバレッジを出力
 *
 * @param array $coverages
 * @param array $targets
 *
 * @return void
 */
function service_coverage_output($coverages, $targets)
{
    if (!function_exists('xdebug_start_code_coverage')) {
        return;
    }

    echo "<style>\n";
    echo "table {\n";
    echo "    border-collapse: collapse;\n";
    echo "    border-spacing: 0;\n";
    echo "}\n";
    echo "table tr td {\n";
    echo "    border: 0;\n";
    echo "}\n";
    echo "table tr td.used {\n";
    echo "    background-color:#DDEEDD;\n";
    echo "}\n";
    echo "table tr td.unused {\n";
    echo "    background-color:#EEDDDD;\n";
    echo "}\n";
    echo "table tr td.dead_code {\n";
    echo "    background-color:#EEEEEE;\n";
    echo "}\n";
    echo "div.coverage {\n";
    echo "    padding: 10px 20px;\n";
    echo "    background-color:#EEEEEE;\n";
    echo "}\n";
    echo "div.coverage h2 {\n";
    echo "    margin: 0;\n";
    echo "    font-size: 110%;\n";
    echo "}\n";
    echo "div.coverage p {\n";
    echo "    margin: 0;\n";
    echo "}\n";
    echo "div.coverage div.file {\n";
    echo "    overflow: scroll;\n";
    echo "    width: 100%;\n";
    echo "    max-height: 500px;\n";
    echo "    margin: 0 0 10px;\n";
    echo "    border: 1px solid #CCCCCC;\n";
    echo "    background-color:#FFFFFF;\n";
    echo "}\n";
    echo "</style>\n";
    echo "<div class=\"coverage\">\n";

    // ファイルごとのコードカバレッジを確認
    foreach ($coverages as $path => $lines) {
        $path = str_replace('\\', '/', $path);

        // 対象ファイル確認
        $file = null;
        foreach ($targets as $target) {
            if (preg_match('/' . preg_quote($target, '/') . '$/', $path)) {
                $file = $target;

                continue;
            }
        }
        if ($file === null) {
            continue;
        }

        // 対象ファイル読み込み
        $content = file_get_contents(MAIN_PATH . MAIN_APPLICATION_PATH . $file);
        if ($content === false) {
            echo '<p>' . $file . 'の読み込みに失敗しました。</p>';

            continue;
        }

        // 改行ごとに配列化
        $content = preg_replace("/\r?\n/", "\r", $content);
    	$content = preg_replace("/\r/", "\n", $content);
        $content = explode("\n", $content);

        // カバレッジ取得
        $results = [];
        $used    = 0;
        $unused  = 0;
        foreach ($content as $key => $value){
            if (isset($lines[$key + 1])) {
                if ($lines[$key + 1] === 1) {
                    $class = 'used';

                    $used++;
                } elseif ($lines[$key + 1] === -1) {
                    $class = 'unused';

                    $unused++;
                } elseif ($lines[$key + 1] === -2) {
                    $class = 'dead_code';
                }
            } else {
                $class = '';
            }

            $results[] = [
                'line'  => $key + 1,
                'code'  => $value,
                'class' => $class,
            ];
        }

        // カバレッジ出力
        echo "<h2>" . $file . "</h2>\n";
        echo "<p>Code coverage: <code>" . round(($used / ($used + $unused)) * 100, 1) . "%</code></p>\n";
        echo "<div class=\"file\">\n";
        echo "<table>\n";

        foreach ($results as $result) {
            echo "<tr>\n";
            echo "<td>" . sprintf('% 4d', $result['line']) . ":</td>\n";
            echo "<td class=\"" . $result['class'] . "\">" . t($result['code'], true) . "</td>\n";
            echo "</tr>\n";
        }

        echo "</table>\n";
        echo "</div>\n";
    }

    echo "</div>\n";

    return;
}
