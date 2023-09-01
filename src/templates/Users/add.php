<?php $this->assign('title', 'register'); ?>
<?php $this->assign('content-title', 'ユーザー新規登録'); ?>


<div class="user-detail-content disp-flex just-center">
    <div class="detail form pure-g pure-form pure-form-stacked">
        <?= $this->Form->create($user) ?>
            <div class="mb10">
                <label for="username">ユーザーID</label>
                <p class="note-p">
                    ログイン時にユーザーIDとして入力する文字列<br>
                    半角英数字のみ
                </p>
                <input type="text" class="pure-u-1" name="username" required="required" data-validity-message="This field cannot be left empty" oninvalid="this.setCustomValidity(''); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)" oninput="this.setCustomValidity('')" id="username" aria-required="true" maxlength="255">

            </div>
            <div class="mb10">
                <label for="password">パスワード</label>
                <p class="note-p">
                    ログイン時にパスワードとして入力する文字列<br>
                    半角英数字のみ
                </p>
                <input type="password" class="pure-u-1" name="password" id="password">
            </div>

            <div class="mb10">
                <label for="display-name">表示名</label>
                <p class="note-p">
                    アンケートやイベント作成時に他の人から見える名前<br>
                    半角全角可
                </p>
                <input type="text" class="pure-u-1" name="display_name" required="required" data-validity-message="This field cannot be left empty" oninvalid="this.setCustomValidity(''); if (!this.value) this.setCustomValidity(this.dataset.validityMessage)" oninput="this.setCustomValidity('')" id="display-name" aria-required="true" maxlength="255">
            </div>

            <div class="mb10">
            <button type="submit" name="login" class="pure-button pure-button-primary">新規登録</button>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>
