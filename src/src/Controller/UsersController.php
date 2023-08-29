<?php
declare(strict_types=1);

namespace App\Controller;

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
        $this->Authentication->addUnauthenticatedActions(['login', 'add']); //認証不要のアクション
    }

    public function login()
    {
        $result = $this->Authentication->getResult();
        // debug($result);
        if ($result->isValid()) {
            // ログインしていればログイン後の画面にリダイレクト
            $target = $this->Authentication->getLoginRedirect() ?? '/events/index';
            return $this->redirect($target);
        }
        // ログイン認証に失敗した場合はエラーを表示する
        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->error(__('メールアドレスまたはパスワードが誤っています。'));
        }
    }

    public function logout()
    {
        $result = $this->Authentication->getResult();
        if ($result->isValid()) {
            $this->Authentication->logout();
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
            if(isset($data['username'])){ $save_data['username'] = $data['username']; }
            if(isset($data['password'])){ $save_data['password'] = $data['password']; }

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
