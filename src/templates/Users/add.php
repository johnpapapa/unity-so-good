<?php $this->assign('title', 'register'); ?>
<?php $this->assign('content-title', 'ユーザ-新規登録'); ?>
<div class="users form">
<?= $this->Form->create($user) ?>
    <fieldset>
        <label for="username">ユーザーID</label>
        <p style="font-size: 12px; color:gray">
            ログイン時にユーザーIDとして入力する文字列<br>
            半角英数字のみ
        </p>
        <input type="text" name="username" required="required" data-validity-message="This field cannot be left empty" oninvalid="this.setCustomValidity(''); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)" oninput="this.setCustomValidity('')" id="username" aria-required="true" maxlength="255">

        <label for="password">パスワード</label>
        <p style="font-size: 12px; color:gray">
            ログイン時にパスワードとして入力する文字列<br>
            半角英数字のみ
        </p>
        <input type="password" name="password" id="password">

        <label for="display-name">表示名</label>
        <p style="font-size: 12px; color:gray">
            アンケートやイベント作成時に他の人から見える名前<br>
            半角全角可
        </p>
        <input type="text" name="display_name" required="required" data-validity-message="This field cannot be left empty" oninvalid="this.setCustomValidity(''); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)" oninput="this.setCustomValidity('')" id="display-name" aria-required="true" maxlength="255">
   </fieldset>
<?= $this->Form->button(__('Submit')); ?>
<?= $this->Form->end() ?>
</div>