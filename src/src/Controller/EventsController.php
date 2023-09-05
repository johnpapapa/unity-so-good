<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Utility\Hash;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\FrozenTime;

/**
 * Events Controller
 *
 * @property \App\Model\Table\EventsTable $Events
 * @method \App\Model\Entity\Event[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class EventsController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['index', 'list']); //認証不要のアクション
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */

    public function index()
    {
        $uid = null;
        if ($this->Authentication->getResult()->isValid()){
            $uid = $this->Authentication->getResult()->getData()['id'];
        }
        
        $this->Locations = $this->fetchTable('Locations');
        $conditions = [
            'Events.deleted_at IS' => NULL, //削除前のイベント
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
        ->limit(Configure::read('event_item_limit')); 
        $events = $events_query->all()->toArray();
        
        $events_formated = []; //整形後のevent
        $now_datetime = new FrozenTime('+9 hour');
        foreach($events as $event){
            //時刻の比較
            if($now_datetime < $event->start_time){
                $event['event_state'] = 0;
            } elseif($now_datetime > $event->end_time) {
                $event['event_state'] = 2;
            } else {
                $event['event_state'] = 1;
            }

            //ユーザの参加情報取出
            if ($uid){
                $resp = array_search($uid, array_column($event->event_responses, 'responder_id'));
                $event['user_response_state'] = ($resp !== false) ? $event->event_responses[$resp]->response_state: null;
            }

            //参加情報取出
            $event_responder_list = [0=>[], 1=>[], 2=>[]];
            foreach($event->event_responses as $event_response){
                $event_responder_list[$event_response->response_state][] = $event_response->user->display_name;
            }
            $event['event_responses'] = $event_responder_list;

            $events_formated[] = $event;
        }
        $events = $events_formated;
        
        $this->set(compact('events'));
    }

    public function unresponded(){ //未表明
        if (!$this->Authentication->getResult()->isValid()){
            $this->Flash->error(__('Failed to get member information. Please login.'));
            return $this->redirect(['action' => 'index']);
        }
        $uid = $this->Authentication->getResult()->getData()['id'];

        //未削除かつ開催時間が未来のEventに対して,userのEventResponseをLEFTJOINした結果NULLのものを返す
        //1.未削除かつ開催時間が未来のEventを取得 => e
        //2.responder_idが自分のEventResponseを取得 => er
        //3.Event.idとEventResponse.event_idをキーにしてLEFTJOIN
        //4.responder_idがNULLとなるEvent.idを取得
        $sql = <<<EOF
        SELECT e.id, e.start_time
        FROM ( 
            SELECT events.id, events.start_time, events.deleted_at
            FROM events 
            WHERE ISNULL(events.deleted_at) AND events.start_time between cast(NOW()  + interval 9 hour as datetime) and cast( NOW()+ interval 1 year as datetime) 
        ) AS e 
        LEFT JOIN ( 
            SELECT event_responses.responder_id, event_responses.event_id 
            FROM event_responses 
            WHERE event_responses.responder_id = {$uid} 
        ) AS er 
        ON (er.event_id = e.id ) 
        WHERE ISNULL(er.responder_id)
        ORDER BY e.start_time ASC
        EOF;
        $connection = ConnectionManager::get('default');
        $event_responses = $connection->execute($sql)->fetchAll('assoc');
        $event_ids = Hash::extract($event_responses, '{n}.id');

        $events = [];
        if(count($event_ids) > 0){  

            $this->Locations = $this->fetchTable('Locations');
            $conditions = [
                'Events.id IN' => $event_ids,
            ]; 
            $events_query = $this->Events->find("all", ['conditions'=>$conditions]);
            $events_query = $events_query
            ->contain([
                'Locations',
                'EventResponses' => [
                    'sort' => [
                        'response_state' => 'DESC',
                        'EventResponses.updated_at' => 'ASC'
                    ]
                ]
            ])
            ->select($this->Events)
            ->select($this->Locations)
            ->contain('EventResponses.Users') //EventResponses以下Usersオブジェクト作成
            ->order(['Events.start_time'=>'ASC'])
            ->limit(Configure::read('event_item_limit'));
            $events = $events_query->all()->toArray();

            $events_formated = []; //整形前のevent
            $now_datetime = new FrozenTime('+9 hour');
            foreach($events as $event){
                //時刻の比較
                if($now_datetime < $event->start_time){
                    $event['event_state'] = 0;
                } elseif($now_datetime > $event->end_time) {
                    $event['event_state'] = 2;
                } else {
                    $event['event_state'] = 1;
                }
                
                //ユーザの参加情報取出
                if ($uid){
                    $resp = array_search($uid, array_column($event->event_responses, 'responder_id'));
                    $event['user_response_state'] = ($resp !== false) ? $event->event_responses[$resp]->response_state: null;
                }

                //参加情報取出
                $event_responder_list = [0=>[], 1=>[], 2=>[]];
                foreach($event->event_responses as $event_response){
                    $event_responder_list[$event_response->response_state][] = $event_response->user->display_name;
                }
                $event['event_responses'] = $event_responder_list;
                
                $events_formated[] = $event;
            };
            $events = $events_formated;
        }

        $this->set(compact('events'));
    }

    public function participate(){ //表明済み
        if ($this->Authentication->getResult()->isValid()){
            $uid = $this->Authentication->getResult()->getData()['id'];
        } else {
            $this->Flash->error(__('Failed to get member information. Please login.'));
            return $this->redirect(['action' => 'index']);
        }


        $sql = <<<EOF
        SELECT e.id 
        FROM ( 
            SELECT events.id, events.start_time, events.deleted_at 
            FROM events 
            WHERE ISNULL(events.deleted_at) AND events.start_time between cast(NOW() + interval 9 hour as datetime) and cast( NOW() + interval 1 year as datetime) 
        ) AS e 
        LEFT JOIN ( 
            SELECT event_responses.responder_id, event_responses.event_id 
            FROM event_responses
            WHERE event_responses.responder_id = {$uid}
        ) AS er 
        ON (er.event_id = e.id ) 
        WHERE ISNULL(er.responder_id) = 0 
        ORDER BY e.start_time ASC;
        EOF;
        $connection = ConnectionManager::get('default');
        $event_responses = $connection->execute($sql)->fetchAll('assoc');
        $event_ids = Hash::extract($event_responses, '{n}.id');

        $events = [];

        if(count($event_ids) > 0){   
            $this->Locations = $this->fetchTable('Locations');
            $conditions = [
                'Events.id IN' => $event_ids
            ]; 
            $events_query = $this->Events->find("all", ['conditions'=>$conditions]);
            $events_query = $events_query
            ->contain([
                'Locations',
                'EventResponses' => [
                    'sort' => [
                        'response_state' => 'DESC', 
                        'EventResponses.updated_at' => 'ASC'
                    ]
                ]
            ])
            ->select($this->Events)
            ->select($this->Locations)
            ->contain('EventResponses.Users') //EventResponses以下Usersオブジェクト作成
            ->order(['Events.start_time'=>'ASC'])
            ->limit(Configure::read('event_item_limit')); 
            $events = $events_query->all()->toArray();

            $events_formated = [];
            $now_datetime = new FrozenTime('+9 hour');
            foreach($events as $event){
                //時刻の比較
                if($now_datetime < $event->start_time){
                    $event['event_state'] = 0;
                } elseif($now_datetime > $event->end_time) {
                    $event['event_state'] = 2;
                } else {
                    $event['event_state'] = 1;
                }

                //ユーザの参加情報取出
                if ($uid){
                    $resp = array_search($uid, array_column($event->event_responses, 'responder_id'));
                    $event['user_response_state'] = ($resp !== false) ? $event->event_responses[$resp]->response_state: null;
                    
                }

                //参加情報取出
                $event_responder_list = [0=>[], 1=>[], 2=>[]];
                foreach($event->event_responses as $event_response){
                    $event_responder_list[$event_response->response_state][] = $event_response->user->display_name;
                }
                $event['event_responses'] = $event_responder_list;

                $events_formated[] = $event;
            };
            $events = $events_formated;
        }
        
        $this->set(compact('events'));
    }

    public function created(){
        $uid = null;
        if ($this->Authentication->getResult()->isValid()){
            $uid = $this->Authentication->getResult()->getData()['id'];
        }

        $this->Locations = $this->fetchTable('Locations');
        $conditions = [ //削除済みのイベントも含ませる
            'Events.organizer_id IN' => $uid
        ]; 
        $events_query = $this->Events->find("all", ['conditions'=>$conditions]);
        $events_query = $events_query
        ->contain([
            'Locations',
            'EventResponses' => [
                'sort' => [
                    'response_state' => 'DESC',
                    'EventResponses.updated_at' => 'ASC'
                ]
            ]
        ])
        ->select($this->Events)
        ->select($this->Locations)
        ->contain('EventResponses.Users') //EventResponses以下Usersオブジェクト作成
        ->order(['Events.start_time'=>'DESC'])
        ->limit(Configure::read('event_item_limit')); 
        $events = $events_query->all()->toArray();
        
        $events_formated = []; //整形後のevent
        $now_datetime = new FrozenTime('+9 hour');
        foreach($events as $event){
            //時刻の比較
            if($now_datetime < $event->start_time){
                $event['event_state'] = 0;
            } elseif($now_datetime > $event->end_time) {
                $event['event_state'] = 2;
            } else {
                $event['event_state'] = 1;
            }

            //ユーザの参加情報取出
            if ($uid){
                $resp = array_search($uid, array_column($event->event_responses, 'responder_id'));
                $event['user_response_state'] = ($resp !== false) ? $event->event_responses[$resp]->response_state: null;
            }

            //参加情報取出
            $event_responder_list = [0=>[], 1=>[], 2=>[]];
            foreach($event->event_responses as $event_response){
                $event_responder_list[$event_response->response_state][] = $event_response->user->display_name;
            }
            $event['event_responses'] = $event_responder_list;

            $events_formated[] = $event;
        }
        $events = $events_formated;

        $this->set(compact('events'));
    }

    public function detail($id = null)
    {
        $uid = null;
        if ($this->Authentication->getResult()->isValid()){
            $uid = $this->Authentication->getResult()->getData()['id'];
        }

        if(!$id){
            $this->Flash->success(__('The event has not exist.'));
            return $this->redirect(['action' => 'index']);
        }
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
                    
        //時刻の比較
        $now_datetime = strtotime("now");
        $start_datetime = strtotime($event->start_time->i18nFormat('yyyy-MM-dd HH:mm:ss'));
        $end_datetime = strtotime($event->end_time->i18nFormat('yyyy-MM-dd HH:mm:ss'));
        if($now_datetime < $start_datetime){
            $event['event_state'] = 0;
        } elseif($now_datetime > $end_datetime) {
            $event['event_state'] = 2;
        } else {
            $event['event_state'] = 1;
        }

         //ユーザの参加情報取出
         if ($uid){
            $user_event_responses = Hash::extract($event, 'event_responses.{n}[responder_id='.$uid.']');
            if(count($user_event_responses) <= 0){
                $event['user_response_state'] = null;
            } else {
                $event['user_response_state'] = $user_event_responses[0]['response_state'];
            }
        }

        if ($uid){
            $resp = array_search($uid, array_column($event->event_responses, 'responder_id'));
            $event['user_response_state'] = ($resp !== false) ? $event->event_responses[$resp]->response_state: null;
        }

        //参加情報取出
        $event_responder_list = [0=>[], 1=>[], 2=>[]];
        foreach($event->event_responses as $event_response){
            $event_responder_list[$event_response->response_state][] = ["name"=>$event_response->user->display_name, "time"=>$event_response->updated_at];
        }
        $event['event_responses'] = $event_responder_list;

        $event_prev = $this->Events->find("all", [
            "conditions" => ["Events.start_time <" => $event->start_time]
        ])->select('id')->order(['Events.start_time'=>'DESC'])->limit(1)->first();
        $event_next = $this->Events->find("all", [
            "conditions" => ["Events.start_time >" => $event->start_time]
        ])->select('id')->order(['Events.start_time'=>'ASC'])->limit(1)->first();

        $event_prev_id = (isset($event_prev->id)) ? $event_prev->id : null;
        $event_next_id = (isset($event_next->id)) ? $event_next->id : null;

        $this->set(compact('event', 'event_prev_id', 'event_next_id'));
    }

    public function ajaxChangeResponseState(){
        $this->autoRender = false;
        $data = $this->request->getData();

        $this->EventResponses = $this->fetchTable('EventResponses');
        $event_response = $this->EventResponses->find('all', ['conditions'=>['responder_id'=>$data['user_id'],'event_id'=>$data['event_id']]])->first();
        if(!$event_response){
            $event_response = $this->EventResponses->newEntity(['responder_id'=>$data['user_id'],'event_id'=>$data['event_id']]);
        }
        $event_response = $this->EventResponses->patchEntity($event_response, ['response_state' => $data['response_state']]);
        if ($this->EventResponses->save($event_response)) {
            $response = ['status'=>'ok'];
        } else {
            $response = ['status'=>'bad'];
        }

        $this->RequestHandler->respondAs('application/json; charset=UTF-8');
        return $this->response->withStringBody(json_encode($response));
    }


    /**
     * View method
     *
     * @param string|null $id Event id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $event = $this->Events->get($id, [
            'contain' => ['Users', 'Locations', 'EventResponses'],
        ]);

        $this->set(compact('event'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        if ($this->Authentication->getResult()->isValid()){
            $uid = $this->Authentication->getResult()->getData()['id'];
        } else {
            $this->Flash->error(__('Failed to get member information. Please login.'));
            return $this->redirect(['action' => 'index']);
        }
        $this->Locations = $this->fetchTable('Locations');
        $event = $this->Events->newEmptyEntity();
        $locations = $this->Locations->find('all', ["conditions"=>[]])->all()->toArray();
        // $locations = Hash::combine($locations, '{n}.display_name', '{n}');
        $this->set(compact('event', 'locations'));

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            //新規コートのチェックがついている場合のLocation追加処理
            if(isset($data['location_new_check'])){
                $location_data = $this->Locations->newEntity([
                    "display_name"=>h($data['display_name']),
                    "address"=>h($data['address']),
                    "usage_price"=>$data['usage_price'],
                    "night_price"=>$data['night_price'],
                ]);
                $result_location = $this->Locations->save($location_data);
                if(!$result_location){
                    $this->Flash->error(__('The location could not be saved. Please, try again.'));
                    return;
                }
                $data["location_id"] = $result_location->id;
            }

            $data['event_date'] = str_replace('/','-',$data['event_date']); //日付のフォーマット
            $data['start_time'] = wordwrap($data['start_time'], 2, ':', true); //時刻のフォーマット
            $data['end_time'] = wordwrap($data['end_time'], 2, ':', true); //時刻のフォーマット

            $event_data = $this->Events->newEntity([
                "organizer_id" => $uid,
                "start_time"=>$data['event_date'] . ' ' . $data['start_time'] . ':00',
                "end_time"=>$data['event_date'] . ' ' . $data['end_time'] . ':00',
                "area"=>h($data['area']),
                "participants_limit"=>$data['participants_limit'],
                "comment"=>h($data['comment']),
                "location_id"=>$data['location_id'],
            ]);

            $result_event = $this->Events->save($event_data);
            if ($result_event) {
                $this->Flash->success(__('The event has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The event could not be saved. Please, try again.'));
        }
    }

    /**
     * Edit method
     *
     * @param string|null $id Event id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $event = $this->Events->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $event = $this->Events->patchEntity($event, $this->request->getData());
            if ($this->Events->save($event)) {
                $this->Flash->success(__('The event has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The event could not be saved. Please, try again.'));
        }
        $users = $this->Events->Users->find('list', ['limit' => 200])->all();
        $locations = $this->Events->Locations->find('list', ['limit' => 200])->all();
        $this->set(compact('event', 'users', 'locations'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Event id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $event = $this->Events->get($id);
        if ($this->Events->delete($event)) {
            $this->Flash->success(__('The event has been deleted.'));
        } else {
            $this->Flash->error(__('The event could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
