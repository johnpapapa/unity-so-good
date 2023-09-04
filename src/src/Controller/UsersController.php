<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\Core\Configure; 
use Cake\Http\Cookie\Cookie;
use DateTime;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['login', 'add', 'lineLogin','setIdentity','removeIdentity']); //認証不要のアクション
    }

    public function login()
    {
        $result = $this->Authentication->getResult();
        if ($result->isValid()) {
            //ログインに成功した場合クッキーをセット
            $user_data = $this->Authentication->getResult()->getData();
            $result = $this->setIdentity($user_data);
            $this->Flash->success('setIdentity_result:'.$result);
            // ログインしていればログイン後の画面にリダイレクト
            $target = $this->Authentication->getLoginRedirect() ?? '/events/index';
            return $this->redirect($target);
        } else {
            // ログインしていない場合cookieを確認
            $cookie = $this->request->getCookie(Configure::read('cookie.key'));
            if($cookie){
                // cookieが存在する場合にcookieのidを持つuserを検索
                $user_data = $this->Users->find('all', ['conditions'=>['remember_token' => $cookie]])->first();
                if($user_data){
                    //cookieのidを持つuserがいた場合ログイン
                    $this->setIdentity($user_data);
                }
                $target = $this->Authentication->getLoginRedirect() ?? '/events/index';
                return $this->redirect($target);
            }
        }


        // ログイン認証に失敗した場合はエラーを表示する
        if ($this->request->is('post')) {
            if(!$result->isValid()){
                $this->Flash->error(__('メールアドレスまたはパスワードが誤っています。'));
            } 
        } 
        
    }

    public function logout()
    {
        $result = $this->Authentication->getResult();
        if ($result->isValid()) {
            // $this->Authentication->logout();
            $this->removeIdentity();
        }
        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    public function detail(){
        if (!$this->Authentication->getResult()->isValid()){
            $this->Flash->error(__('Failed to get member information. Please login.'));
            return $this->redirect(['action' => 'index']);
        }

        $current_user = $this->Authentication->getResult()->getData();
        $this->set(compact('current_user'));

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            $save_data = [];
            
            if(isset($data['display_name'])){ $save_data['display_name'] = $data['display_name']; }
            if(isset($data['user_id'])){ $save_data['user_id'] = $data['user_id']; }
            
            if($data['password'] != ''){     
                $save_data['password'] = $data['password']; 
            } else {
                unset($save_data['password']);
            }
            $user_data = $this->Users->patchEntity($current_user, $save_data);
            
            $result_user = $this->Users->save($user_data);

            if ($result_user) {
                $this->Flash->success(__('The event has been saved.'));
                return $this->redirect(['controller'=>'events', 'action' => 'index']);
            }
            $this->Flash->error(__('The event could not be saved. Please, try again.'));
        }
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $users = $this->paginate($this->Users);

        $this->set(compact('users'));
    }

    public function lineLogin(){
        $this->autoRender = false;

        $postData = array(
            'grant_type'    => 'authorization_code',
            'code'          => $this->request->getQuery('code'),
            'redirect_uri'  => Configure::read('param_linelogin.redirect_uri')[$this->request->host()],
            'client_id'     => '2000439541',
            'client_secret' => 'b3b4212b5b7760b442883bb88b1f21f1'
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
        $line_user_id = $json2->userId;

        $user_data = $this->Users->find('all', ['conditions'=>['line_user_id' => $line_user_id]])->first();
        if(!$user_data){
            $save_data = $this->Users->newEntity([
                'display_name' => (isset($json2->displayName)) ? $json2->displayName : '',
                'line_user_id' => $line_user_id,
            ]);
            $user_data = $this->Users->save($save_data);
            
            if (!$user_data) {
                $this->Flash->error(__('The event could not be saved. Please, try again.'));
                return $this->redirect(['controller'=>'Events','action'=>'index']);
            }
            $this->Flash->success(__('The user has been saved.'));
        }
        $this->setIdentity($user_data);
        $this->Flash->success(__('Login successful'));
        return $this->redirect(['controller'=>'Events','action'=>'index']);
    }

    public function setIdentity($user_data){
        $this->autoRender = false;
        //手動でログインする場合にcookieを設定する
        $this->Authentication->setIdentity($user_data);
        $cookie = $this->request->getCookie(Configure::read('cookie.key'));
        if($cookie){ //既にcookieが設定されている場合はログインのみ
            return true;
        }
        $key_auto_login = hash('sha256', (uniqid() . mt_rand(1, 999999999) . '_auto_logins'));
        $this->response = $this->response->withCookie(Cookie::create(
            Configure::read('cookie.key'),
            $key_auto_login,
            ['expires'=>new DateTime(Configure::read('cookie.expired')), 'http'=>true]
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

        $this->response = $this->response->withCookie(Cookie::create(
            Configure::read('cookie.key'),
            '',
            ['expires'=>new DateTime('-1 day'), 'http'=>true]
        ));
        return true;
    }
    
    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('user'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'login']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
