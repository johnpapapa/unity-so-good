Issueにまとめる気はないのでここにやることまとめる

# やること
- % Admin画面のユーザー詳細にある参加履歴は開催済のものに対する未反応のみ  
/admin/administrators/user-detail/
- % イベント開始1日前に参加から不参加にした人リストの作成


# 過去行う予定だった修正(見直す必要あり)
`*:未対応, -:修正済, - %:修正中, -@:優先度低`
- _ default.phpに記述したelementのcss,jsを集約させたい
- _ 参加人数無制限のタグを追加
- _ events/detailの参加表明欄が更新されない
- _ events/detailの前後関係がid順のため日付関係なくなっている問題の解消
- _ events/detail 参加一覧を左に配置する
- _ users/detailのパスワードを空欄にすると空文字列でhashされる問題(解消済み)
- _ event-item 参加=>参加者確認に修正
- _ event-item タグに色がついてない問題
- _ lineログインとpc用のログイン併用
- _ ログインする度にcookieが更新されてしまうので、永遠にログインする必要がなくなる処理を修正
- _ 普通のログイン処理をしたときにもcookieが設定されるように修正
- _ events/addで人数無制限のチェックをしたときにparticipants_limitが存在しないエラーの解消(解消済み)
- _ events/add 日付カレンダーの対策(キーボードフォーカスなし)=>日付を文字列で入力する場所と押してカレンダーが表示される場所を分けた=>カレンダーにしてもフォーカスがうつるのは変わらないのでreadonlyにしてキーボードを表示させないようにした
- _ events/addのコート名入力のautocompleteが効かない=>inputのtext入力でautocompleteを表示,autocompleteの候補から選択した場合にのみ既存のlocation_dataを使用,それ以外はlocation_data新規作成
- _ events/add 時刻を文字列ではなく10分刻みのくるくる => 10分刻み未対応/とりあえずは入力しやすいことを優先
-_ イベントの編集,削除=>削除されたイベントはわかりやすく灰色にする
- _ 削除フラグは(時刻:null)ではなく(0:1)にする
- _ events/createdには作成画面を表示(一覧はその下か別ページ？), 編集ボタン追加=>ボタン同士を離すか角丸めを限定するか
- _ cookieの処理を切り出し
- _ events/detail 参加情報の名前の横にある時刻の位置を調整
- _ events/detail コート使用料に従って金額表示
- _ events/addのコート名入力のautocompleteが効かない=>inputのtext入力でautocompleteを表示,autocompleteの候補から選択した場合にのみ既存のlocation_dataを使用,それ以外はlocation_data新規作成
- _ events/add 時刻を文字列ではなく10分刻みのくるくる => 10分刻み未対応/とりあえずは入力しやすいことを優先
- _ events/add 日付カレンダーの対策(キーボードフォーカスなし)=>日付を文字列で入力する場所と押してカレンダーが表示される場所を分けた=>カレンダーにしてもフォーカスがうつるのは変わらないのでreadonlyにしてキーボードを表示させないようにした
<9_ /8>
- _ events/detail 削除済みのイベントには作成者以外がアクセスできないようにする
- _ event-item 開催中のタグが表示されない
- _ テニスコートの住所入力必須解除
- _ 共有なし
- _ 通常ログインは廃止、LINEログインボタンのみ
- _ /events/participant/ 参加予定イベントは今日以降にする、今以降ではない
- _ 画像をunityの画像にする
- _ ssl対応させるためにcookieのパスを指定(https://book.cakephp.org/4/ja/controllers/request-response.html#creating-cookies)=>他のcookie(cakephp自体から発送されるcookieの位置を指定できないと共有sslでのリスクがあるので、今回はドメインを新しく契約して対処)
- _ events/participates 参加予定のものに不参加のやつもある
- _ 背景画像を設定したため、body要素のbottomで背景が切れる
- _ 管理画面作成
- _ 管理画面=>管理画面に投票してない人表示
- _ ボタンが思った領域の外にはみでてるかもしれない(aタグ直下buttonタグにmargin-bottomを指定してると表示されている領域より外に判定がある)ので要修正
- _ イベントの検索(コート別)/並び替え(日付)は必要か？=>なし
- _ events/detail 参加、参加未定のタイトルの下の余白
- _ events/edit コート情報の変化(既存のコートが入力されたことを示す文言に変化)
- _ 星を出す
  _   (https://codepen.io/mtedwards/pen/AbEVBM)
  _   (https://www.jqueryscript.net/animation/twinkling-stars-effect-starlight.html)
  _   (https://www.codewithrandom.com/2023/05/03/css-star-animation/)
  _   (https://web-dev.tech/front-end/javascript/glittering-effect-on-hover/#index_id4)
- _ 管理画面 反応してない数をまとめる
- _ migration DBのbooleanにするべき箇所をint(11)で管理してるのでtinyint(1)に変更
(h_ ttps://blog.masuyoshi.com/cakephp3-4- @E3- @83- @9E- @E3- @82- @A4- @E3- @82- @B0- @E3- @83- @AC- @E3- @83- @BC- @E3- @82- @B7- @E3- @83- @A7- @E3- @83- @B3smallint- @E3- @81- @A8tinyint/)
- _ events/detail 開催後のイベントには反応できないようにする
- _ events/detail event_responsesで反応した結果をそのまま直で参加者情報に表示させる処理を追加
- _ locationsのナイター料金(night_price)は合計(日中の料金+照明代)にする
- _ DBのカラム名が気に食わない(usernameとか) =>公開時に
<9_ /13みんなに触ってもらった結果>
- _ ヘッダーを固定
- _ events/[create | edit] コート名必須
- _ events/index 開催済みのイベントはアーカイブ
- _ bottom-nav 今表示中のタブがどれか判定
- _ 未開催イベントは開催日近い順に表示
- _ コメント機能
- _ events/add コート番号に入力された文字列からコート数算出=>使用料金算出,表示 (入力できる文字列は[*, *, *]か[***]にして、split(',').strip(' ')で個数検出できんじゃない)
- _ サブセット化=>font-family
- _ event-itemをforで呼出すのではない形にする
- _ lineログイン時の個人情報取扱いに関する説明を付け加えれたら付け加えたい
(h_ ttps://blog.socialplus.jp/knowledge/line-login-and-personal-information/#toc2)
- _ /admin/* 管理者であることを常に確認
- _ /events/participants_count 未反応数違う(要調査)
- _ サイトの背景画像の読み込み優先順位を下げる()
- _ デプロイがしやすいように処理=>環境ごとにDB接続先を変更する(https://shungoblog.com/cakephp-change-db-per-environment/)=>今の所やりようがない
- _ events/detail イベントの人数制限が無制限のとき、参加者情報の反応時間は削除する(反応時間は人数制限あって意味のあるものだし、無制限だと人数多いから情報量えぐい)
- _ 特定のLINE access tokenの場合(登録・ログイン)を許可しない
=>_  管理画面にて許可しないユーザーを追加することでログイン・登録を許可させなく出来る
=>_  BANにするとユーザーを削除する
=>_  現在同名のdisplay_nameを許容しているので,uuidを付与して荒らしが同じ名前で暴れ回るのを対策してもいいかも
-_ ユーザーの削除機能
- _ gitのブランチ上でmasterへのcommit禁止
- _ about 管理画面にaboutの項目編集する
- _ 削除済みのユーザーは反応してもevent_responsesにテーブル追加させないようにする


- @ 試合とか飲み会案内にも対応
- @ エラーログの出力をするようにしたい=>公開時に
- @ sessionが切れていてcookieが残っている時、要認証ページにアクセスすると要認証のはずなのにAuthentication->getResult()->isValid()がfalseになる問題=>一時的にAppController内でuserDataを取得した時にgetResult()がnullの場合再ログインする処理を書き加えた(処理が疎でないため要修正)
- @ 未ログインの場合のredirectをauthenticatorのredirectURLでまとめる
- @ ajax通信におけるcontrollerからの返り値となるresponseの配列を整理
- @ events/index 開催日順に並び替え順ボタン欲しい
- @ パスパラメータをドライにしつつ、各イベントリストでイベントの詳細を見た後それぞれのイベントリストに戻れるように
    (https://book.cakephp.org/4/en/development/routing.html#using-router-reverse)
    (/event-item/index/10 == /events/detail/10となり、/event-item/indexに戻れるのが理想)
- @ 大文字小文字統一, config/routes.phpのパス名前定義
- @ top-navが収納されサイドメニューを出した時に画面外(余白)をタップしても収納するようにする
- @ admin/events/index・過去のイベント一覧(日付検索, コート検索, 時刻並べ替え)
- @ Git リポジトリの限定公開/issueで管理
- @ eventComponentとeventsTableの入出力整理(parameterを増やすかconditionsにまとめるか)
- @ ログアウトした際にユーザーのアクセストークンを削除する処理を追加(https://developers.line.biz/ja/docs/line-login/managing-users/#logout)
- @ セッションが切れた状態だとログインのクエリが2回飛ぶので、それを解消したい
- @ DBへのsave処理でエラーがあった場合に追いやすくするために処理を書き換えたい
    ```php
    <?php
    ...
    try {
        $result = $this->Comments->saveOrFail($comment_data);
        } catch (\Cake\ORM\Exception\PersistenceFailedException $e) {
            // $response['error'] = print_r($e);
            $response['entity'] = $e->getEntity();
            // $repsonse['model'] = print_r($this->Comments);
            debug($e);
        }
    ?>
    ```
- @ ボタンを押した場合の処理で遷移を伴わない(aタグに囲まれてないもの)は一時的にボタンをdisabledにする(連打対策)
- @ locationsにナイターの開始時間を記録するカラム追加 => 追加や編集時のコート代算出や詳細のコート代が昼夜それぞれに分けられる状態が望ましい,不正な時間を弾く？
- @ events/edit ナイター開始時間を空にしたい場合の処理
- @ admin/events/participants_count sqlの効率化
- @ cronで定期的に処理を実行するようにして、期限が近いものに関してnotificationを出すような処理


* /* クッキーの情報やDBの状態に応じてしっかりログアウト

* テストのためにDB書き込み時のresultはlogを記録
* インターネット切断時表示が崩れるため、pureに依存しない形式(最悪無くても実動作する際にはNW必須なので関係なかったりする)

* events/[add, edit] 同名のコートは同じものとして紐付け

* events/[add, edit] コート代の算出のためにareaの入力を網羅([*,*,]の時countが2になるのが望ましい)


* admin/user-detail コート別の参加率を表示させる時全ての場所のリストを表示させるべき
* saveした時のエラーはpatchEntityの'[errors]' => ['about' => [***],]に出てくるのでこの値をlogに出せるようにしたい

* locationsの詳細ページがあってもいいかも
* events/detail Locationsのaddressを使用してMAPサービスと紐づける(locationsテーブルに座標値等を格納する必要)
* LINEログインの脆弱性をあらためて確認する
https://logmi.jp/tech/articles/323628
* コピーして新規作成する機能はほしい？
* サイトの変更点を更新履歴という形で表示させたい
* gitのhookをリポジトリで管理したい
https://qiita.com/ik-fib/items/55edad2e5f5f06b3ddd1