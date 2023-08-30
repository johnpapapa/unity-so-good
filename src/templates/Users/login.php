<?php $this->assign('title', 'login'); ?>
<?php $this->assign('content-title', 'ログイン'); ?>

<form method="post" name="login">
    <input type="hidden" name="_csrfToken" autocomplete="off" value="<?= $this->request->getAttribute('csrfToken') ?>">
    <!-- <?= $this->Form->control('username') ?> -->
    <label for="username">ユーザーID</label>
    <input type="text" name="username" id="username">

    <!-- <?= $this->Form->control('password') ?> -->
    <label for="password">パスワード</label>
    <input type="password" name="password" id="password">

    <button type="submit" name="login">login</button>
</form>

<a href="<?= $this->Url->build(["controller"=>"Users","action"=>"add"]); ?>">ユーザー登録</a>