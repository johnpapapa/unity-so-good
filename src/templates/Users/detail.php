<?php $this->assign('title', 'user detail'); ?>
<?php $this->assign('content-title', 'ユーザー詳細'); ?>

<div class="user-detail-content disp-flex just-center">
    <div class="detail form pure-g pure-form pure-form-stacked">
        <?= $this->Form->create() ?>
        <a class="nostyle-a" href="<?= $this->Url->build(['controller' => 'users','action' => 'logout']); ?>">
            <button class="pure-button mb10" type="button">
                ログアウトする
            </button>
        </a>

        <div class="mb10">
            <label for="display_name">表示名</label>
            <input type="text" class="pure-u-1" id="display_name" name="display_name" required="required" data-validity-message="This field cannot be left empty" value="<?= $current_user->display_name ?>" maxlength="255">
        </div>

        <div class="mb30">
            <label for="username">ID</label>
            <input type="text" class="pure-u-1" id="username" name="username" required="required" data-validity-message="This field cannot be left empty" value="<?= $current_user->username ?>" maxlength="255">
        </div>

        <div class="mb30">
            <div class="mb10">
                <label for="password">パスワード</label>
                <p class="note-p">パスワードを変更しない場合は空欄にしてください</p>
                <input type="password" class="pure-u-1" name="password" id="password" value="">
            </div>

            <div class="mb10">
                <label for="password_confirm">パスワード再確認</label>
                <input type="password" class="pure-u-1" name="password_confirm" id="password_confirm" value="">
            </div>
        </div>

        <div class="mb10">
            <button type="submit" name="login" class="pure-button pure-button-primary">変更を保存</button>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>

<script>
    let obj_password = $('#password');
    let obj_password_confirm = $('#password_confirm');
    let obj_submit = $('button[type=submit]');

    $([obj_password[0], obj_password_confirm[0]]).on('keyup', function () {
        if (obj_password.val() == obj_password_confirm.val()) {
            // $('#message').html('Matching').css('color', 'green');
            obj_submit.prop("disabled", false);
        } else {
            // $('#message').html('Not Matching').css('color', 'red');
            obj_submit.prop("disabled", true);
        }
    });
</script>