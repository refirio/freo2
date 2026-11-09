# freo2 開発ガイド(はじめて触る人向け)

freo2 のコードをはじめて読む・改修する・プラグインやテーマを作る人向けの案内です。
「どこに何があるか」「1リクエストがどう処理されるか」「どう書くのがこのコードベースの作法か」を、実際のファイルを示しながら説明します。

- 公式サイト: https://freo.jp/freo2/
  - [設置方法](https://freo.jp/freo2/setup/) / [開発環境構築方法](https://freo.jp/freo2/develop/) / [テーマ](https://freo.jp/freo2/theme/) / [プラグイン](https://freo.jp/freo2/plugin/)
  - このガイドと公式サイトの内容が食い違う場合、設置・テーマ・プラグインの手順は公式サイトを正とします
- freo2 で何ができるか(機能の紹介): [OVERVIEW.md](OVERVIEW.md)
- すぐに使えるプラグイン: https://github.com/refirio/freo2-plugins
- フレームワーク(levis): https://refirio.org/levis/
- ライセンス: MIT

このガイドのパスは、リポジトリの標準の配置(`index.php` と `app/`・`libs/` などが同じ階層に並ぶ形)で書いています。

---

## 目次

1. [freo2 とは](#1-freo2-とは)
2. [セットアップ](#2-セットアップ)
3. [ディレクトリ構成](#3-ディレクトリ構成)
4. [リクエストの流れ](#4-リクエストの流れ)
5. [コードの書き方](#5-コードの書き方)
6. [データモデル](#6-データモデル)
7. [画面とURL](#7-画面とurl)
8. [設定・メニュー・ウィジェット](#8-設定メニューウィジェット)
9. [テーマ](#9-テーマ)
10. [プラグイン](#10-プラグイン)
11. [データベースのマイグレーション](#11-データベースのマイグレーション)
12. [デバッグと開発ツール](#12-デバッグと開発ツール)
13. [テスト](#13-テスト)
14. [はまりやすい点](#14-はまりやすい点)

---

## 1. freo2 とは

freo2(フレオ)は、独自の軽量PHPフレームワーク **levis** の上に作られた汎用CMSです。

中心にあるのは次の3つの組み合わせです。

| 概念 | テーブル | 役割 |
| --- | --- | --- |
| 型 | `types` | コンテンツの種類。初期状態で `entry`(エントリー=ブログ記事的なもの)と `page`(ページ)がある |
| エントリー | `entries` | すべてのコンテンツの本体。型が違っても同じテーブルに入る |
| フィールド | `fields` / `field_sets` | 型ごとに追加できる入力項目(カスタムフィールド) |

ブログ記事も固定ページも、プラグインが独自の型で追加するコンテンツも、すべて `entries` の1行です。
本体に手を加えずに、プラグインで機能を足せる作りになっています(→ [10. プラグイン](#10-プラグイン))。

**levis はクラスを使わない、関数ベースのフレームワークです。** 名前空間やオートローダー、DIコンテナはありません。
ファイルを `import()` で読み込み、`model('select_xxx', ...)` のような関数呼び出しと、`$_view` などのグローバル変数でデータを受け渡します。
Laravel などに慣れていると最初は戸惑いますが、処理はファイルを上から読めば追える単純な作りです。

---

## 2. セットアップ

公式の手順は [設置方法](https://freo.jp/freo2/setup/) と [開発環境構築方法](https://freo.jp/freo2/develop/) にあります。
ここでは、その要点をまとめます。

### 必要な環境

- PHP 8 以上
- MariaDB 10 以上、もしくは MySQL 8 以上
- (任意)Apache の `mod_rewrite`: 有効なら URL から `index.php` を省略できる(`.htaccess`)
- (任意)PHP の GD: **メディアに画像を登録したときのサムネイルの作成にだけ使います。** 無効でもエラーにはならず、サムネイルが作られないだけです(一覧ではアイコンで表示されます)。GD が有効でも、ビルドによっては JPEG を扱えないことがあるため、**形式ごとに `imagecreatefromjpeg()` などの有無を確認しています**。扱える形式は、管理画面の「バージョン情報」(`/admin/version`)で確認できます

### 手順

1. **データベースを作る**
   MariaDB または MySQL で、文字コード `utf8mb4` のデータベースを作成します(例: `freo2`)。

2. **プログラムを置く**
   https://github.com/refirio/freo2 からプログラムを取得し、`index.php` などのファイルを公開ディレクトリ(またはその中のサブディレクトリ)に置きます。

3. **書き込み権限を付ける**
   `files/` 内の各ディレクトリのパーミッションを `0777` にして、PHPから読み書きできるようにします。XAMPP などでは設定が不要な場合もあります。

4. **設定ファイルを作る**
   `config.default.php` を複製して、同じ階層に `config.php` を作ります。

   **データベース:**

   ```php
   define('DATABASE_HOST', 'localhost');
   define('DATABASE_PORT', '');
   define('DATABASE_USERNAME', 'username');
   define('DATABASE_PASSWORD', 'password');
   define('DATABASE_NAME', 'freo2');
   ```

   **設置URL**(本番。公式の設置方法の例):

   ```php
   // https://example.net/ に設置する場合
   define('APP_HTTP_URL', 'https://example.net');
   ```

   ```php
   // 公開ディレクトリ直下の freo2 ディレクトリに設置する場合
   define('APP_HTTP_URL', 'http://example.net');
   define('APP_HTTP_PATH', '/freo2/');
   define('APP_STORAGE_TYPE', 'file');
   define('APP_STORAGE_URL', APP_HTTP_URL . '/freo2/');
   ```

   **設置URLとメール**(開発環境。公式の開発環境構築方法の例。`http://localhost/freo2/` に設置し、URL に `index.php` を含める):

   ```php
   define('APP_HTTP_URL', 'http://localhost/freo2/index.php');
   define('APP_STORAGE_TYPE', 'file');
   define('APP_STORAGE_URL', 'http://localhost/freo2');
   define('APP_MAIL_SEND', null);
   define('APP_MAIL_LOG', true);
   ```

   `APP_MAIL_SEND` を `null`、`APP_MAIL_LOG` を `true` にすると、システムはメールを実際には送らず、`mail` フォルダ内に送信内容をテキストファイルで記録します。**`index.php` と同じ階層に `mail` フォルダを作成しておきます。**

5. **テーブルを作る**
   `?_mode=info_levis` を開き、「levis: PHP Framework」という画面が表示されることを確認します。
   開発環境の例では `http://localhost/freo2/index.php/?_mode=info_levis` です。
   「Menu」の「migrate」をクリックし、ページ下部の「status」がすべて「success」になっていることを確認します。

6. **ログインして初期設定をする**
   トップページのメニューにある「ログイン」をクリックし、次の情報でログインするとダッシュボードが表示されます。

   - ユーザー名: `admin`
   - パスワード: `abcd1234`

   画面右上の「管理者さん」→「ユーザー情報編集」で情報を更新します。**パスワードは必ず変更します。ユーザー名の変更も推奨です。**
   続いて、画面左のメニューの「設定」→「基本設定」で、サイト名などを設定します。

7. **動作を確認する**
   最低限、次の2点を確認します。

   - 管理画面でデータを登録できるか
   - お問い合わせからメールを送信できるか

### 本番環境に置くとき(セキュリティ設定)

**公開サーバーに設置する場合は、必ず設定してください。**

- `DEBUG_LEVEL` を `0` にします。`?_mode=info_levis` へのアクセスが禁止され、データベース管理などの開発ツールも使えなくなります。
  - データベースの内容を直接編集したい場合は、別途データベース管理ツールを導入します。
  - マイグレーションは、SSHで `index.php` のあるディレクトリに移動して `php index.php db_migrate` を実行します。
- SSHでのアクセスが難しい場合(レンタルサーバーなど)は、`DEBUG_PASSWORD` に半角英数字で任意のパスワードを設定します。`?_mode=info_levis` を開くときにパスワードが必要になります。

### プログラム本体を公開ディレクトリの外に置く

公式の設置方法の補足にある配置です。PHPのプログラムを、ブラウザから直接開けない場所に置けます。

```
home/
├── html/     公開ディレクトリ: index.php, .htaccess, css/, files/, img/, js/, scenario/, themes/sample/css/public.css
└── levis/    config.default.php, app/, libs/, migrate/, plugins/, test/, themes/
```

1. 公開ディレクトリと同じ階層に `levis` ディレクトリを作り、`config.default.php`・`app`・`libs`・`migrate`・`plugins`・`test`・`themes` をその中に移します(`.gitignore` と `README.md`・`OVERVIEW.md`・`DEVELOPMENT.md` は無くても動作に支障はありません)。
2. `html/index.php` の `require_once 'config.php';` を `require_once '../levis/config.php';` に変えます。
3. `levis/config.default.php` を複製して `levis/config.php` を作り、パスを設定します。以降は通常の手順と同様に、データベースなどを設定します。

   ```php
   define('MAIN_LIBRARY_PATH', '../levis/');
   define('MAIN_APPLICATION_PATH', '../levis/');
   define('DATABASE_MIGRATE_PATH', '../levis/migrate/');
   define('DATABASE_SCAFFOLD_PATH', '../levis/scaffold/');
   define('DATABASE_BACKUP_PATH', '../levis/backup/');
   define('PAGE_PATH', '../levis/page/');
   define('TEST_PATH', '../levis/test/');
   define('LOGGING_PATH', '../levis/log/');
   ```

4. プラグインとテーマに公開すべきファイル(CSS・JavaScriptなど)が含まれていれば、**そのファイルだけ**を公開ディレクトリ内の同じパスに移します。
   例: `sample` テーマの `themes/sample/css/public.css` は、`html/themes/sample/css/public.css` に移します。

この配置では、`mail` フォルダは `levis/mail/` に作ります(記録先が `MAIN_APPLICATION_PATH . 'mail/'` のため)。CLI のコマンドは `html/` で実行します。

### アップロードファイルの保存先に S3 を使う

1. S3 バケットと、必要な権限を持つアクセスキーを用意します。
2. `import('libs/vendor/autoload.php');` で読み込めるように、AWS SDK を `libs/vendor/` に配置します(例: `composer require aws/aws-sdk-php`)。
3. `config.php` を次のように設定します。

   ```php
   define('APP_STORAGE_TYPE', 's3');
   define('APP_STORAGE_URL', APP_HTTP_URL . '/');
   define('APP_AWS_CREDENTIAL_KEY', 'XXXXXXXXXX');
   define('APP_AWS_CREDENTIAL_SECRET', 'YYYYYYYYYY');
   define('APP_AWS_REGION', 'ap-northeast-1');
   define('APP_AWS_VERSION', 'latest');
   define('APP_AWS_BUCKET', 'ZZZZZZZZZZ');
   ```

---

## 3. ディレクトリ構成

```
freo2/
├── index.php               エントリーポイント(全リクエストがここを通る)
├── .htaccess               URLから index.php を省略するためのリライト
├── .github/workflows/      リリース用の GitHub Actions
├── config.default.php      設定ファイルのひな形 → config.php にコピーして使う
├── README.md               リポジトリの説明
├── OVERVIEW.md             freo2 で何ができるか(機能の紹介)
├── DEVELOPMENT.md          このガイド
├── css/ js/ img/           本体用の静的ファイル(jQuery など)
├── files/                  アップロードファイルの保存先(要書き込み権限)
├── scenario/               ブラウザで動かすシナリオテスト
├── libs/
│   ├── cores/              フレームワークの中核(ルーティング、DB、テストランナー)
│   ├── modules/            汎用モジュール(バリデーター、メール、ファイル、S3 など)
│   └── vendor/             Composer で入れた外部ライブラリ(AWS SDK など)
├── app/                    freo2 本体のアプリケーションコード
│   ├── bootstrap.php       起動時に読み込まれる共通関数
│   ├── config.php          アプリの設定値(選択肢のラベル、アップロード可能な形式など)
│   ├── setting.php         管理画面「設定」の項目定義
│   ├── menu.php            各画面のメニュー定義
│   ├── string.php          画面の文言
│   ├── controllers/        コントローラー
│   ├── models/             モデル(1テーブル = 1ファイル)
│   ├── services/           サービス(モデル操作 + 操作ログ・排他制御など)
│   └── views/              ビュー(PHPテンプレート)
├── migrate/                本体のマイグレーションSQL
├── plugins/                プラグイン(同梱は最小例の sample のみ。JS・CSS も各プラグインのディレクトリに入る)
├── themes/                 テーマ(sample など。CSS も各テーマのディレクトリに入る)
└── test/                   単体テスト
```

設定ファイルは2つあるので注意してください。直下の `config.php`(`config.default.php` から作る)は設置環境ごとの定数、`app/config.php` はアプリの設定値です(→ [8. 設定・メニュー・ウィジェット](#8-設定メニューウィジェット))。

プログラム本体を公開ディレクトリの外に置く配置にもできます(→ [2. セットアップ](#プログラム本体を公開ディレクトリの外に置く))。

### `libs/` は編集しない

**`libs/` の中は、原則として編集しません。** フレームワーク [levis](https://github.com/refirio/levis) のファイルで、levis を更新すると上書きされるためです。修正が必要になったら、levis 側で対応します。

ただし `libs/modules/` の次の6つは、**freo2 のために追加したモジュール**です。levis には含まれないので、freo2 側で修正してかまいません。

| ファイル | 内容 |
| --- | --- |
| `environment.php` | ユーザーエージェントからの環境判定 |
| `loader.php` | CSS・JavaScript の読み込み(キャッシュ回避のため、更新日時をクエリに付ける) |
| `recaptcha.php` | reCAPTCHA |
| `s3.php` | Amazon S3 |
| `smtp.php` | SMTP でのメールの送信 |
| `validator.php` | 入力値の検証 |

`libs/cores/` のすべてと、`libs/modules/` のそれ以外(`cookie.php`・`directory.php`・`file.php`・`hash.php`・`mail.php`・`string.php`・`ui.php`)は levis のファイルです。

---

## 4. リクエストの流れ

### URL とファイルの対応

URL は `/<mode>/<work>/<以降のパラメーター>` という形です。

| URL | コントローラー | ビュー |
| --- | --- | --- |
| `/` | `app/controllers/home/index.php` | `app/views/home/index.php` |
| `/entry/` | `app/controllers/entry/index.php` | `app/views/entry/index.php` |
| `/entry/detail/hello` | `app/controllers/entry/detail.php` | `app/views/entry/detail.php` |
| `/admin/category` | `app/controllers/admin/category.php` | `app/views/admin/category.php` |
| `/admin/category_form?id=3` | `app/controllers/admin/category_form.php` | `app/views/admin/category_form.php` |

- `mode` を省略すると `home`、`work` を省略すると `index` になります。
- `mode`/`work` 以降の部分は `$_params` 配列に入ります(`/entry/detail/hello` なら `$_params[2]` が `hello`)。
- `mod_rewrite` が無い環境では `/index.php/entry/` のように `index.php` を挟みます。ビューでリンクを作るときは、どちらの環境でも動くよう定数 `MAIN_FILE` を前に付けます(`<?php t(MAIN_FILE) ?>/admin/category`)。
- 管理画面は `admin` という1つの mode の中で、`work` 名を `<対象>`/`<対象>_form`/`<対象>_post`/`<対象>_delete` と分けて画面を並べています。

### 1リクエストの処理順

`index.php` → [libs/cores/main.php](libs/cores/main.php) の順に、次の処理が上から実行されます。

```
bootstrap()    app/bootstrap.php を読み込む(共通関数・設定値)
session()      セッション開始
database()     DB接続(app/database.php があれば読み込む)
normalize()    $_GET/$_POST などの NULL バイト除去・改行コード統一
routing()      URL を解析して $_REQUEST['_mode'] / ['_work'] と $_params を決める
model()        app/models/*.php をすべて読み込む
controller()   ↓ の順にコントローラーを読み込む
                 app/controllers/before.php
                 app/controllers/before_<mode>.php   (例: before_admin.php = ログイン必須チェック)
                 app/controllers/<mode>/<work>.php
                 app/controllers/after_<mode>.php
                 app/controllers/after.php
view()         app/views/<mode>/<work>.php を読み込む
```

**[app/controllers/before.php](app/controllers/before.php) が全画面共通の初期化を担っています。** ここを読むと全体像がつかめます。

1. `settings` テーブルを `$GLOBALS['setting']` に読み込む
2. 自動ログイン、ログインユーザーと権限の確認(権限外の管理画面は `error()` で止める)
3. メニュー・文言・設定項目の定義(`app/menu.php`, `string.php`, `setting.php`)とウィジェットを読み込む
4. 有効なテーマを読み込む
5. 有効なプラグインの `config.php` と `app/bootstrap.php` を読み込む
6. **プラグインに該当URLのコントローラーがあれば、それを実行してビューを表示し、その場で終了する**

最後の6があるので、プラグインは本体に手を加えずに新しいURLを追加できます(→ [10. プラグイン](#10-プラグイン))。

---

## 5. コードの書き方

**配列は `[]` で書きます**(`array()` は使いません)。`libs/` の中は levis のファイルなので `array()` のままですが、`app/` と `plugins/` は `[]` にそろえています(→ [3. ディレクトリ構成](#libs-は編集しない))。

**日本語のコメントで補足を括弧に入れるときは、全角の `（` `）` を使います**(`// パスワード認証（失敗したら、失敗した回数を記録する）`)。関数呼び出し(`cookie_set()`)や記法の例(`[text](url)`)など、コードとして書く部分は半角のままです。

### 5.1 よく使うグローバル変数

| 変数 | 内容 |
| --- | --- |
| `$_view` | コントローラーからビューへ渡すデータ。`$_view['title']` など |
| `$_params` | URL をスラッシュで分割した配列 |
| `$_REQUEST['_mode']` / `['_work']` | 現在の mode / work |
| `$_REQUEST['_type']` | 応答形式。`json` なら `ok()`/`warning()`/`error()` がJSONを返す |
| `$GLOBALS['config']` | [app/config.php](app/config.php) の設定値(選択肢のラベルなど) |
| `$GLOBALS['setting']` | 管理画面「設定」で保存された値(`settings` テーブル) |
| `$GLOBALS['authority']` | ログインユーザーの権限。`power` は 3=管理者 / 2=投稿者 / 1=閲覧者 / 0=ゲスト |
| `$GLOBALS['plugin'][<コード>]` | プラグインの情報と設定値 |

> **注意:** `routing()` は `$_REQUEST` を `_mode`/`_work`/`_type`/`_token`/`_test` だけの配列で**上書き**します。フォームの値は `$_REQUEST` ではなく `$_GET`/`$_POST` から読んでください。

### 5.2 ファイルの読み込み: `import()`

```php
import('app/services/category.php');
import('libs/modules/validator.php');
```

- パスは、設置ディレクトリ(`app/` や `libs/` がある場所)からの相対パスで書きます。
- **モデルはすべて自動で読み込まれますが、サービスは自動では読み込まれません。** 使うコントローラーの先頭で `import()` します。
- テーマが有効な場合、`import()` はテーマ内に同じパスのファイルがあればそちらを優先して読み込みます(→ [9. テーマ](#9-テーマ))。

### 5.3 モデル

`app/models/<テーブル名>.php` に、1テーブル分の関数をまとめます。参考: [app/models/categories.php](app/models/categories.php)

| 関数 | 役割 |
| --- | --- |
| `select_<テーブル>($queries, $options)` | 取得 |
| `insert_<テーブル>($queries, $options)` | 登録(`created`/`modified` を自動で埋める) |
| `update_<テーブル>($queries, $options)` | 更新(`modified` を自動で埋める) |
| `delete_<テーブル>($queries, $options)` | 削除(既定は論理削除) |
| `normalize_<テーブル>($queries)` | 入力値の整形(全角数字→半角など) |
| `validate_<テーブル>($queries, $options)` | 入力値の検証。エラーメッセージの配列を返す(空なら正常) |
| `default_<テーブル>()` | 新規登録フォームの初期値 |

呼び出すときは関数を直接呼ばず、`model()` を通します。

```php
$categories = model('select_categories', [
    'where'    => [
        'categories.type_id = :type_id',
        [
            'type_id' => $_GET['type_id'],
        ],
    ],
    'order_by' => 'categories.sort, categories.id',
    'limit'    => 10,
], [
    'associate' => true,
]);
```

- **クエリは配列で組み立てます。** 取得は `select`/`from`/`where`/`group_by`/`having`/`order_by`/`offset`/`limit`、登録は `values`、更新は `set`/`where` です。
- **ユーザーの入力値は、必ずプレースホルダーで渡します。** `where` に `[SQL, [名前 => 値]]` の形で書くと、`:名前` の部分がエスケープされて埋め込まれます。
- `values`/`set` の値は自動でエスケープされます。**SQL式をそのまま書きたいときは配列にします**(`'code' => ['CONCAT(\'DELETED \', code)']`)。
- **`values`/`set` に空文字 `''` を渡すと `NULL` になります。**
- 取得で `'associate' => true` を付けると、関連テーブルを JOIN した結果が返ります(`categories` なら `types.name AS type_name` など)。`associate` は取得だけのオプションで、削除には使いません(下記)。
- **削除は論理削除です。** `deleted` 列に日時が入り、`select_*` は削除済みの行を返しません。重複チェックでぶつからないよう、`code` などの先頭には `DELETED <日時> ` が付きます。物理削除したいときは `['softdelete' => false]` を渡します。
  - 接頭辞が付くのは**重複を確認する列だけ**です(`code`、ユーザーは `username` と `email`)。そのため、**削除したものと同じコードやメールアドレスで登録し直せます。**
  - **元に戻す仕組みはありません。** `deleted` を空に戻す処理は本体にないので、論理削除は「一覧から隠して記録を残す」ためのものと考えてください。
  - ひも付け(`category_sets` など)は、**既定で一緒に物理削除**されます。消すかどうかは、ひも付けのテーブル名から `_sets` を取った名前のオプションで指定します(`entries` なら `'field'`・`'category'`・`'attribute'`、`categories` なら `'category'`)。残したいときだけ `false` を渡します(`['category' => false]`)。
  - 自分で `delete_<テーブル>()` を書いて関連データも消すときは、同じ形のオプション(既定 `true`)にし、**削除する行が無いときは関連データの削除を飛ばします**(`if ($options['category'] === true && !empty($ids))`)。IDが空のまま `IN()` を組み立てると、SQL の構文エラーになります。
- `app/models/` にファイルを置くと、`select_`/`insert_`/`update_`/`delete_`/`normalize_`/`validate_` のうち**定義していないものは、ファイル名をテーブル名とみなして自動で作られます。**

### 5.4 サービス

`app/services/<名前>.php` に `service_<名前>_<操作>()` を定義します。参考: [app/services/category.php](app/services/category.php)

サービスはモデルを呼ぶ前後に、アプリとして必要な処理を足します。

- 操作ログの記録(`service_log_record()` → `logs` テーブル)
- 排他制御(編集画面を開いた後に、他の人がデータを更新していないかの確認)
- 失敗したら `error()` で止める

**画面から登録・更新・削除するときはサービスを使います。**
取得だけならモデルを直接呼んでかまいません。ただし公開側でエントリーを取得するときは、公開範囲を判定する `service_entry_select_published()` を使います(→ [6. データモデル](#6-データモデル))。

### 5.5 コントローラー: 登録・編集の定型

管理画面の登録・編集は、どの画面も同じ流れで書かれています。カテゴリーの例で説明します。

```
一覧                /admin/category          一覧を取得して表示
  ↓「登録」「編集」
入力フォーム(GET)  /admin/category_form     初期値(新規)または既存データを表示し、編集開始日時をセッションに記録
  ↓ 送信
入力フォーム(POST) /admin/category_form     ワンタイムトークン → アクセス元 → 整形 → 検証
  ├ エラーあり → そのままフォームを再表示(エラーメッセージ付き)
  └ エラーなし → $_SESSION['post'] に保存して forward('/admin/category_post')
登録処理            /admin/category_post     トランザクション内でサービスを呼んで登録・更新
  ↓ redirect('/admin/category?ok=post')
一覧                                         完了メッセージを表示
```

- [category_form.php](app/controllers/admin/category_form.php): 入力と検証
- [category_post.php](app/controllers/admin/category_post.php): 登録処理。冒頭の `if (forward() === null) { error(...); }` で、**URLを直接開いたアクセスを拒否しています。**
- 検証は、送信前にも実行されます。フォームに `class="validate"` を付けると、[js/common.js](js/common.js) が `_type=json` を付けて同じURLにAjaxで送信します。コントローラーは `ok()`/`warning()` でJSONを返し、エラーは各入力欄の下に表示されます。

新しい管理画面を作るときは、既存の一式(`category.php` / `category_form.php` / `category_post.php` / `category_delete.php` とビュー)を写して書き換えるのが確実です。

公開側のお問い合わせ・コメントは、`index`(入力)→ `preview`(確認)→ `post`(登録)→ `complete`(完了)の順に画面が分かれています。

**公開側だけで必須にしたい項目は、モデルではなくコントローラーで確認を足します。** モデルの `validate_<テーブル>()` は管理画面と公開側の両方から呼ばれるので、そこで必須にすると、管理画面から登録するときまで入力が必要になります。コントローラーで `validate_<テーブル>()` を呼んだ後に、警告の配列へ足します。

```php
$warnings = model('validate_comments', $post['comment']);

// 公開側ではURLを必須にする(管理画面からの登録では任意のまま)
if (!isset($warnings['url']) && !validator_required($post['comment']['url'])) {
    $warnings['url'] = 'URLが入力されていません。';
}
```

### 5.6 ビュー

PHPテンプレートです。共通のヘッダー・フッターは、画面ごとに `import()` で読み込みます。

```php
<?php import('app/views/admin/header.php') ?>

    <h2><?php h($_view['title']) ?></h2>
    <a href="<?php t(MAIN_FILE) ?>/admin/category_form?id=<?php t($category['id']) ?>">編集</a>

<?php import('app/views/admin/footer.php') ?>
```

**出力には必ずエスケープ関数を使います。**

| 関数 | 処理 | 使いどころ |
| --- | --- | --- |
| `t($s)` | HTMLエスケープ | 属性値、URL、1行のテキスト |
| `h($s)` | HTMLエスケープ + 改行を `<br>` に変換 | 複数行のテキスト |
| `e($s)` | 何もしない | 管理者が入力したHTML(ウィジェット、WYSIWYG本文)など、**信頼できる値だけ** |

第2引数に `true` を渡すと、出力せずに文字列を返します(`t($s, true)`)。

**エントリーの画像・フィールドのファイルのURLは、`app_file_url()`([app/bootstrap.php](app/bootstrap.php))で組み立てます。** 設定 `APP_STORAGE_PROTECT` によって、保存先のURLにも、公開範囲で判定する `/storage/` のURLにもなるためです(→ [5.8](#58-セキュリティ))。テーマやプラグインのビューでも、保存先のURLを直接組み立てないでください。

```php
<img src="<?php t(app_file_url('entry', $entry['id'], $entry['thumbnail'])) ?>">
<a href="<?php t(app_file_url('field', $entry['id'] . '_' . $field['id'], $entry['field_sets'][$field['id']])) ?>">
```

公開側・会員向け(`auth`)・管理画面(`admin`)で、ヘッダー・フッターが別々に用意されています。
管理画面は Bootstrap 5 のクラスでレイアウトされています。

### 5.7 処理を終える関数

次の関数は、**呼んだ時点でスクリプトが終了します(`exit`)。**

| 関数 | 用途 |
| --- | --- |
| `redirect('/admin/category')` | リダイレクト。先頭が `/` なら `MAIN_FILE` を自動で付ける |
| `forward('/admin/category_post')` | リダイレクトせずに、別のコントローラーとビューを実行する |
| `ok($message)` | 成功を返す(トランザクションはコミット)。主に `_type=json` 用 |
| `warning($messages)` | 入力エラーなどを返す(ロールバック) |
| `error($message)` | エラー画面を表示する(ロールバック) |

### 5.8 セキュリティ

- **CSRF対策:** フォームには `<input type="hidden" name="_token" value="<?php t($_view['token']) ?>">` を入れ、POST を受けた側で `token('check')` を確認します。`$_view['token']` は [app/controllers/after.php](app/controllers/after.php) が全画面で用意します。
- **アクセス元の確認:** 管理画面の POST では、`HTTP_REFERER` が `APP_HTTP_URL` から始まるかも確認しています。
- **権限:** 管理画面の権限チェックは `before.php` に `work` 名の正規表現でまとまっています。投稿者(power 2)が触れない画面を増やすときは、ここに追記します。
- **SQL:** 値はプレースホルダーで渡します(→ [5.3](#53-モデル))。
- **出力:** エスケープ関数を使います(→ [5.6](#56-ビュー))。
- **クッキー:** セッション(`PHPSESSID`)と、`cookie_set()` で発行するクッキー(ログイン状態を保持する `auth[session]` など)には、`HttpOnly` と `SameSite=Lax` が付きます(SameSite は PHP 7.3 以上のとき)。`Secure` は HTTPS で開いているときに付き、ロードバランサーなどで HTTPS を終えている構成でも `X-Forwarded-Proto: https` で判定します。`cookie_set()` で発行するクッキーの `Secure` は、`config.php` の `APP_COOKIE_SECURE` で固定することもできます。
- **セキュリティヘッダー:** [app/controllers/before.php](app/controllers/before.php) が、すべての画面で次のヘッダーを送ります(値は [app/config.php](app/config.php) の `security_headers`)。あわせて、PHP のバージョンが分かる `X-Powered-By` を消します。

  | ヘッダー | 値 | 目的 |
  | --- | --- | --- |
  | `X-Content-Type-Options` | `nosniff` | ブラウザがファイルの種類を中身から推測しないようにする |
  | `X-Frame-Options` | `SAMEORIGIN` | ほかのサイトの iframe に埋め込ませない(クリックジャッキング対策) |
  | `Content-Security-Policy` | `frame-ancestors 'self'` | 同上(今のブラウザ向け)。スクリプトの読み込み元などは制限しません |
  | `Referrer-Policy` | `strict-origin-when-cross-origin` | ほかのサイトへは URL ではなくドメインだけを送る |

  - 変えたいときは `config.php` で `APP_SECURITY_HEADERS` を配列ごと定義します(空の配列にすると送りません)。たとえば freo2 の画面をほかのサイトに埋め込みたいなら、`X-Frame-Options` と `Content-Security-Policy` を外します。
  - **HSTS(`Strict-Transport-Security`)は、既定では送りません。** `config.php` で `define('APP_HSTS_MAX_AGE', 31536000);` のように秒数を指定すると、HTTPS で開いているときだけ送ります。一度送るとブラウザがその期間 HTTPS でしか開かなくなるので、HTTPS で問題なく運用できることを確かめてから、短い期間で始めてください。
  - **静的なファイル(CSS・画像・アップロードしたファイル)には付きません。** PHP を通らないためです。付けたいときは、Web サーバーの設定(Apache の `mod_headers` など)で付けます。
- **アップロードしたファイルへの直接アクセス:** 既定では、エントリーの画像・フィールドのファイルは保存先のURL(`files/entries/<エントリーID>/<ファイル名>` など)をそのまま出します。**エントリーを公開範囲で制限しても、画像のURLを知っていれば誰でも開けます。** 作品など、画像そのものを制限したいときは、`config.php` で `define('APP_STORAGE_PROTECT', true);` にします。
  - 有効にすると、画像のURLが `/storage/entry/<エントリーID>/<ファイル名>`(フィールドは `/storage/field/<エントリーID>_<フィールドID>/<ファイル名>`)になり、エントリーの公開側と同じ判定(`service_entry_select_published()`)で、見られる人にだけ返します。パスワード認証の前は、画像もフィールドのファイルも返しません。見られないときは 404 です。
  - **これだけでは、保存先のURLは開けたままです。直接のURLは、Web サーバーやバケットの設定で別に塞ぎます。**
    - ファイルに保存するとき(Apache): `files/entries/` と `files/fields/` に、次の `.htaccess` を置きます(同梱していません)。設定を無効に戻すときは、この `.htaccess` も消します(消さないと画像が表示されなくなります)。

      ```apache
      <IfModule mod_authz_core.c>
          Require all denied
      </IfModule>
      <IfModule !mod_authz_core.c>
          Order allow,deny
          Deny from all
      </IfModule>
      ```

    - ファイルに保存するとき(nginx): `.htaccess` は効かないので、サーバーの設定で拒否します(例: `location ~ ^/files/(entries|fields|temps)/ { deny all; }`)。
    - S3 に保存するとき: 画像は**有効期限付きの署名付きURL**へリダイレクトして返します(PHP では中継しません。期限は `APP_STORAGE_SIGNED_EXPIRE`、初期値300秒)。バケットポリシーで誰でも読めるようにしている範囲を、メディアとそのサムネイルだけに絞ります。署名付きURLは freo2 のアクセスキーの権限で読むので、絞っても開けます。

      ```json
      {
          "Effect": "Allow",
          "Principal": "*",
          "Action": "s3:GetObject",
          "Resource": [
              "arn:aws:s3:::<バケット名>/files/medias/*",
              "arn:aws:s3:::<バケット名>/files/thumbnails/*"
          ]
      }
      ```

      「パブリックアクセスをすべてブロック」は使いません。バケットポリシーによる公開(メディア)まで止まるためです。

      塞げているかは、管理画面の「バージョン情報」(`/admin/version`)の「AWS SDK」で確かめられます。画像のある記事を1件選び、サーバーから署名を付けずに開いた結果を出します。設定と食い違っているとき(有効なのに開ける・無効なのに開けない)は「注意」になります。この項目は S3 を使っているときだけ表示され、開くたびにバケットへ1回リクエストします。
  - **メディア(`files/medias/`)とそのサムネイルは対象外です。** メディアはエントリーに結び付いておらず、本文には直接のURLで入るためです。公開範囲で制限したい画像は、エントリーの画像(またはフィールドの画像)として登録します。
  - **アップロード中の一時ファイル(`files/temps/`)は、設定に関係なく直接開けないようにしています**(同梱の `files/temps/.htaccess`)。S3 では `.htaccess` が効かないので、上のバケットポリシーで塞ぎます。
  - **有効にすると、画像1枚ごとに PHP が動きます。** 負荷を抑えるため、受け口では最初にセッションのロックを外し(同じ人の画像を並べて取得できるように)、ファイルは `readfile()` で流し、`Last-Modified` を付けて変わっていなければ `304` を返します。S3 のリダイレクトは、ブラウザが署名の期限の半分まで覚えます。
  - `mod_rewrite` で URL から `index.php` を省略しているときは、同梱の `.htaccess` の `RewriteRule ^(storage/.*)$ index.php/$1 [L]` が必要です(拡張子の付いたURLは、ほかの行では `index.php` に回さないため)。

---

## 6. データモデル

テーブル定義は [migrate/](migrate/) のSQLにあります。最初の定義は `20231218163000-create_tables.sql` で、以降の変更は日付順の `ALTER` ファイルです。

```
authorities(権限) ─< users(ユーザー) ─< attribute_sets >─ attributes(属性)
                                                │
types(型) ─< entries(エントリー) ───────────────┘
   │            │
   │            ├─< category_sets >─ categories(カテゴリー) ─ types
   │            ├─< field_sets ────  fields(フィールド) ────── types
   │            └─< comments(コメント)
   │
   └ 型ごとに、カテゴリーとフィールドを持つ
```

### コンテンツ

- **`types`(型):** 初期データは `entry` と `page`。プラグインが独自の型を追加することもあります(`setup/install.php` で `types` に行を登録する)。
- **`entries`(エントリー):** 型を問わず、すべてのコンテンツがここに入ります。主な列は次のとおりです。
  - `code`: URL に使う識別子(`/entry/detail/<code>`、`/page/<code>`)。**一意かどうかは型ごとに見ます**。型が違えば同じコードを使えます
    - 使える文字は半角英数字・`_`・`-`・`/` です。`/` は階層の区切りなので、**先頭・末尾に置くことと、続けて書くことはできません**(`about/company` は可、`/about`・`about/`・`about//company` は不可)
    - 管理画面のページ一覧(`/admin/page`)は、この階層で親子を判定してまとめます。コードを末尾から `/` ごとに遡り、**存在する最も近い祖先**を親とします(`about/staff` が無ければ、`about/staff/yamada` の親は `about`)。祖先が1つも無ければ一覧のトップに出ます。子のある行はタイトルが `?parent=<親のコード>` へのリンクになり、存在しないコードを `parent` に渡すとエラーになります。子の一覧から開いた登録画面(`/admin/page_form?parent=<親のコード>`)では、コードの初期値が `<親のコード>/` になります。判定は [app/services/entry.php](app/services/entry.php) の `service_entry_parent()`
  - `title`, `text`, `text_type`(本文形式: 複数行入力 / HTML / WYSIWYG など)
  - `public`(公開範囲): `all`(公開)/ `user`(登録ユーザー)/ `attribute`(指定の属性を持つユーザー)/ `password`(パスワード認証)/ `none`(非公開)
  - `public_begin`/`public_end`(公開期間)、`approved`(承認)
  - `pictures`, `thumbnail`, `comment`(コメントの受付), `sort`
- **`fields` / `field_sets`(フィールド):** 型ごとの追加項目の定義と、エントリーごとの値です。値はすべて `field_sets.text` に文字列で入ります。種類(`kind`)は `app/config.php` の `option.field.kind` にあります(一行入力、セレクトボックス、画像アップロードなど)。
  - **種類によって、エントリーを保存するときの検証が変わります**([app/models/entries.php](app/models/entries.php) の `validate_entries()`)。

    | 種類(`kind`) | 検証 |
    | --- | --- |
    | 一行入力・数字入力・英数字入力 | 100文字以内(数字入力は数値、英数字入力は半角英数字であること) |
    | 複数行入力 | 2000文字以内 |
    | セレクトボックス・ラジオボタン・チェックボックス | 選択肢(`choices`。改行区切りで持ちます)にある値であること |
    | それ以外(HTML直接入力・WYSIWYGエディタ・画像 / ファイルアップロード) | 5000文字以内 |

  - `validation` を `required` にすると必須になります。
  - **複数選べる種類の値は、改行区切りで1つの `field_sets.text` に入ります。**
  - **画像 / ファイルアップロードの値はファイル名で、入力欄ではなくアップロードで登録されます。** そのため、エントリーを保存してもこの2種類のひも付けは消えません。
- **`categories` / `category_sets`:** 型ごとの分類と、エントリーとのひも付けです。

**公開側でエントリーを取得するときは、`model('select_entries', ...)` ではなく `service_entry_select_published('<型コード>', $queries)` を使ってください。**
ログインユーザーの権限・属性、公開範囲、公開期間、承認状態をまとめて判定し、パスワード認証前の本文も伏せてくれます([app/services/entry.php](app/services/entry.php))。

判定の結果は、見る人によって次のように変わります。

| `public` | 未ログイン | ゲスト(power 0) | 閲覧者以上(power 1〜3) |
| --- | --- | --- | --- |
| `all`(公開) | 見える | 見える | 見える |
| `user`(登録ユーザーに公開) | 見えない | 見える | 見える |
| `attribute`(指定の属性に公開) | 見えない | その属性を持っていれば見える | 見える |
| `password`(パスワード認証で公開) | 伏せて見える | 伏せて見える | 伏せて見える |
| `none`(非公開) | 見えない | 見えない | 見えない |

- **「伏せて見える」は、エントリーは取得できるが中身が差し替わる**という意味です。タイトルの先頭に設定の文字列(初期値は `要認証: `)が付き、本文は案内文に置き換わり、画像とフィールドの値も外されます。認証に成功すると `$_SESSION['entry_passwords'][<エントリーID>]` に記録され、以降は本来の内容が表示されます。
- **公開期間(`public_begin`/`public_end`)と承認(`approved`)は、権限に関係なく効きます。** 管理者でログインしていても、公開開始前・公開終了後のエントリーは公開側に出ません。
- 属性には有効期間(`users.attribute_begin`/`attribute_end`)があり、**期限外のユーザーは属性を持っていない扱い**になります([app/controllers/before.php](app/controllers/before.php))。
- **ゲストは、フィルター対象の属性のうち、自分で表示を選んでいないものを持っていない扱い**になります(→ [ユーザーと公開制御](#ユーザーと公開制御))。
- **本文は `text_type` によって出力が変わります。** 「なし」は本文を返さず、「複数行入力」はエスケープして `<p>` で囲み改行を `<br>` にし、「HTML直接入力」「WYSIWYGエディタ」はそのまま出します。`pictures`(改行区切り)も、このとき配列になります。**ビュー側で整形するのではなく、取得した時点で整形済みです。**

### ユーザーと公開制御

- **`users` / `authorities`:** ユーザーと権限です。権限の強さは `authorities.power` で決まります(3=管理者、2=投稿者、1=閲覧者、0=ゲスト)。
  - 投稿者: コンテンツは編集できますが、設定・ユーザー・プラグインなどのシステム系画面には入れません。
  - 閲覧者: 管理画面のコンテンツ系画面にも入れません。
  - ゲスト: 公開側で会員登録したユーザーです。管理画面には入れません。
  - どの権限がどの画面に入れるかは [app/controllers/before.php](app/controllers/before.php) に `work` 名の正規表現でまとまっています。
  - `enabled` が 0 のユーザーは、パスワードが合っていてもログインできません。
  - **パスワードは `users.password` にハッシュで保存します。** 方式は `app/config.php` の `hash_algorithm`(`APP_HASH_ALGORITHM` で上書き可)で選び、既定は **bcrypt**(どの環境でも `password_hash()` の組み込みで使える)です。`argon2` は可用性チェック付きのオプション、`md5` は古い PHP 環境向けの互換用です。保存するときは `service_user_password_hash()`、照合するときは `service_user_password_verify()` を使います(`app/services/user.php`)。秘密のペッパー(`config.php` の `hash_pepper`)を HMAC で前処理してからハッシュ化するので、ペッパーを効かせつつ bcrypt の72バイト上限・NUL バイト問題も避けられます。古い方式で保存されたパスワードは、ログインに成功した時点で現在の方式に作り直されます(rehash-on-login)。
  - **ログインに10回続けて失敗すると、そのアカウントは5分間凍結されます**(`users.failed`/`failed_last` で判定)。正しいパスワードでも入れなくなり、ログインに成功すると回数はリセットされます。前の失敗から5分以上たってから失敗したときは、1回目から数え直します(凍結が明けた後も、改めて10回試せます)。
  - **2段階認証:** `users.twostep` が 1 のユーザーは、パスワードが通った後に `users.twostep_email` へ送られるコード(`twostep_code`。パスワード再設定の暗証コードと同じく8桁の数字で、どちらも `service_user_code()` が作ります。有効期限 `twostep_expire` は10分)を入力して、はじめてログインできます。設定するのは管理画面のユーザー編集(`/admin/user_form`)と、本人のユーザー情報編集(`/auth/modify`)です。
    - **コードやトークンは、暗号用の乱数で作ります。** コードは `service_user_code()`(`random_int()`)、URL のトークンとログイン状態を保持するセッションIDは `service_user_token()`(`random_bytes()`。16進数32文字)です。推測されると困る値を新しく作るときも、この2つを使います。levis の `rand_number()` は `mt_rand()` をもとにしていて予測できるので、こうした用途には使いません。`rand_string()` も、古い PHP では予測できる方法に切り替わることがあるので、推測されると困る値には `service_user_token()` を使います。
    - **コードを続けて10回間違えると、そのコードは無効になります**(上限は `app/config.php` の `code_failed_limit`。回数は `users.twostep_failed`)。再送信すると新しいコードで数え直します。アカウントは凍結しません。パスワード再設定の暗証コード(`users.token_failed`)も同じです。
    - パスワードが通った時点では `$_SESSION['auth']['twostep']` に「認証待ち」を記録するだけで、コードの画面にユーザー名・パスワードは受け渡しません。使ったコードは、ログインに成功した時点で消します。
    - 本人が有効にするとき、または有効のまま `twostep_email` を変えるときは、確認画面の後のコード入力画面(`/auth/modify_verify`)で、そのアドレスに送ったコードを入力しないと登録できません(判定は `service_user_twostep_verify_required()`)。送ったコードと確認中のアドレスはセッションに持ちます。管理画面からの設定では確認しません。
    - 「次回からこのブラウザではコードを入力しない」を選ぶと、そのブラウザのログイン用のセッション(`sessions` の行)の `twostep` が 1 になり、次からコードを省略します。2段階認証を無効にしたときや `twostep_email` を変えたときは、`service_user_twostep_reset()` でそのユーザーの省略をすべて解除します。
- **`attributes` / `attribute_sets`:** 「有料会員」のような属性です。ユーザーとエントリーの両方にひも付け、`public = 'attribute'` のエントリーを見られる人を絞り込みます。ユーザー側には有効期間(`attribute_begin`/`attribute_end`)も持てます。
  - **フィルター:** `attributes.filterable` が 1 の属性は、ゲストが会員ページの「フィルター」(`/auth/filter`)で、表示するかどうかを自分で選べます。「R-18 は見たくない」のように、**見える範囲を自分で狭める**ための機能です。
    - ログインした時点で [app/controllers/before.php](app/controllers/before.php) が、与えられた属性からフィルター対象で表示を選んでいないものを除いて `$GLOBALS['attributes']` を作ります。判定は `service_entry_select_published()` のままなので、エントリーを取得するすべての画面に効きます。
    - **初期状態は「表示しない」です。** 選んだ属性はクッキー `attribute_filter[<ユーザーID>]` に保存します(ユーザーごとに分けているので、1つのブラウザを複数人で使っても、ほかの人の選択は効きません)。
    - **クッキーの値は、与えられた属性と照らし合わせてから使います。** 与えられた属性から除くだけなので、クッキーを書き換えても見える範囲は広がりません。
    - 対象はゲスト(power 0)だけです。閲覧者以上は、もともと属性を見ずに判定しています。
    - 判定は「エントリーの属性のどれか1つでも一致すれば見える」のままです。フィルター対象の属性とほかの属性を同じエントリーに付けると、フィルター対象を外してもほかの属性で見えるので、**エントリーに付けるフィルター対象の属性は1つまで**にします。
    - フィルターで隠れたエントリーの詳細は、ほかの理由で見えないときと同じく「見つかりません」になります。
- **`sessions`:** 「ログイン状態を保持する」ためのセッションです。ログアウトしても行は消えず、`keep` が 0 になるだけで、期限切れの行は次のログイン時にまとめて削除されます。

### サイト運営

| テーブル | 内容 |
| --- | --- |
| `settings` | 管理画面「設定」の値(キーと値の組) |
| `menus` | 公開側のメニュー |
| `widgets` | 画面の決まった位置に差し込むHTML(`public_home`, `admin_page` など) |
| `contacts` | お問い合わせ。`status`(未対応 / 対応中 / 完了 / 対応不要)で進捗を管理します。管理画面の一覧は、状況を指定しないと `app/config.php` の `contact_status_hidden`(既定は完了・対応不要)を除いて表示します |
| `comments` | コメント。`entry_id` が入ればエントリーへのコメント、`contact_id` が入れば**お問い合わせのやりとり**(管理者と会員の双方が書き込めます) |
| `plugins` / `themes` | インストール済みのプラグイン・テーマと、その有効状態・設定値 |
| `logs` | 管理画面での操作ログ。**同じ操作(対象と種類の組み合わせ)は、1リクエストにつき1件だけ**記録されます(並び順を10件まとめて変えても1件)。**保存日数(`app/config.php` の `log_retention`、初期値180日)を過ぎたログは、ログを記録するときに物理削除されます**(Cron は不要。`0` にすると削除しません) |

---

## 7. 画面とURL

### 公開側

| URL | 内容 |
| --- | --- |
| `/` | トップページ(設定で指定したページ + 新着エントリー) |
| `/entry/` | エントリー一覧(2ページ目以降は `/entry/page/<ページ>/`) |
| `/entry/category/<カテゴリーのコード>/` | カテゴリーで絞り込んだエントリー一覧(2ページ目以降は `/entry/category/<コード>/page/<ページ>/`) |
| `/entry/archive/<年月>/` | 月で絞り込んだエントリー一覧(年月は `2026-10` の形。2ページ目以降は同じく `page/<ページ>/` を付ける) |
| `/entry/detail/<code>` | エントリー詳細 |
| `/entry/feed` | RSS |
| `/page/<code>` | ページ(`code` にスラッシュを含めて階層にできる。設定で `/page/` を省略できる) |
| `/contact/` | お問い合わせ(入力 → 確認 → 完了) |
| `/auth/` | ログインと会員向けの画面(→ [会員機能](#会員機能auth)) |

コメントの投稿フォームは、エントリーやページの詳細の中にあります(送信先も詳細のURLで、`exec=comment` を付けて送ります)。確認画面だけが `/comment/preview`、完了が `/comment/complete` に分かれます。

**エントリー一覧の絞り込みとページ送りは、パスで表します。** URLは `service_entry_list_url()`([app/services/entry.php](app/services/entry.php))で組み立て、テーマのビューでも `?page=` のようなクエリ文字列を自分で組み立てないでください(コントローラーが `$_view['entry_pages']`・カテゴリーと月の `url` に用意しています)。

- カテゴリーと月は、どちらか1つだけをパスにできます。存在しないカテゴリーや形の違うパス(`/entry/foo` など)は「ページが見つかりません」になります。
- 以前のクエリ文字列のURL(`/entry/?category_sets[]=3`・`/entry/?archive=2026-10`・`/entry/?page=2`)は、パスで表せるときは新しいURLへ転送します(301)。
- パスで表せない絞り込み(複数のカテゴリー、カテゴリーと月の組み合わせ、`keyword` などのキーワード)は、今までどおりクエリ文字列で使えます。このときのページ送りもクエリ文字列(`?keyword=xxx&page=2`)になります。

### 会員機能(`/auth/`)

`/auth/` はログイン画面であると同時に、会員(ゲスト)向けの画面の入口です。

| URL | 内容 |
| --- | --- |
| `/auth/` | ログイン。ログイン後は `referer` で指定された画面か `/auth/home` に戻る。2段階認証を使うユーザーは、同じURLでコードの入力画面に進む(→ [ユーザーと公開制御](#ユーザーと公開制御)) |
| `/auth/home` | 会員ページ。権限が閲覧者以上なら `/admin/` にリダイレクトされる |
| `/auth/register` | 会員登録(入力 → `register_preview` → 完了) |
| `/auth/modify` | 会員情報の編集(入力 → `modify_preview` → 完了。2段階認証を有効にするとき・送り先を変えるときは、確認の後に `modify_verify` でコードを入力する) |
| `/auth/password` | パスワード再設定 |
| `/auth/email_send` | メールアドレスの存在確認 |
| `/auth/leave` | 退会(`leave_confirm` で確認 → 完了) |
| `/auth/contact` | 自分が送ったお問い合わせの一覧。詳細から**管理者とコメントでやりとりできる** |
| `/auth/comment` | 自分が投稿したコメントの一覧 |
| `/auth/filter` | フィルター。フィルター対象の属性を持つゲストだけ、メニューに出る(→ [ユーザーと公開制御](#ユーザーと公開制御)) |
| `/auth/logout` | ログアウト |

- **会員登録と退会は、設定「訪問者によるユーザー新規登録」(`user_use_register`)を有効にしないと使えません。** 無効のままだとログイン画面にリンクが出ず、URL を直接開いてもエラーになります。初期値は無効です。
- 公開側から登録したユーザーの権限は、`power` が 0 の権限(ゲスト)になります。
- **メールアドレスの存在確認:** 登録直後は `users.email_verified` が 0 で、会員ページに確認をうながす案内が出ます。`/auth/email_send` からメールを送り、本文に書かれたURL(`/auth/email_verify?key=<メールアドレス>&token=<トークン>`)を開くと 1 になります。トークンはメールを送るたびに作り直し、有効期限は24時間です。期限を過ぎたURLや誤ったURLを開くとエラーになります。
- **パスワード再設定:** メールアドレスを送ると、そのユーザーに `token`(URL用)と `token_code`(暗証コード。8桁の数字)が記録されます。画面は `token` 付きのURLへ進み、**メールで届いた暗証コードと新しいパスワード**を入力して完了です。メールに入るのは暗証コードと有効期限だけで、URL は画面の遷移で渡されます。**有効期限(`token_expire`)は発行から10分**で、URL を開くときと送信するときの両方で確認します(期限を過ぎてから送信すると、暗証コードの入力エラーになります)。**暗証コードを続けて10回間違えると、その暗証コードは無効になり、再発行からやり直します**(送信前の入力内容検証で間違えた分も数えます)。
- ログインしていると、お問い合わせやコメントの入力欄には登録した名前・メールアドレスが読み取り専用で入り、`contacts.user_id`/`comments.user_id` にユーザーがひも付きます。

### 管理画面(`/admin/`)

ログインが必要です([app/controllers/before_admin.php](app/controllers/before_admin.php))。

- コンテンツ: エントリー、ページ、カテゴリー、フィールド、メディア、メニュー、ウィジェット
- コミュニケーション: お問い合わせ、コメント
- システム: ユーザー、属性、設定、プラグイン、テーマ、操作ログ、HTML出力(設定で有効にしたときだけ)、バージョン(システムの更新は `/admin/update`。→ [11. データベースのマイグレーション](#11-データベースのマイグレーション))

---

## 8. 設定・メニュー・ウィジェット

### 設定値の置き場所は3つある

| 置き場所 | 変更する人 | 参照方法 | 例 |
| --- | --- | --- | --- |
| 直下の `config.php` の定数 | サーバー管理者 | 定数、または `$GLOBALS['config']` | DB接続、URL、メール送信の有無 |
| [app/config.php](app/config.php) | 開発者 | `$GLOBALS['config']` | 選択肢のラベル、アップロードできる拡張子 |
| `settings` テーブル | サイト管理者(管理画面) | `$GLOBALS['setting']` | サイト名、表示件数、メール文面 |

`app/config.php` の値はすべて `app_config('APP_XXX', 既定値)` で定義されています。**直下の `config.php` で同名の定数を定義すれば、本体を書き換えずに上書きできます。**

### 管理画面の「設定」に項目を増やす

1. マイグレーションで `settings` に行を追加する(`INSERT INTO settings VALUES('項目ID', 初期値);`)
2. [app/setting.php](app/setting.php) の `$GLOBALS['setting_contents']` に項目の定義(名前、説明、`type`、必須かどうか)を追加する

### コード値を日本語で表示する

`public` や `kind` のようなコード値は、`$GLOBALS['config']['option']` でラベルに変換して表示します。

```php
<?php h($GLOBALS['config']['option']['entry']['public'][$entry['public']]) ?>
```

### 承認の設定

エントリー・ページ・コメント・ユーザーには承認の仕組みがありますが、**設定で有効にしたときだけ**使われます。初期値はどれも無効です。

| 設定 | 有効にすると |
| --- | --- |
| `entry_use_approve` / `page_use_approve` | 登録した直後は `approved` が 0 になり、公開側に出ません。編集画面に「承認済にする」ボタン、一覧に「承認」の列が出ます(操作できるのは管理者だけ) |
| `comment_use_approve` | 投稿されたコメントは `approved` が 0 になり、承認するまで公開側に出ません。完了画面にもその案内が出ます |
| `user_use_approve` | 会員登録した直後は `enabled` が 0 になり、管理者が有効にするまでログインできません |

**無効のときは、承認のボタンも列も画面に出ません。** 動きを確認したいときは、先に設定から有効にします。

### freo2 が送るメール

本文は `app/views/mail/` のテンプレート、件名と共通の前書き・後書きは管理画面の「設定」→「メール設定」です。

| きっかけ | 宛先 | テンプレート |
| --- | --- | --- |
| お問い合わせの送信 | 設定の送信先(`mail_to`) | `mail/contact/send_admin.php` |
| 同上(自動返信) | 入力されたメールアドレス | `mail/contact/send_user.php` |
| 会員登録 | 登録したメールアドレス | `mail/auth/register.php` |
| メールアドレスの存在確認 | 登録されたメールアドレス | `mail/auth/email_verify.php` |
| パスワード再設定 | 入力されたメールアドレス | `mail/auth/password.php` |
| ログインの2段階認証 | 2段階認証用メールアドレス | `mail/auth/twostep.php` |
| 退会 | 登録されたメールアドレス | `mail/auth/leave.php` |

テンプレートは、コントローラーと同じく **`mail/<mode>/<名前>.php` の2階層**に置きます(会員向けの画面から送るメールは `mail/auth/`)。プラグインでメールを足すときも、プラグインの `app/views/mail/<mode>/` に置きます。

送信は [app/services/mail.php](app/services/mail.php) の `service_mail_send()` を通します。送り方は、直下の `config.php` の `APP_MAIL_SEND` で選びます。

| `APP_MAIL_SEND` | 動作 |
| --- | --- |
| `'sendmail'`(初期値) | PHP の `mail()` で送る |
| `'smtp'` | `APP_MAIL_SMTP` で指定した SMTP サーバーから送る(下記) |
| `null` | 送らない |

それ以外の値(書き間違いなど)のときは、送信の失敗として扱い、エラーログに出します。**テスト中(単体テスト・シナリオテスト)は、`APP_MAIL_SEND` に関係なく `null`(送らない)、`APP_MAIL_LOG` に関係なく記録あり**になります(→ [13. テスト](#13-テスト))。`APP_MAIL_SEND` を `null`、`APP_MAIL_LOG` を `true` にしておくと、実際には送らずに `mail/<日付>/<時刻>_<宛先>.txt` に記録されます(→ [2. セットアップ](#手順))。

**SMTP で送る:** Amazon SES やレンタルサーバーの SMTP サーバーから送りたいときは、直下の `config.php` で `APP_MAIL_SEND` を `'smtp'` にし、`APP_MAIL_SMTP` を定義します(項目は [app/config.php](app/config.php) の `mail_smtp`)。

```php
define('APP_MAIL_SEND', 'smtp');
define('APP_MAIL_SMTP', [
    'host'       => 'email-smtp.ap-northeast-1.amazonaws.com',
    'port'       => 587,
    'encryption' => 'tls',   // tls(587番などで STARTTLS)/ ssl(465番)/ none(暗号化しない)
    'username'   => 'XXXXXXXXXX',
    'password'   => 'YYYYYYYYYY',
    'timeout'    => 10,      // 接続・応答を待つ秒数
]);
```

- 送信元(エンベロープ)には、設定「メール設定」の送信元(`From` ヘッダー)のアドレスを使います。Amazon SES では、このアドレス(またはドメイン)を SES で認証しておきます。
- 認証は AUTH PLAIN か AUTH LOGIN(サーバーが対応しているほう)、`username` が空なら認証しません。サーバーの証明書は検証します。
- 送れなかったときは、ほかの送信の失敗と同じくエラー画面になり、理由(接続できない・認証に失敗したなど)を PHP のエラーログに出します。
- SMTP の処理は [libs/modules/smtp.php](libs/modules/smtp.php) の `smtp_send()` です。引数は levis の `mail_send()` と同じ(先頭に接続設定)で、メールの組み立て(文字コードの変換・ヘッダー)も `mail()` で送るときと同じ `mail_build()` を使います。

**エンベロープFrom(Return-Path)を指定する:** 届かなかったメールの戻り先や SPF の判定に使われる送信元です。指定しないと、`mail()` で送るときはサーバーの既定のアドレス(`www-data@<ホスト名>` など)、SMTP で送るときは `From` ヘッダーのアドレスになります。変えたいときは、直下の `config.php` で `APP_MAIL_ENVELOPE` を定義します。

```php
define('APP_MAIL_ENVELOPE', 'no-reply@example.com');
```

- `'sendmail'` のときは `mail()` の第5引数に `-f<アドレス>` を渡し、`'smtp'` のときは `MAIL FROM` に使います。送り方を切り替えても同じ意味で効きます。
- sendmail のコマンドの引数にもなるので、メールアドレスの形でない値(空白や改行を含むなど)のときは送信せず、送信の失敗として扱ってエラーログに出します。
- `service_mail_send()` の第5引数(`$parameters`)を呼び出し側で指定したときは、`'sendmail'` ではそちらを優先します。
- レンタルサーバーによっては、`-f` に指定できるアドレスが制限されていることがあります(サーバーで管理しているドメインのアドレスだけ、など)。

### HTML出力

管理画面の「HTML出力」(`/admin/export`)で、公開側のページを HTML ファイルに出力し、CSS・画像などと一緒に ZIP ファイルでダウンロードできます。設定「HTML出力設定」の `export_use_html` を有効にしたときだけ使えます(初期値は無効。無効のときはメニューに出ず、URL を開いてもエラーになります)。処理は [app/services/export.php](app/services/export.php) です。

**出力のしかた:** freo2 が、自分自身の公開側のページを1つずつ HTTP で取得して保存します(画面の JavaScript が、`APP_EXPORT_STEP` で指定した数(初期値5)ずつ繰り返し頼みます)。

- 取得するときはクッキーを付けないので、**ページは未ログインの訪問者と同じ内容になります。** 管理者がログインして出力しても、会員向けの記事は入りません。出力するURLの一覧を作るときも、権限と属性を外して判定します(`service_export_select_published()`)。
- 取得先は `APP_HTTP_URL` です。サーバーの中から公開URLを開けない環境(Docker のコンテナの中など)では、直下の `config.php` で `APP_EXPORT_FETCH_URL` を定義して変えます。**Basic 認証や IP アドレスの制限で、サーバーが自分自身のページを開けない環境では使えません。**
- ページ数ぶんのリクエストが、アクセスログやアクセス解析に残ります。WAF のレート制限を設定しているサーバーでは、制限に掛かることがあります。
- 作業用のファイルは `files/temps/export_<トークン>/` に置きます(直接は開けません)。`temp_retention`(初期値8時間)より古いものは、次に出力を始めたときに削除します。

**コマンドラインから出力する:** ページ数の多いサイトや、Basic 認証・IP アドレスの制限があるサーバーでは、`index.php` のあるディレクトリで次のコマンドを実行します。設定「HTML出力」が有効なときだけ動きます。

```
php index.php "/cli/export?zip=/path/to/export.zip"
```

- 出力する前の確認(`NOTICE:`)、ページごとの進み具合、最後に結果(`OK:`)を表示し、`zip` に指定したパスへ ZIP ファイルを書き出します。出力できなかったページは `FAILURE:` で表示します。
- HTTP を使わず、ページを1つずつ別のプロセス(`php index.php <URL>`)で描画します(`service_export_render()`)。アクセスログや WAF の影響を受けず、クッキーも無いので、必ず未ログインの訪問者と同じ内容になります。出力中であることは、環境変数 `HTTP_X_FREO2_EXPORT` で子のプロセスに渡します。
- コマンドラインでは HTTP のステータスが分からないので、何も出力されなかったページ(転送されたページ)と、`WARNING:`・`ERROR:` を出したページを、出力できなかったものとして数えます。
- `php index.php /cli/<コマンド>` の形のコマンドは `app/controllers/cli/` に置きます。[app/controllers/before_cli.php](app/controllers/before_cli.php) が、コマンドライン以外から開いたときはエラーにします。

**出力するもの:**

| 対象 | 出力するファイル |
| --- | --- |
| トップページ | `index.html` |
| エントリー一覧(全件・カテゴリー・月 × ページ送り) | `entry/index.html`・`entry/category/<コード>/page/2/index.html` など |
| エントリー・ページの詳細(「全体に公開」で、公開期間内のものだけ) | `entry/detail/<コード>/index.html`・`page/<コード>/index.html`(設定でページのURLを省略していれば、省略したURLにも) |
| 画像のページ(画像のあるエントリー・ページ) | `file/entry/<コード>/index.html` など |
| RSS | `entry/feed.xml` |
| 存在しないページ | `404.html` |
| プラグインのページ | プラグインの `config.php` の `export`(→ [10. プラグイン](#10-プラグイン)) |
| 静的なファイル | `APP_EXPORT_STATIC` のディレクトリ(初期値 `css/`・`js/`・`img/`・`themes/`・`plugins/`)と、ストレージがファイルのときはアップロードしたファイル。`.` で始まるファイルと PHP のファイルは除きます |

- URL は `/entry/detail/<コード>` のように末尾に `/` が無くても、`<コード>/index.html` に出力します。配信先で、ディレクトリの URL に `index.html` を返す設定が要ります(Amazon S3 の静的ウェブサイトホスティングや Apache は既定で返します。CloudFront で S3 を直接オリジンにするときは、CloudFront Functions などで `index.html` を補います)。
- HTML の中の管理用のURL(`APP_HTTP_URL`、ストレージがファイルのときは `APP_STORAGE_URL` も)は、設定の「公開用URL」(`export_url`)に置き換えます。`href`・`src`・`action` に書かれたサイトの中の絶対パス(`/entry/` など)も、公開用URLのパスに合わせ、`index.php` を外します。CSS・JavaScript の中の URL は置き換えません。
- **セキュリティヘッダーは付きません**(→ [5.8](#58-セキュリティ))。配信先(CloudFront のレスポンスヘッダーポリシーなど)で付けます。

**出力中のページで変わること:** 出力の処理はヘッダー `X-Freo2-Export` にトークンを付けて取得し、`app_export()`([app/bootstrap.php](app/bootstrap.php))はそのトークンの作業用ディレクトリがあれば `true` を返します。出力中は次のようになります。

- 公開側のメニューから、お問い合わせ・ログイン(`$GLOBALS['menu_contents']['public']['home']` の `contact`・`auth`)と、メニュー管理で登録した `/contact/`・`/auth/` へのリンクを外します。ウィジェットや本文に書いたリンクは外れません。
- エントリーの `comment` を「受け付けない」として扱い、コメントのフォームを出しません。承認済みのコメントは表示します。

テーマやプラグインで、静的なページで使えないもの(フォームなど)を出しているときは、`app_export()` で出し分けます。

**出力しても使えない機能:** 出力する前に、画面で件数とともに知らせます(`service_export_check()`)。

- 「全体に公開」以外の記事(会員向けの公開・パスワード認証)、コメントの投稿、お問い合わせ、ログイン
- 公開期間: 開始・終了の日時になっても、出力し直すまで反映されません
- エントリー一覧の絞り込みのうち、パスで表せないもの(→ [7. 画面とURL](#7-画面とurl))
- `APP_STORAGE_PROTECT`: 画像を PHP で返す仕組みなので、HTML では画像を表示できません
- メニュー管理で登録したメニューのうち、リンク先のサイトの中のページを出力しないもの(対応していないプラグインのページなど): HTML ではリンクが切れます。メニューは自動では外さないので、無効にするかリンク先を直します

### メニュー

各画面のメニュー構成は [app/menu.php](app/menu.php) の `$GLOBALS['menu_group']` / `$GLOBALS['menu_contents']` で定義されています。
プラグインは、この配列に項目を差し込んで管理画面にメニューを追加します(→ [10. プラグイン](#10-プラグイン))。

**メニューは権限と設定で出し分けられます。** たとえばフィールド管理とウィジェット管理は管理者だけ、システム系のグループも管理者だけ、といった具合です(`show` の条件)。**画面に入れるかどうかの判定は [app/controllers/before.php](app/controllers/before.php) が別に行う**ので、メニューを消しただけでは URL を直接開けてしまいます。画面を増やすときは両方に手を入れます。

---

## 9. テーマ

**見た目を変えたいときは、本体のビューを直接書き換えず、テーマを作ります。**

### 置き場所

テーマは `themes/<テーマコード>/` に置きます。CSS・画像などの静的ファイルも同じディレクトリに入れ、ブラウザからはそのパス(例: `themes/sample/css/public.css`)で読み込まれます。

プログラム本体を公開ディレクトリの外に置く配置では、静的ファイルだけを公開ディレクトリ側に移します(→ [2. セットアップ](#プログラム本体を公開ディレクトリの外に置く))。

参考: [themes/sample/](themes/sample/)

### 作り方

1. `themes/<コード>/config.php` を作ります(必須)。

   ```php
   <?php

   $GLOBALS['theme']['mytheme']['code']        = 'mytheme';
   $GLOBALS['theme']['mytheme']['name']        = 'マイテーマ';
   $GLOBALS['theme']['mytheme']['description'] = 'テーマの説明。';
   $GLOBALS['theme']['mytheme']['version']     = '0.0.0';
   $GLOBALS['theme']['mytheme']['updated']     = '2026-01-01';
   ```

2. 置き換えたいファイルを、**本体と同じパス**で `app/` の下に置きます。
   たとえば `themes/mytheme/app/views/header.php` を置くと、`app/views/header.php` の代わりに読み込まれます。
   ビューだけでなく、`import()` で読み込まれるファイル(コントローラーなど)も同じ仕組みで差し替えられます。

3. 管理画面の「テーマ」からインストールし、有効にします。**有効にできるテーマは1つだけです。**

**見出し・メニュー・ボタンなどの文言は、ビューを差し替えなくても変えられます。** 公開側・会員向け(`auth`)の画面は、多くの画面に出る短い文言を [app/string.php](app/string.php) の `$GLOBALS['string']` から出力しています。テーマの `app/bootstrap.php` で値を書き換えます(参考: [themes/sample/app/bootstrap.php](themes/sample/app/bootstrap.php))。

```php
$GLOBALS['string']['heading_menu']   = 'Menu';
$GLOBALS['string']['text_required']  = 'Required';   // 入力欄の「必須」のバッジ
```

`string.php` に入れているのは、**多くの画面に共通して出る短い文言だけ**です。その画面だけの項目名(「お名前」など)や案内文はビューに直接書いてあるので、変えたいときはビューを差し替えます。検証のメッセージ(モデル)と管理画面の文言は対象外です。

**`footer.php` を差し替えるときは、`<?php isset($_view['script']) ? e($_view['script']) : '' ?>` の出力を残してください。** プラグインは、ここを通して画面にJSを読み込みます。消すとプラグインのJSが読み込まれなくなります(`sample` テーマのフッターは残しています)。

`config.php` に `setting_define`(設定項目の定義)と `setting_default`(初期値)を書くと、管理画面のテーマ詳細から設定を変更できます。値は `$GLOBALS['theme'][<コード>]['setting']` で参照します。

---

## 10. プラグイン

**機能を足したいときは、本体を書き換えずにプラグインを作ります。**

### 置き場所

プラグインは `plugins/<プラグインコード>/` に置きます。JS・CSSなどの静的ファイルも同じディレクトリに入れます。

テーマと同じく、プログラム本体を公開ディレクトリの外に置く配置では、静的ファイルだけを公開ディレクトリ側に移します。

プラグインのコードは、原則として本体と同じように書けます。ただし、パスの扱いなど若干異なる部分があります(下記「書くときのポイント」)。

### 同梱のプラグイン: `sample`

リポジトリに含まれるプラグインは、専用ページ(`/sample/`)を1枚表示するだけの最小例 [plugins/sample/](plugins/sample/) だけです。まずこれを読むと、プラグインの最小構成が分かります。

```
plugins/sample/
├── config.php                         プラグインの情報
└── app/
    ├── controllers/sample/index.php   /sample/ のコントローラー($_view['title'] を設定する)
    └── views/sample/index.php         /sample/ のビュー(本体のヘッダー・フッターを読み込む)
```

テーブルの作成やメニューの追加など、`sample` に含まれない機能は、下記「書くときのポイント」のコード例を参考にしてください。
独自のテーブルや型を持つ実際のプラグインは、別のリポジトリ [freo2-plugins](https://github.com/refirio/freo2-plugins) で公開しています。

### ファイル構成

```
plugins/<コード>/
├── config.php                 必須。プラグインの情報
├── app/
│   ├── bootstrap.php          有効なら毎リクエスト読み込まれる(関数定義、メニュー追加、モデルの読み込み)
│   ├── controllers/
│   │   ├── before.php         本体のコントローラーより前に実行される
│   │   ├── after.php          本体のコントローラーの後、ビューの前に実行される($_view を加工できる)
│   │   └── <mode>/<work>.php  プラグイン独自の画面
│   ├── models/                独自テーブルのモデル
│   ├── services/
│   └── views/<mode>/<work>.php
├── setup/
│   ├── install.php            インストール時に実行(テーブル作成など)
│   ├── uninstall.php          アンインストール時に実行
│   └── upgrade_0_0_2.php      バージョン 0.0.2 へ更新するときに実行
└── js/ css/                   ブラウザから読み込む静的ファイル
```

`before_<mode>.php` / `after_<mode>.php` を置くと、特定の mode のときだけ実行されます。

必須なのは `config.php` だけです。**`setup/` も、テーブルを作らないプラグインなら置かなくてかまいません**(`sample` にもありません)。

### config.php

```php
<?php

$GLOBALS['plugin']['myplugin']['code']        = 'myplugin';
$GLOBALS['plugin']['myplugin']['name']        = 'マイプラグイン';
$GLOBALS['plugin']['myplugin']['description'] = 'プラグインの説明。';
$GLOBALS['plugin']['myplugin']['version']     = '0.0.0';
$GLOBALS['plugin']['myplugin']['updated']     = '2026-01-01';
```

テーマと同じく、`setting_define` / `setting_default` を書くと管理画面から設定でき、値は `$GLOBALS['plugin'][<コード>]['setting']` で参照できます。

**HTML出力**(→ [8. 設定・メニュー・ウィジェット](#html出力))に対応するときは、`export` に出力するページのURLを書きます。

```php
// 出力するページのURLの配列
$GLOBALS['plugin']['myplugin']['export'] = ['/myplugin/'];

// URLの配列を返す関数の名前（記事の一覧など、データによって変わるとき。関数は app/bootstrap.php で定義する）
$GLOBALS['plugin']['myplugin']['export'] = 'myplugin_export_urls';

// 対応する。ただし、出力するページは増えない（本文の表示を変えるだけなど、本体のページの出力で足りるとき）
$GLOBALS['plugin']['myplugin']['export'] = [];

// 対応しない（注文のように、PHP が動かないと使えない機能）
$GLOBALS['plugin']['myplugin']['export'] = false;
```

- `[]` と `false` は、どちらも出力するページは増えませんが、`false` のときだけ出力の画面で「対応していません」と知らせます。
- 書いていないプラグインは、出力の画面で「対応しているか分かりません」と表示し、ページを出力しません。
- 記事の一覧を返すときは、`service_export_select_published()`([app/services/export.php](app/services/export.php))で「全体に公開」のものだけを取得します。管理者がログインした状態で呼ばれるので、`service_entry_select_published()` では会員向けの記事まで入ります。

**プラグインの `config.php` では `app_config()` を使わず、そのまま代入してください**(`$GLOBALS['plugin']['myplugin']['option'] = [...]`)。`app_config()` は本体の設定を定数で上書きするための仕組みなので、本体と同じ定数名を書くと、**本体を上書きしたときにプラグインの値まで同じ配列に差し替わります。**

### 書くときのポイント

- **独自の画面を追加する:** `app/controllers/<mode>/<work>.php` と `app/views/<mode>/<work>.php` を置くと、そのURLで表示されます。本体のコントローラーより先に判定されるので、**本体と同じURLのファイルを置くと、本体側は実行されません。**
- **ビューでは本体のヘッダー・フッターをそのまま使えます:** `import('app/views/header.php')`(参考: [plugins/sample/app/views/sample/index.php](plugins/sample/app/views/sample/index.php))
- **独自の型を追加するなら、詳細ページのURLは `/<型コード>/detail/<コード>` にします**(エントリーの `/entry/detail/<コード>` と同じ形)。本体のパスワード認証のフォーム(`app/views/password_form.php`)やコメントのフォームは、この形で送信先を組み立てます。認証などの後にリダイレクトするときも、同じURLに戻します。
  - 存在しない `work` のURL(たとえば `/<型コード>/<コード>`)は、エラーにならず `<mode>/index.php` が表示されます。リダイレクト先を間違えても一覧が出るだけなので、気付きにくい点に注意してください。
  - 詳細ページのURLは `service_entry_url($entry)`([app/services/entry.php](app/services/entry.php))で組み立てられます。パスワード認証(`service_entry_password()`)と、コメントの受け付け(`service_comment_acceptable()`・`service_comment_normalize()`・`service_comment_default()`・`service_comment_select_approved()`。[app/services/comment.php](app/services/comment.php))もサービスにあるので、本体の `app/controllers/entry/detail.php` と同じ形で書けます。
- **公開側のフォームで reCAPTCHA を確認するときは、`service_recaptcha_verify()`([app/services/recaptcha.php](app/services/recaptcha.php))を使います。** 設定(`recaptcha_enable`)が無効なら確認せずに `true` を返し、一度通ったら同じセッションでは確認しません。ビューの `recaptcha_input()`・`recaptcha_import()` は `libs/modules/recaptcha.php` の関数で、このサービスを読み込めば一緒に読み込まれます。
  - **コメント先のエントリーは、`service_comment_normalize()` が表示しているエントリーにします。** フォームから送られた `entry_id` は使いません(書き換えて送ると、コメントを受け付けていないエントリーに登録できてしまうため)。
- **テーブルは `setup/install.php` で作り、`setup/uninstall.php` で削除します。** 関数 `db_query()` / `db_insert()` を使い、失敗したら `error()` で止めます。テーブル名には `DATABASE_PREFIX` を付け、本体や他のプラグインとぶつからないようプラグインコードで始めます。

  ```php
  <?php

  // setup/install.php
  $resource = db_query('
      CREATE TABLE IF NOT EXISTS ' . DATABASE_PREFIX . 'myplugin_items(
          id       INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT \'代理キー\',
          created  DATETIME     NOT NULL                COMMENT \'作成日時\',
          modified DATETIME     NOT NULL                COMMENT \'更新日時\',
          deleted  DATETIME                             COMMENT \'削除日時\',
          name     VARCHAR(255) NOT NULL                COMMENT \'名前\',
          PRIMARY KEY(id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT \'マイプラグイン\';
  ');
  if (!$resource) {
      error('プラグイン用SQL [テーブルを作成] を実行できません。');
  }
  ```

- **独自テーブルのモデルは自動では読み込まれません。** `app/models/<テーブル名>.php` に置き、`app/bootstrap.php` で読み込みます(パスは設置ディレクトリからの相対パス)。

  ```php
  import('plugins/myplugin/app/models/myplugin_items.php');
  ```

- **管理画面にメニューを追加する:** `app/bootstrap.php` で `$GLOBALS['menu_contents']` に項目を差し込みます。`active` は、メニューを選択状態にする `work` 名の正規表現です。

  ```php
  $GLOBALS['menu_contents']['admin']['contents']['myplugin'] = [
      'name'   => 'マイプラグイン',
      'link'   => '/admin/myplugin',
      'active' => '/^myplugin(_|$)/',
      'icon'   => '#symbol-list-ul',
      'show'   => true,
  ];
  ```

- **本体の表示内容を加工する:** `app/controllers/after.php` は本体のコントローラーの後、ビューの前に実行されるので、`$_view` を書き換えられます。

  ```php
  <?php

  // エントリー詳細の本文を加工する(myplugin_convert() は app/bootstrap.php で定義しておく)
  if ($_REQUEST['_mode'] === 'entry' && $_REQUEST['_work'] === 'detail') {
      $_view['entry']['text'] = myplugin_convert($_view['entry']['text']);
  }
  ```
- **エントリー(`entries`)の入力画面を作るなら、入力中の内容の一時保存を有効にできます。** 本体の `entry_form` と同じく、`form.register` に次の2つの属性を付けると、`js/admin.js` がタイトル(`input[name=title]`)と本文(`textarea[name=text]`)をブラウザ(`localStorage`)に一時保存し、次に開いたときに復元するかを聞きます。属性が無ければ何もしません。

  ```php
  <form action="..." method="post" class="register validate" data-draft="<?php t(app_draft_key($_view['type']['id'], $_view['entry']['id'])) ?>" data-draft-modified="<?php t($_view['entry']['modified'] ?? '') ?>">
  ```

  - 一時保存は記事ごとに1件(新規登録は型ごとに1件)で、ユーザーごとに分かれます。キーは `app_draft_key()`([app/bootstrap.php](app/bootstrap.php))が作ります。
  - **登録が完了したと判断するのは、送信した後に `?ok=post` の付いた画面を開いたときです。** 登録処理の最後は、本体と同じく `redirect('/admin/<一覧>?ok=post')` にします。
  - `data-draft-modified` は、保存されている内容が一時保存より新しいか(ほかの人が更新したか)を判断するのに使います。
  - 明示的にログアウトすると、そのユーザーの一時保存は削除されます(ログイン画面で `$_view['script']` を通してスクリプトを出すので、テーマで `auth/footer.php` を差し替えるときはこの出力を残します)。
- **エントリーの入力画面で画像・ファイルをアップロードさせるなら、本体の `entry_form` と同じく `/admin/file_upload` を使います。** アップロードされたファイルは一時ファイル(`files/temps/`)に保存され、セッション(`$_SESSION['file']`)には名前と一時ファイルの場所だけが入ります(内容をセッションに入れると、大きなファイルで画面が開けなくなるため)。
  - 入力画面を新しく開いたときと、登録が終わったときは、`unset($_SESSION['file'])` ではなく **`service_entry_file_clear()`**([app/services/entry.php](app/services/entry.php))を呼びます。一時ファイルも一緒に消えます。
  - 登録は、本体と同じく `$_SESSION['file']` を `service_entry_insert()`/`update()` の `files` に渡します(保存先へは一時ファイルを複製します)。
  - 登録されずに残った一時ファイルは、`app/config.php` の `temp_retention`(初期値8時間)を過ぎると、次にアップロードしたときに削除されます。
- **静的ファイル(JS/CSS)はプラグインのディレクトリに置き(例: `plugins/<コード>/js/admin.js`)、そのファイルを使う画面のプラグインのビューから読み込みます。** 本体の `js/admin.js` などには書き足さないでください(プラグインを入れ外しするたびに本体を直す必要が出るため)。
  - 読み込みには `loader_file()` を使います(キャッシュ回避のため、ファイルの更新日時をクエリに付ける)。`loader_js()` は本体の `js/` 配下専用なので、プラグインのファイルには使えません。
  - 公開側・会員向け(`auth`)・管理画面のフッターはどれも、`$_view['script']` の内容を `</body>` の直前(jQuery などの読み込みより後)に出力します。ビューで `footer.php` を import する前に代入します。
  - 他のプラグインも同じ `$_view['script']` に書き込むので、`=` で上書きせず**追記**します。

  ```php
  <?php
  $_view['script'] = ($_view['script'] ?? '') . '<script src="' . t($GLOBALS['config']['http_path'], true) . t(loader_file('plugins/myplugin/js/admin.js'), true) . '"></script>' . "\n";
  ?>
  ```
### 導入の流れ

管理画面の「プラグイン」で、**インストール**(`setup/install.php` の実行と `plugins` テーブルへの登録)→ **有効化**の2段階で使えるようになります。
`config.php` の `version` をインストール済みのバージョンより上げると、管理画面に「要アップグレード」と表示されます。プラグイン詳細画面からアップグレードすると、`setup/upgrade_x_x_x.php` のうち該当するバージョンのものが実行されます。

---

## 11. データベースのマイグレーション

本体のテーブル定義を変えるときは、`migrate/` に SQL ファイルを追加します。

- ファイル名は `YYYYMMDDHHMMSS-<内容>.sql`(例: `20260807223000-alter_table_entries.sql`)
- 実行: `/?_mode=db_migrate` を開く、または `index.php` のあるディレクトリで `php index.php db_migrate`
- 実行済みのファイルは `levis_migrations` テーブルに記録され、未実行のものだけが日付順に実行されます
- **実行済みのファイルを書き換えても、再実行はされません。** 変更は新しいファイルとして追加します
- **テーブルを作るときは `DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci` と、照合順序まで指定します。** `DEFAULT CHARSET=utf8mb4` だけだと、MariaDB では `utf8mb4_general_ci`、MySQL 8 では `utf8mb4_0900_ai_ci` になり、データベースによって文字列の比較結果が変わります(`0900_ai_ci` では「は」と「ぱ」、「あ」と「ア」が一致する)。照合順序の違うテーブルが混ざると、列どうしの比較で `Illegal mix of collations` のエラーにもなります。プラグインの `setup/install.php` で作るテーブルも同じです

プラグインのテーブルはこの仕組みではなく、`setup/install.php` / `upgrade_x_x_x.php` で管理します。

### 本体のバージョンアップとリリース

利用者の手順は [OVERVIEW.md](OVERVIEW.md) の「バージョンアップ」にあります。ZIP を全部上書きしてから、管理画面の `/admin/update` でデータベースの更新と不要なファイルの削除を行います。処理は [app/services/update.php](app/services/update.php) です。

**未実行のマイグレーションがあるあいだは、通常の画面を出しません。** テーブルが新しいコードと合わないことがあるため、[app/controllers/before.php](app/controllers/before.php) の先頭(設定を読む前)で判定して、次のように振り分けます。

| アクセス | 動作 |
| --- | --- |
| `/auth/` のログイン画面 | 通常どおり |
| 管理者(power 3)の `/admin/update` | 更新画面を表示する |
| 管理者のそれ以外のアクセス | `/admin/update` へ転送する |
| 未ログインの管理画面 | ログイン画面へ転送する(ログイン後は `/admin/update`) |
| それ以外 | メンテナンス画面(503) |

- `levis_migrations` テーブルが無いとき(初回の設置前)は判定しません。単体テストは `before.php` を通らないので影響しません。
- 更新画面の「バックアップをダウンロード」は、未実行のマイグレーションがあるときだけ使えます(`service_update_backup()`)。**ファイルはサーバーに保存しません**(内容にパスワードのハッシュも含むため)。全データをメモリに載せてから出力するので、データの多いサイトでは `memory_limit` で止まることがあります。
- **開発中にマイグレーションを追加すると、実行するまで画面がメンテナンス中になります。** テスト用データベースにも実行しておかないと、シナリオテストがメンテナンス画面で止まります(→ `/?_mode=db_migrate&test=<任意>` で実行できます)。
- データベースの形式が変わる更新では、新しいコードで未ログインからログインできないことがあります。そのため、利用者の手順は「先にログインしてから上書き」にしています。それでも入れないときは、`DEBUG_PASSWORD` を設定して `/?_mode=db_migrate` を開きます。
- マイグレーションの実行は levis の `db_migrate_execute()` です。

**リリースは自動です。** `main` への push で `app/version.php` が変わると、[.github/workflows/release.yml](.github/workflows/release.yml) がタグ(`v<バージョン>`)と Release を作り、`freo2-<バージョン>.zip` を添付します。同じタグがあるときは何もしません。ハイフンの付いたバージョン(`2.0.0-alpha-27` など)には Pre-release の印が付きます。

**ZIP には `app/removed_files.txt`(削除済みファイルの一覧)が入ります。** リポジトリにはコミットしません。

- 中身は、履歴で一度でも追跡して今は無いファイルです。リリースのたびに全履歴から作り直すので、バージョンを飛ばしても漏れず、もう一度追加したファイルは載りません。ファイルを消すときに、一覧を手で直す必要はありません。
- `files/`・`libs/vendor/` と、`sample` 以外のプラグイン・テーマは対象外です。
- 形式は `sha256sum` の出力と同じ(ハッシュ、空白2つ、パス)で、1つのパスに過去の版の数だけ行があります。ハッシュは CRLF を LF にそろえてから計算します。
- サーバーのファイルの内容が過去の版のどれかと一致するときだけ削除し、一致しないものは一覧で知らせます。
- 一覧に載ったマイグレーションは、サーバーに残っていても実行しません。

---

## 12. デバッグと開発ツール

### DEBUG_LEVEL

| 値 | 動作 |
| --- | --- |
| `0` | 本番用。開発ツールは使えない |
| `1` | 開発用。`?_mode=info_levis` などの開発ツールが使える |
| `2` | `1` に加えて、処理時間・メモリ使用量と、実行したSQLを画面に出力する |

### 開発ツール(`DEBUG_LEVEL` が 1 以上)

| URL | CLI(`index.php` のあるディレクトリで実行) | 内容 |
| --- | --- | --- |
| `/?_mode=info_levis` | `php index.php info_levis` | フレームワークの情報と各ツールへのリンク |
| `/?_mode=info_php` | `php index.php info_php` | `phpinfo()` |
| `/?_mode=db_admin` | ― | 簡易DB管理 |
| `/?_mode=db_migrate` | `php index.php db_migrate` | マイグレーション |
| `/?_mode=test_index` | `php index.php test_index` | 単体テストの一覧 |

CLI で実行するときは、設定ファイルやマイグレーションの場所を相対パスで扱うため、**必ず `index.php` のあるディレクトリをカレントディレクトリにします。**

**CLI で画面を描画する:** 最初の引数を `/` で始めると、そのURLを GET で開いたときと同じ画面を標準出力に出します(`php index.php /entry/`、`php index.php "/entry/?keyword=a"`)。判定は levis の `cliurl()` です。セッションとクッキーは無いので、未ログインとして描画されます。`warning()`・`error()` は画面ではなく `WARNING: ...`・`ERROR: ...` の1行を出し、`redirect()` は何も出しません。

### その他

- 変数の中身を見る: `debug($value);`(`var_dump` を `<pre>` で囲んで出力)
- 開発中にメールの内容を確認する: `APP_MAIL_SEND` を `null`、`APP_MAIL_LOG` を `true` にすると、`mail/<日付>/` にテキストで記録されます(`index.php` と同じ階層の `mail/` はあらかじめ作成しておく)

---

## 13. テスト

単体テストとブラウザテスト(シナリオテスト)があり、どちらもデータベースを読み書きします。
テスト中の接続先データベースは、自動で **`<DATABASE_NAME>-test`**(例: `freo2-test`)に切り替わります([app/database.php](app/database.php))。あわせて、[app/config.php](app/config.php) の次の設定も、`config.php` の定数に関係なく固定されます。

| 設定 | テスト中の値 | 理由 |
| --- | --- | --- |
| `mail_send` | `null`(送らない) | テスト用の宛先に実際に送らないため |
| `mail_log` | `true`(記録する) | シナリオテストが記録からメールの内容を確かめるため(`mail/` フォルダが必要です) |
| `storage_type`・`storage_url` | `'file'`・設置URL | S3 などを使う環境でも、テスト用データベースのIDで実際のストレージのファイルを消さないため |
| `file_target` | `files/tests/` の中(`entries/`・`fields/`・`medias/`・`thumbnails/`。一時ファイルの `temps/` はそのまま) | 同じく、テスト用データベースのIDで `files/entries/<ID>/` などの実際のファイルを消さないため。テスト中だけ、シナリオテストのスクリーンショットの保存先 `scenario`(`files/scenarios/`)も加わります |
| `recaptcha_enable` | `false` | シナリオテストのブラウザでは reCAPTCHA の確認を通れないため |

入力中の内容の一時保存のキー(`app_draft_key()`)にも、テスト中は `test:` が入ります。普段使っているブラウザでシナリオを流しても、テスト用データベースの記事の一時保存が、同じ番号の普段の記事に出てこないためです。

テスト中かどうかは、[app/bootstrap.php](app/bootstrap.php) の `app_test()` で判定します。単体テストは `_mode`(`test_index`/`test_exec`)、シナリオテストは `?test=` かセッション(`$_SESSION['test']`)で見るので、**セッションが始まった後**(`database()` 以降)に呼びます。テスト中だけ動きを変えたい処理を足すときも、この関数を使います。

テストはテーブルを空にするので、あらかじめテスト用のデータベースを作っておきます(例: データベース名 `freo2-test`、照合順序 `utf8mb4_general_ci`)。
テーブルを作るには、`config.php` の `DATABASE_NAME` を一時的にテスト用データベースに変えてマイグレーションを実行し、終わったら元に戻します。

> **テストを実行するときは、`DATABASE_NAME` を書き換えないでください。** 接続先は自動で切り替わるので、書き換えたまま実行すると `freo2-test-test` に接続しようとします。以前のバージョンでは「テスト実行時に `DATABASE_NAME` を `freo2-test` に書き換える」手順でしたが、今は不要です。

**どちらも `DEBUG_LEVEL` が 1 以上のときだけ動きます。** 本番(`DEBUG_LEVEL` が 0)では入口も表示されません。

### どちらで何を確認するか

| | 単体テスト(`test/`) | シナリオテスト(`scenario/`) |
| --- | --- | --- |
| 対象 | `app/models/`・`app/services/`・`libs/modules/` の関数 | 画面の流れ(コントローラー・ビュー・JavaScript・セッション) |
| 見るもの | 戻り値、検証メッセージ、データベースの状態 | 画面に出る文言、遷移先、フォームの動き |
| 得意なこと | 境界値や異常値を網羅する。速い | 実際に操作しないと通らない経路を確認する |

**同じ題材でも、両方に書く意味があります。** たとえばエントリーの公開範囲は、単体テストでは `$GLOBALS['authority']` を差し替えて権限ごとの判定を網羅し、シナリオテストでは実際にログインして「ログインすると権限や属性が正しく決まるか」まで通します。
逆に、`error()` を呼ぶ分岐や画面の文言は単体テストでは確認できず、長さの境界値のような細かい条件はシナリオテストでは回しきれません。

### 単体テスト(`test/`)

- ブラウザ: `/?_mode=test_index` から選んで実行
- CLI: `index.php` のあるディレクトリで `php index.php test_exec <ファイル名>`
- 参考: [test/model_categories.php](test/model_categories.php)(モデル)、[test/service_category.php](test/service_category.php)(サービス)

書くときの決まりごとです。

- **ファイル名がそのままテスト名になります。** 対象に合わせて `model_<テーブル名>`・`service_<名前>`・`module_<モジュール名>` とします。
- **プラグインのテストは、頭にプラグインコードを付けます**(`plugin_myplugin_model_myplugin_items`・`plugin_myplugin_service_myplugin_item`)。一覧で本体のテストと混ざらないようにするためです。プラグインのモデルは `model()` では読み込めないので `import('plugins/myplugin/app/models/myplugin_items.php')` と書き、検証が選択肢(`$GLOBALS['plugin'][<コード>]['option']`)を参照するなら `import('plugins/myplugin/config.php')` も読み込みます。**プラグインの設定(`['setting']`)は画面からしか入らない**ので、設定で分岐する処理はテスト側で固定します。
- **冒頭と末尾で対象のテーブルを `TRUNCATE` します。** `TRUNCATE` は暗黙のコミットが走るため、`db_rollback()` では元に戻りません。
- **前提データのあるテーブルは `TRUNCATE` しないでください。** `users`(`admin`)・`authorities`・`types`・`settings`・`widgets` はマイグレーションで入るデータが前提になっているので、テスト用に作った行だけを `DELETE` します。`plugins` も同じです(テスト用データベースにインストールしたプラグインを、プラグインのシナリオが前提にするため)。
- **`error()` は最後に `exit` します。** 呼ばれるとテストの実行自体が止まるので、エラーになる分岐は単体テストでは確認できません。
- 長さの検証に使うデータは `str_repeat()` で作り、上限・下限ちょうど(警告なし)とその±1(警告あり)の両方を書きます。文字数を数えなくても読めるようにするためです。繰り返す文字は検証に合わせます(文字数で数える項目は `str_repeat('あ', 20)` のようにマルチバイト文字にしないと、バイト数で数える実装に変わっても気付けません)。

テストファイルは次の形で書きます。

```php
<?php

// 設定ファイルと、テストする対象を読み込む
import('app/config.php');
model('categories.php');

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'categories;');

// トランザクションを開始
db_transaction();

// コードの必須テスト
{
    // データ
    $test_category = [
        'type_id' => 1,
        'code'    => '',
        'name'    => 'テスト1',
        'sort'    => 1,
    ];

    // 確認
    $warnings = model('validate_categories', $test_category);

    // 結果
    test_equals('validate required category code', count($warnings), 1);
}

// トランザクションを終了
db_rollback();

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'categories;');
```

- **テストの中では、モデルもサービスも自動では読み込まれません。** `model('<ファイル名>.php')`・`service('<ファイル名>.php')` で読み込みます。関連するテーブルを使うなら、そのモデル(`model('category_sets.php')` など)も必要です。`Call to undefined function select_xxx()` が出たら読み込み忘れです。
- **データベースへの変更はトランザクションで元に戻します。** ファイルの作成など、トランザクションで戻らないものはテストの中で消します。
- **確認する内容ごとに `{ }` のブロックへ分け、「データ → 確認 → 結果」の順に書きます。** ただし **`{ }` はスコープを作りません。** ブロックの中で使った変数名は、テスト全体の変数を上書きします。
- 検証のテストは、**意図した1件だけ警告が出ること**(`count($warnings) === 1`)を確認する形が定型です。ほかの項目に巻き込まれないよう、重複を確認する `code` などは登録済みの値と衝突させません。
- **設定で変わる挙動は、テスト側で固定します。** メール・ストレージ・reCAPTCHA はテスト中は自動で固定されます(上の表)が、テストで確かめたい値があれば、冒頭で `$GLOBALS['config']` を書き換え、末尾で戻します。アップロード先(`file_target`)はテスト中は `files/tests/` の中になりますが、ファイルを作ったり消したりする単体テストは、ほかのテストやシナリオと混ざらないよう、さらに専用のディレクトリに向けて最後に消します。
- **サービスのテストは、モデルのテストの写しにしません。** サービスが足しているのは操作ログの記録と排他制御(編集画面を開いた後に更新されていないかの確認)、失敗したときの `error()` なので、そこを確認します。
- CLI から実行するときは、冒頭で `$_SERVER['REMOTE_ADDR']` を設定します。操作ログはこの値を `logs.ip`(`NOT NULL`)に入れるので、無いとサービスのテストが途中で止まります。

検証に使う関数です([libs/cores/test.php](libs/cores/test.php))。

| 関数 | 内容 |
| --- | --- |
| `test_equals($title, $actual, $expected)` | `===` で比較する |
| `test_not_equals()` | `!==` で比較する |
| `test_contains()` / `test_not_contains()` | 文字列を含む / 含まない |
| `test_regexp()` / `test_not_regexp()` | 正規表現に一致する / しない(パターンは**デリミタ無し**で渡す) |
| `test_array_haskey()` / `test_array_not_haskey()` | 配列にキーがある / 無い |
| `test_array_subset()` | 配列の中にその行が含まれる(比較は**非厳密**) |

- `test_equals()` は型も見ます。数値の列は `intval()` でそろえてから比較します(データベースから返る型は PHP のバージョンによって変わります)。
- **環境が整っていなくて確認できないテストは、`test_skip($title, $reason)` で飛ばします。** 実行した時点で `SKIP: <タイトル> (<理由>)` と出力され、集計は OK / NG / SKIP の3行になります。たとえば [test/service_media.php](test/service_media.php) は、GD が使えない環境ではサムネイルのテストをまとめて飛ばします。**確認できなかったものを、成功として数えないため**です。

  ```php
  if (!function_exists('imagecreatetruecolor')) {
      test_skip('thumbnail tests', 'GDが有効ではありません。');
  } else {
      // サムネイルのテスト
  }
  ```

- コードカバレッジを出すなら、冒頭で `service('coverage.php'); service_coverage_start();`、末尾で `service_coverage_output(service_coverage_end(), ['app/models/categories.php']);` を呼びます(Xdebug の `coverage` モードが必要)。一括テストのときは邪魔になるので、参考実装のように `if (!isset($_GET['_test']))` で囲みます。

### シナリオテスト(`scenario/`)

実際のブラウザで画面を操作するテストです。

- `/tool/test` を開き、実行するシナリオを選びます(「All Test.」で全シナリオを続けて実行)
- シナリオは `scenario/<名前>.js` に、関数の配列(`test.scenario = [...]`)として書きます
- 参考: [scenario/category.js](scenario/category.js)(登録 → 編集 → 削除)

**1ページの表示につき1ステップだけ実行し、そのステップが起こした画面遷移の先で次のステップに進む**、という作りです。

シナリオは次の形で書きます([scenario/category.js](scenario/category.js) を短くしたものです)。

```js
/* 一覧から、コードで対象の行を取得する */
var categoryRow = function(code) {
    return $('table tbody tr').filter(function() {
        return $(this).find('td code').text().trim() === code;
    });
};

test.scenario = [
    // 初期ページ: ログインページへ移動する
    function() {
        test.assertExists('a:contains("ログイン")', '初期ページにログインへのリンクがありません。');
        test.click('a:contains("ログイン")');
    },
    // ログインページ: ログインする
    function() {
        var form = $('form:eq(0)');

        test.assert(form.find('input[name="username"]').length === 1, 'ログインフォームが表示されていません。');

        form.find('input[name="username"]').val('admin');
        form.find('input[name="password"]').val('abcd1234');
        test.click('form:eq(0) button[type="submit"]');
    },
    // 管理画面: ログインできたことを確認して、カテゴリー管理へ移動する
    function() {
        test.assertText('body', '管理者さん', 'ログインできていません。');
        test.click('a:contains("カテゴリー管理")');
    },
    // カテゴリー一覧: 前回のデータが残っていないことを確認して、登録ページへ移動する
    function() {
        test.assert(categoryRow('test1').length === 0, 'カテゴリー test1 が残っています。前回のテストが中断した可能性があります。');
        test.click('a:contains("カテゴリー登録")');
    },
    // カテゴリー登録: 入力して送信する
    function() {
        var form = $('form.register');

        test.assert(form.find('input[name="code"]').length === 1, '登録フォームが表示されていません。');

        form.find('input[name="code"]').val('test1');
        form.find('input[name="name"]').val('テスト1');
        form.find('select[name="type_id"]').val('1');
        test.click('form.register button[type="submit"]');
    },
    // カテゴリー一覧: 登録できたことを確認する(このあと編集・削除と続けて、最後に消す)
    function() {
        test.assertText('div.alert-success', 'カテゴリーを登録しました。', 'カテゴリーを登録できていません。');
        test.assert(categoryRow('test1').length === 1, '登録したカテゴリーが一覧にありません。');

        test.click(categoryRow('test1').find('a'), '一覧の test1 の編集リンクが見つかりません。');
    }
];
```

**各ステップは「そのページの検証 → 次のページへの遷移」の順に書きます。** 1つ前のステップの結果は、遷移した先のこのステップで確かめます。ここから次の決まりごとが出てきます。

- **ステップの中で必ず画面遷移を起こします。** 遷移しないと次に進まず、タイムアウトで中断します。遷移を伴わない操作(メニューを開く、送信前の入力内容検証)は、同じステップの中で `setTimeout` や `test.wait()` のコールバックを経てから遷移させます。
- **操作するだけでなく、必ず結果を検証します。** 検証が無いと、登録がバリデーションで弾かれてもパンくずのリンクは残っているため次のステップが成功してしまい、**失敗が素通りします。**
- **遷移は `test.click()`・`test.visit()`・`test.reload()` で行います。** `location.href` を直接書くと、検証に失敗して中断した直後にその遷移が走り、中断が取り消されてテストが続いてしまいます。
- **一覧の行は、位置ではなくコードなどで特定します。** `a:eq(0)` のような書き方だと並び順が変わったときに別の行を掴み、テストが無関係なデータを書き換えてしまいます。
- **前提と後始末は、シナリオ自身で面倒を見ます。** セットアップの仕組みは無いので、前提(ログインできるユーザー、テスト用データが残っていないこと)は冒頭の検証で確認し、作ったデータは最後に自分で消します。
- 検証などのヘルパーは、[app/views/test.php](app/views/test.php) の `test` オブジェクトにまとまっています(`test.assert()`・`test.assertText()`・`test.click()` など)。
- **ファイル名は `<対象>_<内容>.js`** とします(対象だけのときは `<対象>.js`)。一覧の並びと一括テストの実行順は**ファイル名順**なので、対象ごとにまとまります。
- **プラグインのシナリオは、単体テストと同じく頭にプラグインコードを付けます**(`plugin_myplugin_<内容>.js`)。本体のシナリオと一覧で混ざりません。
- **プラグインの設定で動きが変わる機能は、シナリオの中で設定を有効にして、最後に元へ戻します。** 管理画面のプラグイン詳細(`/admin/plugin_view?code=<コード>`)の設定フォームをそのまま送信すれば、変更した項目以外は維持されます(登録処理が `setting_define` の全キーを見て、送られていないチェックボックスだけ `0` にするため)。冒頭で「まだ有効になっていないこと」も検証しておくと、前回の中断に気付けます。
- **本体の画面を差し替えるプラグインは、シナリオの中で有効にして、最後に無効へ戻します。** 有効のまま置いておくと、本体の画面を前提にした本体のシナリオが通らなくなります(たとえば、お問い合わせの入力項目を増やすプラグインでは、本体のお問い合わせのシナリオが新しい必須項目で止まります)。テスト用データベースにはインストールだけしておき、冒頭で「無効であること」を検証します。
- **シナリオを書いたら、最後は一括テスト(「All Test.」)で確かめます。** 一括テストは、カートの中身などの**セッションを引き継いだまま**次のシナリオへ進みます(ログイン状態だけは、シナリオが切り替わるたびに破棄されます)。1本ずつ流すと毎回まっさらな状態から始まるので、「前の操作の後に、もう一度同じ操作をすると壊れる」といった不具合は、一括テストでしか出ないことがあります。

---

## 14. はまりやすい点

- **`$_REQUEST` にフォームの値は入っていません。** `$_GET`/`$_POST` を使います(→ [5.1](#51-よく使うグローバル変数))。
- **`redirect()`/`forward()`/`ok()`/`warning()`/`error()` は、その場で `exit` します。** 後ろに書いた処理は実行されません。
- **サービスとプラグインのモデルは自動で読み込まれません。** `Call to undefined function service_xxx()` が出たら `import()` を忘れていないか確認します。
- **`values`/`set` に `''` を渡すと `NULL` として保存されます。** `NOT NULL` の列に空文字を入れようとするとエラーになります。
- **公開側のエントリー取得には `service_entry_select_published()` を使います。** `select_entries` を直接使うと、非公開や公開期間外のエントリーまで表示されます。
- **物理削除(`DELETE`)の `delete_from` に、テーブルの別名(`AS xxx`)を付けないでください。** MariaDB 11.6 未満 / MySQL 8.0.16 未満では構文エラーになります。`select_*`/`update_*` をコピーして作るときに紛れ込みやすい点です。
- **テーブルを作るときは、`COLLATE=utf8mb4_general_ci` まで指定します。** `CHARSET` だけだと、MySQL 8 では照合順序が変わります(→ [11. データベースのマイグレーション](#11-データベースのマイグレーション))。
- **`before.php` の権限チェックは、メニュー・文言を読み込む前に `error()` を呼ぶことがあります。** 公開側のヘッダー・フッターに値を追加するときは、`$GLOBALS['menu_contents']` などが未定義でも動くように `isset()`/`!empty()` で確認します。
- **POST のフォームでは、送信ボタンの `name`/`value` は送られません。** 二重送信を防ぐため、`js/common.js` が送信時に `form[method=post]` の送信ボタンをすべて無効にするからです(無効なボタンの値はブラウザが送らない)。ボタンで処理を分けたいときは、フォームを分けて `<input type="hidden" name="exec" value="...">` で送ります。
- **`_post` 系のコントローラーはURLを直接開いても動きません**(`forward()` 経由でしか実行できない)。動作を確認するときはフォームから送信します。
- **本番では `DEBUG_LEVEL` を必ず `0` にします。** `1` のままだと、誰でもDB管理画面やマイグレーションを開けてしまいます。
