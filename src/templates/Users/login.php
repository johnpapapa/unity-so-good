<?php $this->assign('title', 'login'); ?>
<?php $this->assign('content-title', 'ログイン'); ?>
<?php 
    use Cake\Core\Configure; 
    $host = $this->request->host();
    $param_linelogin = Configure::read('param_linelogin');
    $url_linelogin = 'https://access.line.me/oauth2/v2.1/authorize?'.
        'response_type=code'.
        '&client_id='. $param_linelogin['client_id'].
        '&redirect_uri='. $param_linelogin['redirect_uri'][$host].
        '&state='. bin2hex(openssl_random_pseudo_bytes(16)).
        '&scope='. $param_linelogin['scope'].
        '&nonce='. bin2hex(openssl_random_pseudo_bytes(16));
        
?>
<style>
    .login-content .line-login-btn {
        background-color: #06C755;
        color: white;
        font-weight: bold;
    }
</style>

<div class="login-content disp-flex just-center">
    <div class="login form pure-g pure-form pure-form-stacked mb10">
        <?= $this->Form->create() ?>
            <div class="mb30">
                <!-- <a href="https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=2000439541&redirect_uri=http://localhost:8001/users/lineLogin&state=12345abcde&scope=profile%20openid&nonce=09876xyz"> -->
                <a href="<?= $url_linelogin ?>">
                    <button class="pure-button line-login-btn pure-u-1" type="button">
                        LINEでログイン
                    </button>
                </a>
            </div>
            <div class="mb10">
                <label for="user_id">ユーザーID</label>
                <input type="text" name="user_id" id="user_id">
            </div>

            <div class="mb10">
                <label for="password">パスワード</label>
                <input type="password" name="password" id="password">
            </div>
        
            <div class="mb10">
                <button type="submit" name="login" class="pure-button pure-button-primary">login</button>
            </div>

            <a href="<?= $this->Url->build(["controller"=>"Users","action"=>"add"]); ?>">
                <button class="pure-button" type="button">
                    ユーザー登録
                </button>
            </a>
        <?= $this->Form->end() ?>
    </div>

</div>