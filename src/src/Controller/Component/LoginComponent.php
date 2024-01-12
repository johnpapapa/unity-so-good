<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\Http\Cookie\Cookie;
use Cake\Log\Log;
use DateTime;

/**
 * @property \Cake\Controller\Component\RequestHandlerComponent $RequestHandler
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 * @method \App\Controller\AppController getController()
 */
class loginComponent extends Component
{
    protected $components = ['RequestHandler', 'Authentication.Authentication', 'Flash'];
    private $controller;
    private $request;

    public function initialize(array $config): void
    {
        $this->Locations = FactoryLocator::get('Table')->get('Locations');
        $this->Events = FactoryLocator::get('Table')->get('Events');
        $this->Users = FactoryLocator::get('Table')->get('Users');
        $this->EventResponses = FactoryLocator::get('Table')->get('EventResponses');
        $this->RejectedTokens = FactoryLocator::get('Table')->get('RejectedTokens');
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        $this->controller = $this->getController();
        $this->request = $this->controller->getRequest();
        $this->response = $this->getController()->getResponse();
    }

    
    // ログインする際にCookie情報とDB情報を更新する処理
    public function processSetLogin($login_user_data, $is_cookie_update = true)
    {
        $this->setIdentity($login_user_data);
        if ($is_cookie_update) {
            $key_auto_login = $this->genKeyAutoLogin();
            $this->setCookieAutoLogin($key_auto_login);
            $this->setDataAutoLogin($login_user_data, $key_auto_login);
        }

        return $login_user_data;
    }

    // Cookieが存在する場合にCookie情報を使用してログインを試行する処理

    public function processCookieAutoLogin()
    {
        $cookie = $this->getCookieAutoLogin();
        if ($cookie) {
            $user_data = FactoryLocator::get('Table')->get('Users')->find('all', ['conditions' => ['remember_token' => $cookie]])->first();
            // BAN確認
            if ($user_data && !$this->isRejected($user_data["line_user_id"]) ) {
                $this->processSetLogin($user_data, false); //有効なCookieの場合はDBとCookie情報を更新しない
                return $user_data;
            } else {
                $this->removeCookieAutoLogin();
            }
        }

        return false;
    }

    //Cookie情報に使用するキーを生成

    public function genKeyAutoLogin()
    {
        return hash('sha256', (uniqid() . mt_rand(1, 999999999) . '_auto_logins'));
    }

    //Authenticationコンポーネントに認証情報を譲渡

    public function setIdentity($user_data)
    {
        return $this->Authentication->setIdentity($user_data);
    }

    //Authenticationコンポーネントにある認証情報を削除

    public function removeIdentity()
    {
        return $this->Authentication->logout();
    }

    //Cookie情報を登録

    public function setCookieAutoLogin($key_auto_login)
    {
        $this->response = $this->response->withCookie(Cookie::create(
            Configure::read('cookie.key'),
            $key_auto_login,
            ['expires' => new DateTime('+ 1 month'), 'http' => true]
        ));
        $this->getController()->setResponse($this->response);

        return;
    }

    //Cookie情報を削除

    public function removeCookieAutoLogin()
    {
        $this->response = $this->response->withCookie(Cookie::create(
            Configure::read('cookie.key'),
            '',
            ['expires' => new DateTime('-1 day'), 'http' => true]
        ));
        $this->getController()->setResponse($this->response);

        return;
    }

    //Cookie情報の取得

    public function getCookieAutoLogin()
    {
        return $this->request->getCookie(Configure::read('cookie.key'));
    }

    //有効なcookieが存在するか

    public function isActiveCookieData()
    {
        $cookie = $this->getCookieAutoLogin();
        $result = false;
        if ($cookie) {
            $result = FactoryLocator::get('Table')->get('Users')->exists(['remember_token' => $cookie]);
        }

        return $result;
    }

    //line login set

    public function setDataAutoLogin($user_data, $key_auto_login)
    {
        $update_data = FactoryLocator::get('Table')->get('Users')->patchEntity($user_data, [
            'id' => $user_data['id'],
            'remember_token' => $key_auto_login,
        ]);
        Log::write('debug', print_r($update_data, true));
        $result = FactoryLocator::get('Table')->get('Users')->save($update_data);
        if (!$result) {
            $this->Flash->error('cannot set user identity. retry login.');

            return false;
        }

        return true;
    }

    //line login remove

