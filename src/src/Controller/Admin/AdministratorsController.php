<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use Cake\Utility\Hash;

/**
 * Administrators Controller
 *
 * @property \App\Model\Table\AdministratorsTable $Administrators
 * @method \App\Model\Entity\Administrator[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AdministratorsController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        if(!$this->isAdministrator()){
            $this->Flash->error(__('管理者権限がありません。'));
            return $this->redirect(['prefix'=>false,'controller' => 'Events', 'action' => 'index']);
        }
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
    }

    public function userList(){
        $this->Users = $this->fetchTable('Users');
        $user_data = $this->Users->find("all")->toArray();
        $this->set(compact('user_data'));
            
    }
    public function eventList(){
        $this->loadComponent('Event');

        $this->Events = $this->fetchTable('Events');
        $this->Locations = $this->fetchTable('Locations');
        $conditions = [
            'Events.deleted_at IS' => 0, //削除前のイベント
            'Events.end_time >=' => date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . "-14days")) //14日前までのイベント
        ]; 
        $events_query = $this->Events->find("all", ['conditions'=>$conditions]);
        $events_query = $events_query
        ->contain([
            'Locations',
            'EventResponses' => [
                'sort' => [
                    'response_state' => 'DESC', //反応した種類順
                    'EventResponses.updated_at' => 'ASC' //反応した時間順
                ]
            ]
        ])
        ->select($this->Events)
        ->select($this->Locations)
        ->contain('EventResponses.Users') //EventResponsesに紐づくUsersオブジェクト作成
        ->order(['Events.start_time'=>'ASC']) //Eventが表示される順番
        ->limit(50); 
        $events = $events_query->all()->toArray();
        
        $events = $this->Event->getFormatEventDataList($events);
        
        $this->set(compact('events'));

    }

    public function userDetail($id=null){
        if(!$id){
            $this->Flash->error(__('ユーザー情報の取得に失敗しました'));
            return $this->redirect($this->request->referer());
        }

        $this->Users = $this->fetchTable('Users');
        $user_data = $this->Users->find("all", ["conditions"=>["id"=>$id]])->first();
        if(!$user_data){
            $this->Flash->error(__('存在しないユーザーを指定しました'));
            return $this->redirect($this->request->referer());
        }

        $this->loadComponent('Event');

        $this->Events = $this->fetchTable('Events');
        $this->Locations = $this->fetchTable('Locations');

        // 参加履歴
        $event_response_history_list = $this->Event->getAllEventResponseListByUserId($id);
        
        // 反応回数
        $event_response_history_count_list = array_count_values(array_map('strval', Hash::extract($event_response_history_list, '{n}.response_state')));
        if(isset($event_response_history_count_list[''])){
            $event_response_history_count_list['null'] = $event_response_history_count_list['']; //空文字はnull
            unset($event_response_history_count_list['']); //空文字があるのは嫌なので削除
        }

        // 直近10イベントの反応履歴
        $event_response_history_limit_list = $this->Event->getAllEventResponseListByUserId($id, 10);

        // コート別参加率
        // 曜日別参加率
        // 時間別参加率


        $this->set(compact(
            'user_data', 
            'event_response_history_list', 
            'event_response_history_count_list',
            'event_response_history_limit_list' 
        ));

    }

    public function eventDetail($id=null){
        if(!$id){
            $this->Flash->error(__('イベント情報の取得に失敗しました'));
            return $this->redirect($this->request->referer());
        }

        $this->loadComponent('Event');

        $this->Events = $this->fetchTable('Events');
        $this->Locations = $this->fetchTable('Locations');
        $event = $this->Events->find("all", [
            "conditions" => ["Events.id" => $id]
        ])
        ->contain([
            'Users', 
            'Locations',
            'EventResponses' => ['sort' => ['response_state' => 'DESC', 'EventResponses.updated_at' => 'ASC']]
        ])
        ->contain('EventResponses.Users')
        ->first();
                    
        $event = $this->Event->getFormatEventData($event);
        $event_response_list = $this->Event->getEventResponseListByEventId($id);
        $categorized_event_response_list = $this->Event->categorizedEventResponseList($event_response_list);

        $this->set(compact('event', 'categorized_event_response_list'));

    }
}
