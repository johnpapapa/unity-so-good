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
        $this->Authentication->addUnauthenticatedActions(['login', 'add', 'lineLogin']); //認証不要のアクション
    }

    public function login()
    {
        $login_user_data = $this->getLoginUserData();
        if ($login_user_data) {
            //ログインに成功した場合クッキーをセット
            $key_auto_login = $this->genKeyAutoLogin();
            $this->setCookieAutoLogin($key_auto_login);
            $this->setDataAutoLogin($key_auto_login, $login_user_data);

            // ログイン後の画面にリダイレクト
            $target = $this->Authentication->getLoginRedirect() ?? '/events/index';
            return $this->redirect($target);

        } else {
            // ログインしていない場合cookieを確認
            $cookie = $this->getCookieAutoLogin();
            if($cookie){
                // cookieが存在する場合にcookieのidを持つuserを検索
                $user_data = $this->Users->find('all', ['conditions'=>['remember_token' => $cookie]])->first();
                if($user_data){
                    //cookieのidを持つuserがいた場合再ログイン
                    $this->setIdentity($user_data);
                    $this->Flash->success('セッションが切れていた為、再ログインしました.');
                    $target = $this->Authentication->getLoginRedirect() ?? '/events/index';
                    return $this->redirect($target);
                } else {
                    //cookieのidを持つuserがいない場合cookieを削除
                    $this->removeCookieAutoLogin();
                }
            }
        }

        // ログイン認証に失敗した場合はエラーを表示する
        if ($this->request->is('post')) {
            $login_user_data = $this->getLoginUserData();
            if(!$login_user_data){
                $this->Flash->error(__('メールアドレスまたはパスワードが誤っています。'));
            } 
        } 
        
    }

    public function logout()
    {
        $login_user_data = $this->getLoginUserData();
        if ($login_user_data) {
            $this->removeIdentity();
            $this->removeCookieAutoLogin();
            $this->removeDataAutoLogin($login_user_data);

        }
        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    public function detail(){
        $login_user_data = $this->getLoginUserData();
        if(!$login_user_data){
            $this->Flash->error(__('ユーザー情報の取得に失敗しました。'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $current_user = $this->Users->find("all", ['conditions'=>['id'=>$login_user_data['id']]])->first();
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
                $this->Flash->success(__('The user data has been saved.'));
                return $this->redirect(['controller'=>'events', 'action' => 'index']);
            }
            $this->Flash->error(__('The user data could not be saved. Please, try again.'));
        }
    }

    public function lineLogin(){
        $this->autoRender = false;

        $line_user_data = $this->getLineUserData();
        $user_data = $this->Users->find('all', ['conditions'=>['line_user_id' => $line_user_data['line_user_id']]])->first();
        if(!$user_data){ //該当するline_user_idが存在しない場合新規ユーザー作成
            $save_data = $this->Users->newEntity([
                'display_name' => $line_user_data['display_name'],   
                'line_user_id' => $line_user_data['line_user_id'],
            ]);
            $user_data = $this->Users->save($save_data);
            if (!$user_data) {
                $this->Flash->error(__('The event could not be saved. Please, try again.'));
                return $this->redirect(['controller'=>'Events','action'=>'index']);
            }
            $this->Flash->success(__('The user has been saved.'));
        }

        $key_auto_login = $this->genKeyAutoLogin();
        $this->setCookieAutoLogin($key_auto_login);
        $this->setDataAutoLogin($key_auto_login, $user_data);
        $this->setIdentity($user_data);

        $this->Flash->success(__('Login successful'));

        return $this->redirect(['controller'=>'Events','action'=>'index']);
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
}
