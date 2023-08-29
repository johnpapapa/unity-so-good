<?php $this->assign('title', 'Users'); ?>
<?php $this->assign('content-title', 'ユーザー詳細'); ?>

<?= $this->Form->create() ?>
    <fieldset>
        <div class="container">
            <div class="row">
                <div class="column">
                    <label for="display_name">Display Name</label>
                    <input type="text" id="display_name" name="display_name" required="required" data-validity-message="This field cannot be left empty" value="<?= $current_user->display_name ?>" maxlength="255">
                </div>
            </div>
            <div class="row">
                <div class="column">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required="required" data-validity-message="This field cannot be left empty" value="<?= $current_user->username ?>" maxlength="255">
                </div>           
            </div>
            <div class="row">
                <div class="column">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" value="">
                </div>

                <div class="column">
                    <label for="password_confirm">Password Confirm</label>
                    <input type="password" name="password_confirm" id="password_confirm" value="">
                </div>
            </div>
        </div>
    </fieldset>
<?= $this->Form->button(__('変更を保存')); ?>
<?= $this->Form->end() ?>


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