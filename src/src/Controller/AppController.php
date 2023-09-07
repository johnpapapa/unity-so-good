<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Datasource\FactoryLocator;
use Cake\Controller\Controller;
use Cake\Http\Cookie\Cookie;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use DateTime;
use Cake\Routing\Router;
use Psr\Log\LogLevel;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/4/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication'); //認証ロジック

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/4/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    public function beforeFilter(EventInterface $event)
    {
        //ログインユーザ情報の取得
        $login_user_data = $this->getLoginUserData();
        if(!$login_user_data){
            $cookie = $this->getCookieAutoLogin();
            if($cookie){
                // cookieが存在する場合にcookieのidを持つuserを検索
                $user_data = FactoryLocator::get('Table')->get('Users')->find('all', ['conditions'=>['remember_token' => $cookie]])->first();
                if($user_data){
                    //cookieのidを持つuserがいた場合ログイン
                    $this->setIdentity($user_data);
                    $login_user_data = $user_data;
                } else { //userが存在しない場合はcookieを削除
                    $this->removeCookieAutoLogin();
                }
            }
        }
        $this->set("current_user", $login_user_data);
    }

    public function isActiveCookieData(){ //有効なcookieが存在する場合true
        $cookie = $this->getCookieAutoLogin();
        $result = false;
        if($cookie){
            $result = FactoryLocator::get('Table')->get('Users')->exists(['remember_token' => $cookie]);
        }
        return $result;
    }

    public function getUserDataFromCookie(){
        $cookie = $this->getCookieAutoLogin();
        $user_data = FactoryLocator::get('Table')->get('Users')->find('all', ['conditions'=>['remember_token' => $cookie]])->first();
        if($user_data){
            return $user_data;
        }
        return null;
    }

    public function getLoginUserData($id_only=false){
        $user_data = $this->Authentication->getResult()->getData();        
        if($this->isActiveCookieData()){
            $user_data = $this->getUserDataFromCookie();
            $this->setIdentity($user_data);
        }
        if($id_only && $user_data){
            return $user_data['id'];
        }
        return $user_data;
    }

    public function getLineUserData(){
        $postData = array(
            'grant_type'    => 'authorization_code',
            'code'          => $this->request->getQuery('code'),
            'redirect_uri'  => Configure::read('param_linelogin.redirect_uri')[$this->request->host()],
            'client_id'     => '2000439541', //非公開予定
            'client_secret' => 'b3b4212b5b7760b442883bb88b1f21f1' //非公開予定
          );
        
        $ch1 = curl_init();
        curl_setopt($ch1, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch1, CURLOPT_URL, 'https://api.line.me/oauth2/v2.1/token');
        curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch1, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
        $response1 = curl_exec($ch1);
        curl_close($ch1);
        
        $json1 = json_decode($response1);
        if(isset($json1->error)){
            $error_str = 'LINEログインに失敗しました:accessTokenの取得に失敗しました. ' . '[' . $json1->error_description . ']' ;
            $this->Flash->error($error_str);
            return $this->redirect(['controller'=>'Events','action'=>'index']);
        }
        $accessToken = $json1->access_token;

        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
        curl_setopt($ch2, CURLOPT_URL, 'https://api.line.me/v2/profile');
        curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        $response2 = curl_exec($ch2);
        curl_close($ch2);

        $json2 = json_decode($response2);
        if(!isset($json2->userId)){
            $error_str = 'LINEログインに失敗しました:profileの取得に失敗しました. ' . '[' . $json2->message . ']';
            $this->Flash->error($error_str);
            return $this->redirect(['controller'=>'Events','action'=>'index']);
        }

        $line_user_data = [
            "line_user_id" => (isset($json2->displayName)) ? $json2->userId : '',
            "display_name" => (isset($json2->userId)) ? $json2->displayName : ''
        ];
        return $line_user_data;
    }

    public function genKeyAutoLogin(){
        return hash('sha256', (uniqid() . mt_rand(1, 999999999) . '_auto_logins'));
    }

    public function setIdentity($user_data){
        return $this->Authentication->setIdentity($user_data);
    }

    public function removeIdentity(){
        return $this->Authentication->logout();
    }

    public function getCookieAutoLogin(){
        return $this->request->getCookie(Configure::read('cookie.key'));
    }

    public function setCookieAutoLogin($key_auto_login){
        $this->response = $this->response->withCookie(Cookie::create(
            Configure::read('cookie.key'),
            $key_auto_login,
            ['expires'=>new DateTime('+ 10 min'), 'http'=>true]
        ));
        return ;
    }

    public function removeCookieAutoLogin(){
        $this->response = $this->response->withCookie(Cookie::create(
            Configure::read('cookie.key'),
            '',
            ['expires'=>new DateTime('-1 day'), 'http'=>true]
        ));
        return ;
    }

    public function setDataAutoLogin($key_auto_login, $user_data){
        $update_data = $this->Users->patchEntity($user_data, [
            'id' => $user_data['id'],
            'remember_token' => $key_auto_login
        ]);

        $result = $this->Users->save($update_data);
        if(!$result){
            $this->Flash->error('cannot set user identity. retry login.');
            return true;
        }
        return false;
    }

    public function removeDataAutoLogin($user_data){
        $update_data = $update_data = $this->Users->patchEntity($user_data, [
            'id' => $user_data['id'],
            'remember_token' => null
        ]);
        $result = $this->Users->save($update_data);
        if(!$result){
            $this->Flash->error('cannot remove remember_token. please contact server administrator');
            return false;
        }
    }
}
