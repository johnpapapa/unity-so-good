<?php $this->assign('title', 'user detail'); ?>
<?php $this->assign('content-title', 'ユーザー詳細'); ?>

<div class="user-detail-content disp-flex just-center">
    <div class="detail form pure-g pure-form pure-form-stacked">
        <?= $this->Form->create() ?>
        <a href="<?= $this->Url->build(['controller' => 'users','action' => 'logout']); ?>">
            <button class="pure-button mb10" type="button">
                ログアウトする
            </button>
        </a>

        <div class="mb10">
            <label for="display_name">表示名</label>
            <input type="text" class="pure-u-1" id="display_name" name="display_name" required="required" data-validity-message="This field cannot be left empty" value="<?= $current_user->display_name ?>" maxlength="255">
        </div>

        <div class="mb10">
            <button type="submit" name="login" class="pure-button pure-button-primary">変更を保存</button>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>