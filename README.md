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
`main` : デプロイ中の最新ver, 直接コミットx  
`develop` : テスト環境の最新ver, テスト環境で問題ないことを確認してから`main`ブランチへのmerge, 直接コミットx  
`fix` : 大小種類問わず既存の機能の修正, コミット後には`develop`ブランチへのmerge  
`feature/[topic]` : 機能の追加, 追加後には`develop`ブランチへのmerge