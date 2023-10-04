<?php
/**
 * @var \App\View\AppView $this
 * @var object $information_data
 */
?>
<?php $this->assign('title', 'edit information'); ?>
<?php $this->assign('content-title', '概要編集'); ?>

<div class="information-edit disp-flex just-center">
    <div class="information form pure-g pure-form pure-form-stacked">

        <?= $this->Form->create(); ?>

        <div class="mb10">
            <label for="about">概要</label>
            <textarea class="w100" id="about" name="about" cols="30" rows="10"><?= $information_data->about ?></textarea>
        </div>

        <div class="mb10">
            <label for="rule">ルール</label>
            <textarea class="w100" id="rule" name="rule" cols="50" rows="20"><?= $information_data->rule ?></textarea>
        </div>

        <div class="mb10">
            <button type="submit" class="pure-button pure-button-primary">変更を保存</button>
        </div>
        <?= $this->Form->end(); ?>
    </div>
</div>