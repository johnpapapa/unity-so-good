<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Informations Controller
 *
 * @method \App\Model\Entity\Information[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 * @property \App\Model\Table\InformationsTable $Informations
 */
class InformationsController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['about']); //認証不要のアクション
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function about(){
        $information_data = $this->Informations->find("all")->first();
        if(!$information_data){
            $information_data = $this->Informations->newEmptyEntity();
        }
        $information_data["about"] = $information_data["about"];
        $information_data["rule"] = $information_data["rule"];
        $this->set(compact('information_data'));
    }

    public function edit(){
        $login_user_data = $this->Login->getLoginUserData();
        
        if(!$login_user_data){
            $this->log($login_user_data);
            $this->Flash->error(__('ユーザー情報の取得に失敗しました。'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        if(!$this->Login->isAdministrator()){
            $this->Flash->error(__('編集する権限がありません'));
            return $this->redirect(['controller' => 'Informations', 'action' => 'about']);
        }

        $information_data = $this->Informations->find("all")->first();
        if(!$information_data){
            $information_data = $this->Informations->newEmptyEntity();
        }

        $this->set(compact('information_data'));

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            $save_data = [];
            if(isset($data['about'])){ $save_data['about'] = $data['about']; }
            if(isset($data['rule'])){ $save_data['rule'] = $data['rule']; }
            
            $information_data = $this->Informations->patchEntity($information_data, $save_data);
            $result_information = $this->Informations->save($information_data);
            
            if ($result_information) {
                $this->Flash->success(__('The infomation data has been saved.'));
                return $this->redirect(['controller'=>'informations', 'action' => 'about']);
            }
            $this->Flash->error(__('The information data could not be saved. Please, try again.'));
            return ;
        }
    }
}
