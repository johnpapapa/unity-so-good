<?php $this->assign('title', 'login'); ?>
<?php $this->assign('content-title', 'ログイン'); ?>

<style>
    
</style>

<div class="login-content disp-flex just-center">
    <div class="login form pure-g pure-form pure-form-stacked mb10">
        <?= $this->Form->create() ?>
            <div class="mb10">
                <label for="username">ユーザーID</label>
                <input type="text" name="username" id="username">
            </div>

            <div class="mb10">
                <label for="password">パスワード</label>
                <input type="password" name="password" id="password">
            </div>
        
            <div class="mb10">
                <button type="submit" name="login" class="pure-button pure-button-primary">login</button>
            </div>

            <a href="https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=2000439541&redirect_uri=http://localhost:8001/users/lineLogin&state=12345abcde&scope=profile%20openid&nonce=09876xyz">
                <button class="pure-button" type="button">
                    LINE
                </button>
            </a>

            <a href="<?= $this->Url->build(["controller"=>"Users","action"=>"add"]); ?>">
                <button class="pure-button" type="button">
                    ユーザー登録
                </button>
            </a>
        <?= $this->Form->end() ?>
    </div>

</div>