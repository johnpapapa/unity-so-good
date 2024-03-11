# 概要
ソフトテニスサークルUNITYのイベントスケジュール管理

# 開発環境
OS/Arch:darwin/arm64
Docker:4.21.1
php:7.4
MySQL:5.7.43
CakePHP:4.4.16

# デプロイ
レンタルサーバーを使用してデプロイ中  
https://www.extrem.jp/

公開URL  
https://unity-so-good.com/

# 開発手順
Docker利用した開発を推奨。

1. コンテナの用意

```sh
$mkdir [directory]
$cd [directory]
$git clone [clone_url]
$docker-compose up -d
$docker-compose exec app /bin/bash
```

2. app_local.phpとconst_secret.phpを用意
3. app_local.phpとconst_secret.phpを配置

```sh
$cp [app_local_path] [directory]/src/config/app_local.php
$cp [const_secret_path] [directory]/src/config/const_secret.php
```

4. composerのインストール(3/12時点)

```sh
$composer install
```

5. migration(3/12時点)

```sh
$bash migrate.sh
```



## メモ
* 環境用意するときの`PHP Warning:  PHP Startup: Unable to load dynamic library '/path/to/intl.so'`はまだ解消してない(開発できればいいやの精神)
* 本番環境のDB接続情報などは`app_local.php`の`Datasources`に書き込む。
* CAKE_ENV周りの設定はapp_local.phpでSeed/DB設定管理, const_secret.phpでLINEログイン等設定管理してる=>ignoreに追加
* 実装がひと段落つくたびにannotateをつける  
  (cakephp4/IDE Helperを使用=>`$bin/cake annotate all -r`)
* Seederはかなり適当に書いてる。
* 今はスピード優先して所々適当だけど、MVC関係は以下の責務を意識して記述する。
>  * controllerの責務
>      - 各action内で使用する変数を定義
>      - componentにあるロジックの呼び出し
>      - viewから来た値(post)を必要なだけcomponentに渡す
>      - viewに表示させるFlashメッセージをcomponentから来た値に応じて分岐
>      - 別ページへの遷移をcomponentから来た値に応じて分岐
>      (AppController)
>      - 各ページで共通に使用するデータの定義(user)
>  * componentの責務
>      - 事前に定義されているconst等の呼び出し
>      - controllerから受け取った値/配列を変形
>      - modelにあるデータ読み出しのロジックを呼び出し
>      - modelから受け取った値/配列を変形
>      - modelにデータを書き込む配列の定義
>      - errorLogへの書き込み
>  * model
>      - orm/sqlによるDB読み書き
>      - 変数の宣言は原則なし

* DBコンテナ内でmysqlを起動する場合
```sh
$mysql -u user -h db -ppass
use unitydb;
[sql statement]
exit;
```  

## ブランチの管理
ローカルには`master` `develop` `feature/[topic]`を用意。  
リモートには`master`と`develop`。  

`master` : デプロイ中の最新ver, 直接コミットx  
`develop` : テスト環境の最新ver
`feature/[topic]` : 機能の追加

大小問わず適当な修正は`develop`ブランチ内で修正したのち、テスト環境で問題ないことを確認してから`master`ブランチへのmergeを行なってる。

また`feature/[topic]`ブランチにて機能を追加後には`develop`ブランチへのmergeを行なってる。
