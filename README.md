# 概要
ソフトテニスサークルUNITYのイベントスケジュール管理

# 開発環境
OS/Arch:darwin/arm64
Docker:4.21.1
php:7.4
MySQL:5.7.43
CakePHP:4.4.16

# デプロイ
レンタルサーバーにデプロイ中
https://www.extrem.jp/

# 開発手順
Docker利用した開発を推奨。
```sh
$mkdir [directory]
$cd [directory]
$git clone [clone_url]
$docker-compose up -d
```

ローカルには`main` `develop` `fix` `feature/[topic]`を用意して、リモートには`main`のみで管理。
