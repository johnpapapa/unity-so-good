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
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
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

    // public function login(){
    //     $login_user_data = $this->Login->processLogin();
    // }
    public function login()
    {
        $login_user_data = $this->getLoginUserData();
        if($login_user_data){ //ログイン済みの場合cookieを更新しない
            $this->Login->processSetLogin($login_user_data, false);
            return $this->redirect(['controller'=>'Events', 'action'=>'index']);
        }
    }

    public function logout()
    {
        $login_user_data = $this->getLoginUserData();
        if ($login_user_data) {
            $this->Login->removeIdentity();
            $this->Login->removeCookieAutoLogin();
            $this->Login->removeDataAutoLogin($login_user_data);

        }
        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    public function detail(){
        $login_user_data = $this->getLoginUserData();
        // Log::write('debug', print_r($login_user_data, true));
        
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

        $key_auto_login = $this->Login->genKeyAutoLogin();
        $this->Login->setCookieAutoLogin($key_auto_login);
        $this->Login->setDataAutoLogin($user_data, $key_auto_login);
        $this->Login->setIdentity($user_data);

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
