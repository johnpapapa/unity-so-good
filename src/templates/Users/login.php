
<form method="post" name="login">
    <input type="hidden" name="_csrfToken" autocomplete="off" value="<?= $this->request->getAttribute('csrfToken') ?>">
    <?= $this->Form->control('username') ?>
    <?= $this->Form->control('password') ?>
    <button type="submit" name="login">login</button>
</form>