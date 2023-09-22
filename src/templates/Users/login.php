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
<p class="note-p mb20">
    メンバー専用画面へアクセスするには<br>
    ログインが必要です。
</p>


<div class="login-content disp-flex just-center">
    <div class="login form pure-g pure-form pure-form-stacked mb10">
        <?= $this->Form->create() ?>
            <div class="mb30">
                <a href="<?= $url_linelogin ?>">
                    <button class="pure-button line-login-btn pure-u-1" type="button">
                        LINEでログイン
                    </button>
                </a>
                <p class="note-p">
                    アカウント未作成の方がLINEログインを行うと<br>
                    自動でアカウントが作成されます。
                </p>
            </div>
        <?= $this->Form->end() ?>
    </div>

</div>