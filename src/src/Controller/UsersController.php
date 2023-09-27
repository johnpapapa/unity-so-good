<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\Core\Configure; 
use Cake\Http\Cookie\Cookie;
use Cake\Log\Log;
use DateTime;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @property \App\Controller\Component\loginComponent $Login
 */
class UsersController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['login', 'add', 'lineLogin']); //認証不要のアクション
    }

    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Login');
    }

    public function login()
    {
        //cookieが残っている場合cookieによるログインを行う
        $login_user_data = $this->Login->processCookieAutoLogin();
        if($login_user_data){ //ログイン済みの場合TOPに遷移
            $this->Flash->success("セッション情報が切れていた為再ログインしました");
            return $this->redirect(['controller'=>'Events', 'action'=>'index']);
        }
    }

    public function lineLogin(){
        $this->autoRender = false;

        //LINEからLINEユーザー情報の取得
        $line_user_data = $this->Login->getLineUserData();
        if(!$line_user_data){
            $this->Flash->error("LINEログインに失敗しました");
            return $this->redirect(['controller'=>'Events','action'=>'index']);
        }

        //LINEユーザ情報を使用したユーザー情報の取得
        $user_data = $this->Login->processLineLogin($line_user_data);
        if(!$user_data){
            $this->Flash->error("LINE情報を使用したユーザー情報の取得に失敗しました");
        }
        
        //ログイン情報の更新
        $this->Login->processSetLogin($user_data);
        $this->Flash->success(__('LINEログインに成功しました'));

        return $this->redirect(['controller'=>'Events','action'=>'index']);
    }

    public function logout()
    {
        $login_user_data = $this->Login->getLoginUserData();
        if ($login_user_data) {
            $this->Login->removeIdentity();
            $this->Login->removeCookieAutoLogin();
            $this->Login->removeDataAutoLogin($login_user_data);

        }
        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    public function detail(){
        $login_user_data = $this->Login->getLoginUserData();
        
        if(!$login_user_data){
            $this->log($login_user_data);
            $this->Flash->error(__('ユーザー情報の取得に失敗しました。'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $current_user = $this->Users->find("all", ['conditions'=>['id'=>$login_user_data['id']]])->first();
        $this->set(compact('current_user'));

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            $save_data = [];
            if(isset($data['display_name'])){ $save_data['display_name'] = $data['display_name']; }
            $user_data = $this->Users->patchEntity($current_user, $save_data);
            $result_user = $this->Users->save($user_data);
            if ($result_user) {
                $this->Flash->success(__('The user data has been saved.'));
                return $this->redirect(['controller'=>'events', 'action' => 'index']);
            }
            $this->Flash->error(__('The user data could not be saved. Please, try again.'));
        }
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
        if ($this->request->is(['post'])) {
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
