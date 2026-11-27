# freo2作業用メモ(この環境)

@DEVELOPMENT.md

freo2の概要・セットアップ・ディレクトリ構成・コードの書き方・データモデル・テーマ/プラグインの作り方・テストの基本は、上で読み込んでいる [DEVELOPMENT.md](DEVELOPMENT.md)(freoにはじめて触れる人向け)にまとめてある。
このファイルには、この環境での作業で得た知見・実装例・既知の不具合・TODO・テスト基盤の内部構造だけを書く(役割分担は末尾の[運用メモ](#運用メモ)を参照)。
**このファイルに書くのは、どの作業でも確認すべきことだけ。** 機能・テストごとの記録は `.claude/` に分けてある([下記](#claude-の記録))。

**パスの読み替え:** DEVELOPMENT.md は公式リポジトリの標準の配置(`index.php` と `app/` などが直下に並ぶ形)で書いてある。この環境は DEVELOPMENT.md「2. セットアップ」の「プログラム本体を公開ディレクトリの外に置く」配置なので、次のように読み替える。

| DEVELOPMENT.md の表記 | この環境 |
| --- | --- |
| `app/`・`libs/`・`migrate/`・`plugins/`・`test/`・`themes/`・`config.default.php`・直下の `config.php` | `levis/` 配下 |
| `index.php`・`.htaccess`・`css/`・`js/`・`img/`・`files/`・`scenario/` | `html/` 配下 |
| プラグイン・テーマの静的ファイル(`plugins/<コード>/js/` など) | `html/plugins/<コード>/js/` など |
| `mail/` | `levis/mail/` |
| CLIは `index.php` のあるディレクトリで実行 | コンテナ内の `/var/www/html`(= `html/`)で実行([下記](#docker環境)) |

## Docker環境

2026-09 にXAMPP(Windows)からDocker(WSL2上のDocker Desktop)へ移行した。定義は [docker-compose.yml](docker-compose.yml) と [docker/](docker/)。

| コンテナ | 内容 |
| --- | --- |
| `php` | `php:8.4-apache` + Xdebug + Composer。リポジトリ直下を `/var/www` にマウントし、DocumentRoot `/var/www/html` が `html/` になる。`www-data` は UID/GID 1000(WSLのユーザーと同じ)なので、`html/files/`・`levis/mail/` に書き込める |
| `mysql` | MySQL 8.4。ホストにポートを公開していない(DB管理ツールから直接は繋げない) |
| `mailpit` | 開発用のメール受信サーバー(2026-10-19 追加)。SMTPは `mailpit:1025`、受信したメールは `http://localhost:8025/`。[rules/mail.md](.claude/rules/mail.md) |

- **URL:** `http://freo2.local/`(Windowsの hosts に `127.0.0.1 freo2.local` を登録済み。コンテナの中からも、`extra_hosts` で `freo2.local` が自分自身を指す。2026-10-31 にHTML出力のために追加)。`APP_HTTP_URL` もこれに合わせてある。mod_rewrite を有効にしてあるので、`/admin/` と `/index.php/admin/` のどちらでも開ける。
- **CLI:** `docker exec -w /var/www/html php php index.php <コマンド>`。単体テストは `test_exec model_categories` のように**拡張子を付けない**(`.php` を付けると `is not found`)。
- **ログ:** Apacheのアクセスログ・エラーは `docker logs php`。時刻はUTC(PHPとMySQLは `Asia/Tokyo`)。
- **DB:** `docker exec -it mysql mysql -u user -p`(root も可)。`user` には `freo2` と `freo2-test` の権限がある。
- **Xdebug:** `XDEBUG_MODE=debug,coverage`。`coverage` が無いと [coverage.php](levis/app/services/coverage.php) の `xdebug_start_code_coverage()` が警告を出す。ステップ実行用の `xdebug.client_host`(`host.docker.internal`)は未設定。
- **GD:** JPEG・WebP・FreeTypeを有効にしてビルドしている(`docker-php-ext-install gd` だけだと PNG/GIF しか扱えず、[file.php](levis/libs/modules/file.php) の `imagecreatefromjpeg()` が未定義になる)。
- **PHPの設定:** [docker/php/php.ini](docker/php/php.ini)。`php.ini-development` を元にしていないので、書いていない項目はPHPの既定値(`memory_limit` 128Mなど)。アップロード上限(`upload_max_filesize`・`post_max_size`)は20Mにしてある。
- **設定ファイルを変更したら、コンテナは作り直す。** `php.ini`・`my.cnf` はファイル単体でバインドマウントしている。エディタ(`\\wsl.localhost\...` 経由を含む)の保存はファイルを置き換えるため、`docker compose restart php` だと `error mounting ... php.ini: no such file or directory` で起動できなくなる(実際に発生した)。`docker compose up -d --force-recreate php` を使う。
- **`.bash_history`:** `www-data` のホームが `/var/www`(= リポジトリ直下)なので、`su - www-data` した後の履歴がリポジトリ直下にできる。`.gitignore` 済み。
- **PowerShellからコンテナにスクリプトを渡すとき:** `$script | docker exec -i php sh` で渡すと、1行目の先頭にBOMが付き、CRLFも残る。2026-10-31 に、1行目の変数の代入(`J=/tmp/...`)がBOMで失敗して `curl -c $J -b $J` が `curl -c -b` になり、**`html/-b`(クッキーのファイル)ができてそのままコミットに入った**。1行目は `:` だけにし、`$script.Replace("`r","")` で渡す。**最後の行の後ろにもCRLFが付く**(パイプが足す。2026-11-24 に、最後のコマンドだけが `not found` になった)ので、最後の行も `:` だけにする。PHPのスクリプトを `docker exec -i php php` に渡すときは、BOMが `<?php` の前の出力になるだけで動く。作業ディレクトリは `/var/www/html`(リポジトリの中)なので、試した後は `git status` で余分なファイルが無いかを見る。
- **Composer:** 同じ理由で、そのまま実行するとリポジトリ直下に `.composer/`(キャッシュ)ができる。`COMPOSER_HOME` をコンテナ内の `/tmp` に向けて実行する: `docker exec -e COMPOSER_HOME=/tmp/composer -w /var/www/levis/libs php composer <コマンド>`。

### その他の未対応事項

- **Gitの設定がWindowsのまま:** `.git/config` が `core.ignorecase=true`・`core.symlinks=false`。作業ツリーの多くのファイルがCRLF(リポジトリ内はLF。`core.autocrlf=input` なのでコミットはLFになる)。
- MySQL 8.4 は `ONLY_FULL_GROUP_BY` を含む strict モードだが、`app/`・`plugins/` に `GROUP BY` は無い(2026-09-14 時点)。

## この環境のプラグインと公式リポジトリの関係

`levis/plugins/` には `sample`・`markdown`・`order`・`contact`・`gallery`・`home`・`rewrite` があるが(`test` は 2026-09-26 に先輩が削除)、**公式リポジトリ(refirio/freo2)に含めるプラグインは `sample` のみ**。`contact`・`gallery`・`home`・`markdown`・`order`・`rewrite` はプラグインのリポジトリ([refirio/freo2-plugins](https://github.com/refirio/freo2-plugins))で公開している(2026-09-28 時点で反映済み)。

**`freo2-test` のプラグインの状態(2026-09-26 時点)**: `order`・`rewrite` はインストール済みで有効、`contact`・`gallery`・`home` はインストール済みで**無効**(シナリオが有効にして最後に戻すため)。`mcp`(開発中。[rules/mcp.md](.claude/rules/mcp.md))は 2026-11-10 にインストールして有効(本体の画面を差し替えないため)。`freo2` 側は先輩が画面から操作するので、ここには書かない。

**プラグインの `config.php` の `version` は、原則として上げない**(2026-10-01 先輩の判断。freo2もプラグインもアルファ版のため)。機能を足したり直したりしても、そのままにする。

単体テスト `model_plugins`・`service_plugin` は、`plugins` のうちコードに `test` を含む行だけを消す(2026-09-26 まではテーブルごと `TRUNCATE` していて、インストールしたプラグインが消えていた。[下記](.claude/rules/scenario.md#プラグインのシナリオと-plugins-テーブル))。**プラグインのコードに `test` を含めないこと。**

## `.claude/` の記録

2026-10-17 に、CLAUDE.md(約15万字になり、自動圧縮が来やすくなっていた)から機能・テストごとの記録を `.claude/` に分けた(先輩の方針)。CLAUDE.md と同じく公開用ではないので、**公開用リポジトリへのコミット差分には含めない。**

- **`.claude/rules/`:** 先頭の `paths:` に一致するファイルを読んだ・編集したときに、自動で読み込まれる。**`paths:` が無いと毎回読み込まれるので、必ず付ける。** 自動圧縮の後は、一致するファイルを次に読んだときに読み込み直される。
- **`.claude/notes/`:** 自動では読み込まれない。必要なときに読む。
- 作業の内容が `paths:` から外れる(例: パスに関係なく設計を相談する)ときは、表を見て関係するファイルを自分で読む。

| ファイル | 内容 | 主な `paths` |
| --- | --- | --- |
| [rules/unit-test.md](.claude/rules/unit-test.md) | 単体テスト(テストの一覧、テストごとの注意、`order` のテスト) | `levis/test/**` |
| [rules/scenario.md](.claude/rules/scenario.md) | シナリオテスト(ランナーの内部、ヘルパー、各シナリオの注意、既知の課題) | `html/scenario/**`・`views/test.php`・`tool/test.php` |
| [rules/test-db.md](.claude/rules/test-db.md) | テスト用DBへの自動切り替えの仕組み | `app/database.php`・`levis/test/**`・`html/scenario/**` |
| [rules/order.md](.claude/rules/order.md) | `order` プラグイン(テーブル構成、注文の状況と絞り込み) | `levis/plugins/order/**`・`html/plugins/order/**` |
| [rules/plugins.md](.claude/rules/plugins.md) | `contact`・`gallery`・`home`・`rewrite`、プラグインの入れ外し、管理画面のパスの確認 | `levis/plugins/<order 以外>/**`・`admin/plugin*`・`admin/theme*` |
| [rules/auth.md](.claude/rules/auth.md) | パスワード再設定の暗証コード、2段階認証、ログイン処理のサービス化、bcrypt 化 | `controllers/auth/**`・`views/auth/**`・`services/user.php`・`models/users.php` |
| [rules/entry-detail.md](.claude/rules/entry-detail.md) | 詳細ページの共通処理(パスワード認証・コメント・reCAPTCHA) | `controllers/{entry,page,comment,contact}/**`・`services/{comment,recaptcha,entry}.php` |
| [rules/attribute-filter.md](.claude/rules/attribute-filter.md) | 属性のフィルター | `services/attribute.php`・`auth/filter*`・`admin/attribute*`・`before.php` |
| [rules/page-tree.md](.claude/rules/page-tree.md) | ページ一覧の階層表示 | `admin/page*`・`services/entry.php` |
| [rules/log.md](.claude/rules/log.md) | 操作ログの自動削除 | `services/log.php`・`models/logs.php`・`admin/log*` |
| [rules/draft.md](.claude/rules/draft.md) | 入力中の内容の一時保存、離脱警告 | `html/js/{admin,common}.js`・`admin/{entry,page}_form.php`・`auth/logout.php` |
| [rules/admin-ui.md](.claude/rules/admin-ui.md) | 管理画面の実装パターン(項目の出し分け、プラグインのJS、一覧の表示、お問い合わせの既定の絞り込み) | `views/admin/**`・`html/js/**`・`html/plugins/**` |
| [rules/media.md](.claude/rules/media.md) | メディアのサムネイル、メディア名の確認 | `services/media.php`・`admin/media*`・`libs/modules/file.php` |
| [rules/migration.md](.claude/rules/migration.md) | 値のエスケープ、照合順序(MySQL 8 とMariaDBの違い)、DBの列の修正の記録 | `levis/migrate/**`・`plugins/*/setup/**`・`docker/mysql/**`・`libs/cores/db*.php` |
| [rules/aws-sdk.md](.claude/rules/aws-sdk.md) | AWS SDKの更新、`s3_remove()` の修正 | `libs/modules/s3.php`・`libs/composer.*`・`services/storage.php` |
| [rules/security.md](.claude/rules/security.md) | セキュリティヘッダー | `controllers/before.php`・`libs/cores/basis.php`・`html/.htaccess` |
| [rules/files.md](.claude/rules/files.md) | `html/files/` の同梱 | `html/files/**`・`.gitignore` |
| [rules/storage-protect.md](.claude/rules/storage-protect.md) | アップロードしたファイルへの直接アクセスの制限(`/storage/`、署名付きURL、`.htaccess`、バケットポリシー) | `controllers/storage/**`・`services/{storage,entry}.php`・`bootstrap.php`・`libs/modules/s3.php`・`html/.htaccess` |
| [rules/upload.md](.claude/rules/upload.md) | 管理画面のファイルのアップロード(一時ファイル、一時ファイルの掃除) | `admin/file*.php`・`admin/media_{upload,post}.php`・`services/storage.php` |
| [rules/export.md](.claude/rules/export.md) | HTML出力(静的出力)の検討と決めたこと、エントリー一覧のURLのパス化、出力の画面(`/admin/export`) | `controllers/entry/**`・`views/entry/**`・`services/{entry,export}.php`・`admin/export*` |
| [rules/mail.md](.claude/rules/mail.md) | メールのSMTP送信、Mailpit | `services/mail.php`・`libs/modules/{mail,smtp}.php`・`docker-compose.yml` |
| [rules/update.md](.claude/rules/update.md) | 管理画面からの更新(`/admin/update`。判定と振り分け、削除済みファイルの一覧、`db_migrate()` の切り出し) | `libs/cores/db.php`・`services/update.php`・`admin/update*`・`before.php`・`.github/**` |
| [rules/plugin-code.md](.claude/rules/plugin-code.md) | プラグインのコードの書き方の決まり（モデル・サービスの名前、検証はモデル、列の `COMMENT`、選択肢の置き場所、`enabled`。MCPなどが共通に扱うための前提） | `levis/plugins/**`・`html/plugins/**` |
| [rules/mcp.md](.claude/rules/mcp.md) | `mcp` プラグイン（MCP対応。設計、APIトークン、ツール、レンタルサーバーでの注意、試作の進み具合） | `levis/plugins/mcp/**`・`html/plugins/mcp/**` |
| [notes/history.md](.claude/notes/history.md) | 済んだ作業の経緯(配列の書き方を `[]` にそろえた件) | (自動では読み込まれない) |

## 運用メモ

- このCLAUDE.mdは作業内容に応じて随時更新すること。
- **CLAUDE.md には、どの作業でも確認すべきことだけを書く**(2026-10-17 先輩の方針)。機能・テストごとの知見は `.claude/rules/<対象>.md`(`paths:` 付き)に、今後参照しない経緯は `.claude/notes/` に書き、**CLAUDE.md に新しい機能の節を足さない。** 新しいファイルを作ったら、[上の表](#claude-の記録)にも足す。
  - 以下の役割分担で「CLAUDE.md に書く」とあるものは、`.claude/` を含む(公開用ではない側)。
  - `docs/` など新しいフォルダは作らない(公開に要るファイルか迷うため)。公開用ではない記録は CLAUDE.md と `.claude/` だけに置く。
- **DEVELOPMENT.md との役割分担:** freoにはじめて触れる人に必要な一般的な説明(概要・セットアップ・構成・コードの作法・データモデル・テーマ/プラグインの作り方・テストの基本)は DEVELOPMENT.md に、この環境での作業記録・実装例・既知の不具合・TODO・テスト基盤の内部構造は CLAUDE.md に書く。
  - **同じ内容を両方に書かない。** 作業中に一般的な作法が分かったら DEVELOPMENT.md に書き、CLAUDE.md からは「DEVELOPMENT.md「<節>」を参照」とだけ書く。
  - DEVELOPMENT.md に環境固有の情報(ローカルのURL・パス、作業履歴、未対応の不具合)を書かない。
  - DEVELOPMENT.md は公式リポジトリに含めている(2026-09-28 時点で反映済み)ので、パスは公式リポジトリの標準の配置で書く(この環境の `levis/`・`html/` を付けない)。
  - 公式リポジトリに同梱するプラグインは `sample` のみなので、DEVELOPMENT.md では `order`・`markdown` など他のプラグインを例や参照先にしない(別リポジトリなのでファイルへリンクできない)。`sample` に無い機能は汎用のコード例(`myplugin` など)で説明する。freo2-plugins はリポジトリのトップへのリンクだけにする。
  - 設置・テーマ・プラグインの手順は、公式サイト(https://freo.jp/freo2/)を正とする。
- **README.md との役割分担:** README.md は**公式リポジトリの入口**(2026-09-26 に内容を足した)。何であるか・アルファ版であること・主な特徴(3つだけ)・動作環境・設置方法(公式サイトへ)・プラグイン(freo2-plugins へ)・ライセンス。
  - **機能の一覧は書かない**(OVERVIEW.md)、**構成や作り方も書かない**(DEVELOPMENT.md)、**設置手順も書かない**(公式サイト)。READMEが3つめの解説になると更新漏れが起きる。
  - OVERVIEW.md と DEVELOPMENT.md は「ドキュメント」の節からリンクする(2026-09-28、2つが公式リポジトリに入ったので追加。それまでは含める時期が未決定のためリンクしていなかった)。
- **プラグインのリポジトリ向けドキュメント(`PLUGIN-` で始まる2ファイル):** どちらも**プラグインのリポジトリ([freo2-plugins](https://github.com/refirio/freo2-plugins))に置いている利用者向けドキュメント**(2026-09-26 作成、2026-09-28 時点で反映済み)。**ここが正本で、編集はこちらで行い、反映するときに接頭辞の `PLUGIN-` を外して置く。**

  | 開発用リポジトリ | 反映先 | 内容 |
  | --- | --- | --- |
  | [PLUGIN-README.md](PLUGIN-README.md) | `README.md` | リポジトリの説明と**導入のしかた**(配置 → インストール → 有効化、公開ファイル、アップグレード)。一覧は PLUGINS.md へリンク |
  | [PLUGIN-PLUGINS.md](PLUGIN-PLUGINS.md) | `PLUGINS.md` | **どんなプラグインがあるか**(選び方の表と、プラグインごとの「できること / 増える画面 / 設定 / 使うときの注意」) |

  - **導入のしかたはREADME側にだけ書く。** PLUGINS.md は冒頭でREADMEを参照するだけにして、手順を二重に書かない。
  - **プラグインを追加・改修したら、同じ作業の中で PLUGIN-PLUGINS.md も更新する**(2026-09-26 先輩の指示)。OVERVIEW.md と同じ基準で、**利用者から見えるもの**(できること・増える画面・設定項目・使うときの注意)が変わったときが対象。内部の修正だけなら更新しない。
    - 新しいプラグインを足したときは、「どれを使うか」の表と、性格の合うグループ(コンテンツを増やす / 表示を変える / URLを扱う)に節を足す。**配布しないもの(`test` など)は載せない。**
    - 導入のしかたが変わったときだけ PLUGIN-README.md も直す。
  - 将来ドキュメントが増えるなら、接頭辞よりフォルダにまとめる方が(リネームせずに移せて)楽になる。2ファイルのうちは接頭辞で足りる。
  - **プラグインの詳しい説明は `config.php` の `detail` と `setting_define` が正**(管理画面のプラグイン詳細画面に出る)。PLUGIN-README.md は索引として、要点だけを書いて写しを作らない。
  - **バージョンと更新日は書かない**(`config.php` が正で、プラグインは本体と別に上がるためずれる)。freo2がアルファ版のうちは対応バージョンも書かない。
  - `sample` は本体に同梱なので「本体に同梱」と1行だけ触れる。`test`(この環境での検証用。2026-09-26 に削除済み)は載せなかった。
  - **プラグインの解説は1ファイルにまとめる**(2026-09-26 先輩の判断。本体・プラグインのリポジトリを直接触らずに開発用リポジトリで作業しているため、ファイル数を増やさない)。後でプラグインごとに分ける可能性はある。
  - **本体のリポジトリのファイルへは、GitHubの絶対URLでリンクする**(別リポジトリなので相対リンクは使えない)。今リンクしているのは `DEVELOPMENT.md`(`https://github.com/refirio/freo2/blob/main/DEVELOPMENT.md`)だけ。本体でファイル名や置き場所を変えたら、このリンクも直す。
- **OVERVIEW.md との役割分担:** OVERVIEW.md は、導入を検討する人・サイトの運営者向けの「freo2で何ができるか」(2026-09-25 作成)。機能を利用者の目線で説明し、テーブル名・URL・関数名などの作りの説明は DEVELOPMENT.md に書く。設置手順は公式サイトへのリンクだけにする。
  - DEVELOPMENT.md と同じく公式リポジトリに含めているので、環境固有の情報を書かない。freo2-plugins のプラグインの機能は、「テーマとプラグイン」の節で代表的なものを挙げてリポジトリへ案内するだけにする(詳しくは PLUGIN-PLUGINS.md に書く)。
  - **機能を追加・変更・削除したら、DEVELOPMENT.md と同じく OVERVIEW.md も同じ作業の中で更新する。** 画面から見える機能・設定項目・権限が変わるときが対象。内部の修正だけなら更新しない。
