<?php
 
namespace App\Controller\Component;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Datasource\FactoryLocator;
use Cake\Core\Configure; 
use Cake\Http\Cookie\Cookie;
use DateTime;
 
class loginComponent extends Component
{
    protected $components = ['RequestHandler', 'Authentication.Authentication'];

    public function initialize(array $config): void
    {
        $this->Locations = FactoryLocator::get('Table')->get('Locations');
        $this->Events = FactoryLocator::get('Table')->get('Events');
        $this->Users = FactoryLocator::get('Table')->get('Users');
        $this->EventResponses = FactoryLocator::get('Table')->get('EventResponses');
        
        // $this->loadComponent('RequestHandler');
        // $this->loadComponent('Authentication.Authentication'); //認証ロジック
    }

    public function hello(){
        return 'test';
    }

    public function setIdentity($user_data){
        
        // $this->autoRender = false;
        //手動でログインする場合にcookieを設定する
        $this->Authentication->setIdentity($user_data);
        $this->log('setIdentity');
        $cookie = $this->request->getCookie(Configure::read('cookie.key'));
        if($cookie){
            $this->log(print_r($cookie));
        } else {
            $this->log('null~~');
        }
        
        if($cookie){ //既にcookieが設定されている場合はログインのみ
            return true;
        }
        $key_auto_login = hash('sha256', (uniqid() . mt_rand(1, 999999999) . '_auto_logins'));
        $this->request = $this->request->withCookie(Cookie::create(
            Configure::read('cookie.key'),
            $key_auto_login,
            ['expires'=>new DateTime('+ 1 min'), 'http'=>true]
        ));
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

    public function removeIdentity(){
        $this->autoRender = false;
        if ($this->Authentication->getResult()->isValid()){
            $uid = $this->Authentication->getResult()->getData()['id'];
            $user_data = $this->Users->find('all', ['conditions'=>['id' => $uid]])->first();
            $update_data = $this->Users->patchEntity($user_data, [
                'id' => $user_data['id'],
                'remember_token' => null
            ]);
            $result = $this->Users->save($update_data);
            if(!$result){
                $this->Flash->error('cannot remove remember_token. please contact server administrator');
                return false;
            }
            
            $this->Authentication->logout();
        }
        $this->request = $this->request->withCookie(Cookie::create(
            Configure::read('cookie.key'),
            '',
            ['expires'=>new DateTime('-1 day'), 'http'=>true]
        ));
        return true;
    }

}