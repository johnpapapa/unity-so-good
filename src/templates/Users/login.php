<?php
/**
 * @var \App\View\AppView $this
 */
?>
<?php $this->assign('title', 'login'); ?>
<?php $this->assign('content-title', 'ログイン'); ?>
<?php 
    use Cake\Core\Configure; 
    $host = $this->request->host();
    $param_linelogin = Configure::read('param_linelogin');
    $param_linelogin_secret = Configure::read('param_linelogin_secre');
    if(is_null($param_linelogin_secret)){
        echo '<div style="color:red;font-weight: bold;font-size: large;">LINEログインが使用出来ない状態になっています<br>const_secret.phpを確認してください</div>';
        $url_linelogin = '';
    } else {
        $url_linelogin = 'https://access.line.me/oauth2/v2.1/authorize?'.
            'response_type=code'.
            '&client_id='. $param_linelogin_secret['client_id'].
            '&redirect_uri='. $param_linelogin['redirect_uri'][$host].
            '&state='. bin2hex(openssl_random_pseudo_bytes(16)).
            '&scope='. $param_linelogin['scope'].
            '&nonce='. bin2hex(openssl_random_pseudo_bytes(16));
    }
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


<div class="login-content disp-flex align-center dir-column">
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

    <div class="policy p10" style="width: 350px; border:black 1px solid; background-color: #e6e5e5; border-radius: 5px; margin-top:20px;">
        <span style="font-weight:bold;">LINEログインによる個人情報の取り扱い</span>
        <div class="policy-content mt10 mb10" style="font-size:14px; height:300px; overflow:scroll;">
        <?php  
        $policy = <<<EOF
        このウェブサイト（以下、本サイト）は、ユーザーのプライバシーを尊重し、ユーザー情報の収集と利用に関する方針を以下に示します。
        
        <span style="font-weight:bold;">1. 収集する情報</span>
        
        本サイトは、ユーザーがLINEログインを使用する際、以下の情報を収集します。
        ・表示名


        <span style="font-weight:bold;">2. 情報の利用目的</span>

        収集した情報は、ユーザーが本サイトを利用する際の識別の目的で使用されます。
        

        <span style="font-weight:bold;">3. ユーザー情報の保管</span>
        
        収集した表示名は、本サイトのデータベースに保管されます。
        
        <span style="font-weight:bold;">4. 表示名の変更</span>
        
        表示名は、ユーザーの任意により変更可能です。変更した場合、新しい表示名がデータベースに反映され、今後の利用時に使用されます。
        
        <span style="font-weight:bold;">5. ユーザー情報の他サービスへの提供</span>
        
        本サイトは、LINEログインを通じてユーザー情報を収集しますが、新規登録時以外でLINEに保存されているユーザー情報を引き出すことはありません。
        ユーザー情報は、本サイト内での利用に制限され、他のサービスへの提供は行いません。
        
        <span style="font-weight:bold;">6. プライバシーに関するお問い合わせ</span>
        
        ユーザーは、プライバシーに関するご質問や懸念事項がある場合、以下の連絡先にお問い合わせいただけます。
        
        <span style="font-weight:bold;">[お問い合わせ先]</span>
        連絡先メールアドレス: <a href="mailto:userjpcafe@gmail.com" style="color:blue;">userjpcafe@gmail.com</a>


        本ポリシーは、ユーザー情報の取り扱いに関する方針を示すものであり、本サイトの利用に関して適用されます。
        ポリシーの変更がある場合、本サイト上で通知いたします。
        [最終更新日: 2023年9月29日]
        EOF;
        ?>
        <?= nl2br($policy) ?>
        </div>
    </div>
</div>



