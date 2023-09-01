<?php $this->assign('title', 'event index'); ?>
<?php $this->assign('content-title', 'イベント一覧'); ?>

<!--
https://access.line.me/oauth2/v2.1/authorize?
    response_type=code //code
    &client_id=2000439541 //チャネルID
    &redirect_uri=http://unity.so-good.jp/events/index //コールバックURL
    &state=12345abcde //csrf文字列
    &scope=profile%20openid //取得するデータ範囲
    &nonce=09876xyz //IDトークンに付加する文字列
-->
<a href="https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=2000439541&redirect_uri=http://localhost:8001/users/lineLogin&state=12345abcde&scope=profile%20openid&nonce=09876xyz">
    LINE
</a>


<?php foreach($events as $event): ?>
    <?php echo $this->element('event-item', array('event' => $event, 'displayResponseBtn' => false)); ?>
<?php endforeach; ?>
