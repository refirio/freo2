<?php

// コマンドラインからだけ実行できる（php index.php /cli/<コマンド>）
if (!cliurl()) {
    error('不正なアクセスです。');
}
