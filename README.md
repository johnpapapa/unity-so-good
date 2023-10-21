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
```sh
$mkdir [directory]
$cd [directory]
$git clone [clone_url]
$docker-compose up -d
$docker-compose exec app /bin/bash
$mv config/app_local.example.php config/app_local.php
```

## メモ
* 本番環境のDB接続情報などは`app_local.php`の`Datasources`に書き込む。
* 

## ブランチの管理
ローカルには`main` `develop` `feature/[topic]`を用意して、リモートには`main`のみで管理。  
`main` : デプロイ中の最新ver, 直接コミットx  
`develop` : テスト環境の最新ver
`feature/[topic]` : 機能の追加

大小問わず適当な修正は`develop`ブランチ内で修正したのち、テスト環境で問題ないことを確認してから`main`ブランチへのmergeを行なってる。

また`feature/[topic]`ブランチにて機能を追加後には`develop`ブランチへのmergeを行なってる。