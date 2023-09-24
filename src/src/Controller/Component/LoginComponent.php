<?php
 
namespace App\Controller\Component;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Datasource\FactoryLocator;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Cookie\Cookie;
use DateTime;
use Cake\Log\Log;
 
class loginComponent extends Component
{
    protected $components = ['RequestHandler', 'Authentication.Authentication'];
    private $controller;
    private $request;

    public function initialize(array $config): void
    {
        $this->Locations = FactoryLocator::get('Table')->get('Locations');
        $this->Events = FactoryLocator::get('Table')->get('Events');
        $this->Users = FactoryLocator::get('Table')->get('Users');
        $this->EventResponses = FactoryLocator::get('Table')->get('EventResponses');
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        $this->controller = $this->getController();
        $this->request = $this->controller->getRequest();
        $this->response = $this->getController()->getResponse();
    }

    //ログインに成功した場合true(login_user_dataを返すようにする)
    // public function processLogin(){
    //     $result_cookie_auto_login = $this->processCookieAutoLogin();
    //     if ($result_cookie_auto_login){
    //         return $result_cookie_auto_login;
    //     }
    //     return false;
    // }

    public function processSetLogin($login_user_data, $is_cookie_update=true){
        if(!$is_cookie_update){
            $key_auto_login = $this->genKeyAutoLogin();
            $this->setCookieAutoLogin($key_auto_login);
            $this->setDataAutoLogin($login_user_data, $key_auto_login);
        }
        
        return $login_user_data;
    }

    public function processCookieAutoLogin(){
        $cookie = $this->getCookieAutoLogin();
        if($cookie){
            $user_data = FactoryLocator::get('Table')->get('Users')->find('all', ['conditions'=>['remember_token' => $cookie]])->first();
            if($user_data){
                $this->setIdentity($user_data);
                return $user_data;
            } else {
                $this->removeCookieAutoLogin();
            }
        }
        return false;
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

    public function setCookieAutoLogin($key_auto_login){
        $this->response = $this->response->withCookie(Cookie::create(
            Configure::read('cookie.key'),
            $key_auto_login,
            ['expires'=>new DateTime('+ 10 min'), 'http'=>true]
        ));
        $this->getController()->setResponse($this->response);
        return ; 
    }

    public function removeCookieAutoLogin(){
        $this->response = $this->response->withCookie(Cookie::create(
            Configure::read('cookie.key'),
            '',
            ['expires'=>new DateTime('-1 day'), 'http'=>true]
        ));
        $this->getController()->setResponse($this->response);
        return ;
    }

    //cookieによるログイン
    public function getCookieAutoLogin(){
        return $this->request->getCookie(Configure::read('cookie.key'));
    }

    //有効なcookieが存在する場合true
    public function isActiveCookieData(){ 
        $cookie = $this->getCookieAutoLogin();
        $result = false;
        if($cookie){
            $result = FactoryLocator::get('Table')->get('Users')->exists(['remember_token' => $cookie]);
        }
        return $result;
    }

    public function setDataAutoLogin($user_data, $key_auto_login){
        $update_data = FactoryLocator::get('Table')->get('Users')->patchEntity($user_data, [
            'id' => $user_data['id'],
            'remember_token' => $key_auto_login
        ]);

        $result = FactoryLocator::get('Table')->get('Users')->save($update_data);
        if(!$result){
            $this->Flash->error('cannot set user identity. retry login.');
            return false;
        }
        return true;
    }

    public function removeDataAutoLogin($user_data){
        $update_data = FactoryLocator::get('Table')->get('Users')->patchEntity($user_data, [
            'id' => $user_data['id'],
            'remember_token' => null
        ]);
        $result = FactoryLocator::get('Table')->get('Users')->save($update_data);
        if(!$result){
            $this->Flash->error('cannot remove remember_token. please contact server administrator');
            return false;
        }
        return true;
    }

    //ログイン中のユーザー情報を取得
    public function getLoginUserData($id_only=false){
        Log::write('debug', print_r('[getLoginUserData]get login user data', true));
        $user_data = $this->Authentication->getResult()->getData(); 
        Log::write('debug', print_r('[getLoginUserData]authentication get result', true));
        if(!$user_data){
            //try login
            Log::write('debug', print_r('[getLoginUserData]try login', true));
            $user_data = $this->processCookieAutoLogin();
            if(!$user_data){
                Log::write('debug', print_r('[getLoginUserData]error', true));
                return false;
            }
            Log::write('debug', print_r('[getLoginUserData]success', true));
        }
        Log::write('debug', print_r('[getLoginUserData]true user data', true));

        if($id_only && $user_data){
            return $user_data['id'];
        }
        return $user_data;
    }

    //管理者権限を持つuserでログインしているか
    public function isAdministrator(){ 
        $user_id = $this->getLoginUserData($id_only=true);
        if(!$user_id){
            return false;
        }
        $admin = FactoryLocator::get('Table')->get('Administrators')->exists(["user_id"=>$user_id]);
        if($admin){
            return true; 
        }
        return false;
    }

    


}