    public function removeDataAutoLogin($user_data)
    {
        $update_data = FactoryLocator::get('Table')->get('Users')->patchEntity($user_data, [
            'id' => $user_data['id'],
            'remember_token' => null,
        ]);
        $result = FactoryLocator::get('Table')->get('Users')->save($update_data);
        if (!$result) {
            $this->Flash->error('cannot remove remember_token. please contact server administrator');

            return false;
        }

        return true;
    }

    //ログイン中のユーザー情報を取得

    public function getLoginUserData($id_only = false)
    {
        $user_data = $this->Authentication->getResult()->getData();
        if (!$user_data) { //ログイン中のユーザ情報が取得できない場合Cookie情報によるログインを試行
            $user_data = $this->processCookieAutoLogin();
            if (!$user_data) {
                return false;
            }
        }

        if ($id_only && $user_data) {
            return $user_data['id'];
        }
        return $user_data;
    }

    //管理者権限を持つuserでログインしているか
    public function isAdministrator()
    {
        $user_id = $this->getLoginUserData(true);
        if (!$user_id) {
            return false;
        }
        $admin = FactoryLocator::get('Table')->get('Administrators')->exists(['user_id' => $user_id]);
        if ($admin) {
            return true;
        }

        return false;
    }

    public function processLineLogin($line_user_data)
    {
        $user_data = FactoryLocator::get('Table')->get('Users')->find('all', ['conditions' => ['line_user_id' => $line_user_data['line_user_id']]])->first();
        if (!$user_data) { //該当するline_user_idが存在しない場合新規ユーザー作成
            $line_user_id = $line_user_data['line_user_id'];
            if($this->isRejected($line_user_id)){ //reject対象の場合ユーザー作成を拒否
                $this->Flash->error(__('有効なアカウントとして認められません'));
                return false;
            }

            $save_data = $this->Users->newEntity([
                'display_name' => $line_user_data['display_name'],
                'line_user_id' => $line_user_data['line_user_id'],
            ]);

            $user_data = FactoryLocator::get('Table')->get('Users')->save($save_data);
            if (!$user_data) {
                // $this->Flash->error(__('The event could not be saved. Please, try again.'));
                // return $this->redirect(['controller'=>'Events','action'=>'index']);
                return false;
            }

            $this->Flash->success(__('ユーザー情報を新規に登録しました'));
        }

        if ($user_data && !is_null($user_data['line_user_id'])){
            $line_user_id = $user_data['line_user_id'];
            if($this->isRejected($line_user_id)){
                $this->Flash->error(__('有効なアカウントとして認められません'));
                return false;
            }
        }

        return $user_data;
    }

    public function isRejected($line_user_id){
        $rejected_data = FactoryLocator::get('Table')->get('RejectedTokens')->find('all', ['conditions'=>['line_user_id'=>$line_user_id]])->first();
        if(!$rejected_data){
            return false;
        }
        return true;
    }

    public function getLineUserData()
    {
        $postData = [
            'grant_type'    => 'authorization_code',
            'code'          => $this->request->getQuery('code'),
            'redirect_uri'  => Configure::read('param_linelogin.redirect_uri')[$this->request->host()],
            'client_id'     => Configure::read('param_linelogin_secret.client_id'),
            'client_secret' => Configure::read('param_linelogin_secret.client_secret'),
        ];

        $ch1 = curl_init();
        curl_setopt($ch1, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch1, CURLOPT_URL, 'https://api.line.me/oauth2/v2.1/token');
        curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch1, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
        $response1 = curl_exec($ch1);
        curl_close($ch1);

        $json1 = json_decode($response1);
        if (isset($json1->error)) {
            $error_str = 'LINEログインに失敗しました:accessTokenの取得に失敗しました. ' . '[' . $json1->error_description . ']';
            Log::write('debug', print_r($error_str, true));

            return false;
        }
        $accessToken = $json1->access_token;
        // $accessToken = '12098';
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
        curl_setopt($ch2, CURLOPT_URL, 'https://api.line.me/v2/profile');
        curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        $response2 = curl_exec($ch2);
        curl_close($ch2);

        $json2 = json_decode($response2);
        if (!isset($json2->userId)) {
            $error_str = 'LINEログインに失敗しました:profileの取得に失敗しました. ' . '[' . $json2->message . ']';
            Log::write('debug', print_r($error_str, true));

            return false;
        }

        $line_user_data = [
            'line_user_id' => isset($json2->displayName) ? $json2->userId : '',
            'display_name' => isset($json2->userId) ? $json2->displayName : '',
        ];

        return $line_user_data;
    }
}
