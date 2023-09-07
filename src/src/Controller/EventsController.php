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

    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Event');
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */

    public function index()
    {
        $uid = $this->getLoginUserData(true);   
        
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
        ->limit(Configure::read('event_item_limit')); 
        $events = $events_query->all()->toArray();
        
        $events = $this->Event->getFormatEventDataList($events, $uid);
        
        $this->set(compact('events'));
    }

    public function unresponded(){ //未表明
        $uid = $this->getLoginUserData(true);
        if(!$uid){
            $this->Flash->error(__('ユーザー情報の取得に失敗しました。'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

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
            WHERE events.deleted_at=0 AND events.start_time between cast(NOW()  + interval 9 hour as datetime) and cast( NOW()+ interval 1 year as datetime) 
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
            $events = $this->Event->getFormatEventDataList($events, $uid);
        }

        $this->set(compact('events'));
    }

    public function participate(){ //表明済み
        $uid = $this->getLoginUserData(true);
        if(!$uid){
            $this->Flash->error(__('ユーザー情報の取得に失敗しました。'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }


        // $sql = <<<EOF
        // SELECT e.id 
        // FROM ( 
        //     SELECT events.id, events.start_time, events.deleted_at 
        //     FROM events 
        //     WHERE events.deleted_at=0 AND events.start_time between cast(NOW() + interval 9 hour as datetime) and cast( NOW() + interval 1 year as datetime) 
        // ) AS e 
        // LEFT JOIN ( 
        //     SELECT event_responses.responder_id, event_responses.event_id 
        //     FROM event_responses
        //     WHERE event_responses.responder_id = {$uid}
        // ) AS er 
        // ON (er.event_id = e.id ) 
        // WHERE ISNULL(er.responder_id)
        // ORDER BY e.start_time ASC;
        // EOF;
        $sql = <<<EOF
        SELECT e.id
        FROM ( 
            SELECT events.id, events.start_time, events.deleted_at 
            FROM events 
            WHERE events.deleted_at=0 AND events.start_time between cast(NOW() + interval 9 hour as datetime) and cast( NOW() + interval 1 year as datetime) 
        ) AS e 
         JOIN ( 
            SELECT event_responses.responder_id, event_responses.event_id 
            FROM event_responses
            WHERE event_responses.responder_id = {$uid}
        ) AS er 
        ON (er.event_id = e.id ) 
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
            $events = $this->Event->getFormatEventDataList($events, $uid);
        }
        
        $this->set(compact('events'));
    }

    public function created(){
        $uid = $this->getLoginUserData(true);
        if(!$uid){
            $this->Flash->error(__('ユーザー情報の取得に失敗しました。'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
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
        
        $events = $this->Event->getFormatEventDataList($events, $uid);
        $this->set(compact('events'));
    }

    public function detail($id = null)
    {
        $uid = $this->getLoginUserData(true);
        if(!$uid){
            $this->Flash->error(__('ユーザー情報の取得に失敗しました。'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
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
        if($event->deleted_at && $event->organizer_id != $uid){ //削除されていた時
            $this->Flash->error(__('このイベントはすでに削除されています'));
            return $this->redirect(['controller'=>'Events', 'action'=>'index']);
        }
                    
        $event = $this->Event->getFormatEventData($event, $uid);

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

    public function ajaxDeleteEvent(){

        $this->autoRender = false;
        $response = ['status'=>''];
        $uid = $this->getLoginUserData(true);
        if(!$uid){
            $this->Flash->error(__('ユーザー情報の取得に失敗しました'));
        }

        $data = $this->request->getData();

        $event_ids = $data['event_ids'];
        foreach($event_ids as $id){
            $event_data = $this->Events->find("all", ['conditions'=>['id'=>$id]])->first();
            if(!$event_data){
                $response['content'][] = ['id'=>$id, 'status'=>'存在しないイベントID'];
                continue;
            }
            if ($event_data->organizer_id != $uid){
                $this->Flash->error(__('イベント削除の権限がありません'));
                $response['content'][] = ['id'=>$id, 'status'=>'イベント削除権限なし'];
                continue;
            }
            $event_data = $this->Events->patchEntity($event_data, ['deleted_at'=>1]);
            $result = $this->Events->save($event_data);
            if(!$result){
                $response['content'][] = ['id'=>$id, 'status'=>'イベント情報更新失敗'];
            } else {
                $response['content'][] = ['id'=>$id, 'status'=>'イベント情報更新成功'];
            }
            
        }

        $this->RequestHandler->respondAs('application/json; charset=UTF-8');
        return $this->response->withStringBody(json_encode($response));
    }

    public function ajaxShareEvent(){

        $this->autoRender = false;
        $response = ['status'=>''];
        // $uid = $this->getLoginUserData(true);
        // if(!$uid){
        //     $this->Flash->error(__('ユーザー情報の取得に失敗しました'));
        // }

        $data = $this->request->getData();

        $event_ids = $data['event_ids'];
        $share_sentence = "お疲れ様です！\nテニス日程を記載させて頂きます！\n\n"; //共有する文言
        $this->Locations = $this->fetchTable('Locations');
        foreach($event_ids as $id){
            $event_data = $this->Events->find("all", [
                'conditions'=>['Events.id'=>$id]
            ])->contain(['Locations'])->select($this->Locations)->select($this->Events)->first();
            if(!$event_data){
                $response['error'][] = ['id'=>$id, 'status'=>'存在しないイベントID'];
                continue;
            } 
            $event_date = $event_data->start_time->i18nFormat('MM月dd日');
            $day_of_weeks = Configure::read('day_of_weeks');
            $event_date_dow = $day_of_weeks[$event_data->start_time->dayOfWeek];//曜日
            $location = $event_data->location->display_name;
            $start_time = $event_data->start_time->i18nFormat('HH:mm');
            $end_time = $event_data->end_time->i18nFormat('HH:mm');
            $area = ($event_data->area != '')? $event_data->area.'コート' : '';
            $participants_limit = ($event_data->participants_limit > 0)? '定員'.$event_data->participants_limit.'名' : '';

            $share_sentence = $share_sentence . "{$event_date}（{$event_date_dow}）\n";
            $share_sentence = $share_sentence . "{$location}\n";
            $share_sentence = $share_sentence . "{$start_time}〜{$end_time}\n";
            $share_sentence = $share_sentence . "{$area}\n";
            $share_sentence = $share_sentence . "🍅{$participants_limit}\n\n";
            
            // $share_sentence = $share_sentence . <<<EOF
            //     {$event_date}({$event_date_dow})
            //     {$location}
            //     {$start_time}〜{$end_time}
            //     {$area}
            //     {$participants_limit}
            // EOF;

            // if(1){
            //     $response['content'][] = ['id'=>$id, 'status'=>'イベント情報更新失敗'];
            // } else {
            //     $response['content'][] = ['id'=>$id, 'status'=>'イベント情報更新成功'];
            // }            
        }

        $response['content'] = $share_sentence;

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
        $uid = $this->getLoginUserData(true);
        if(!$uid){
            $this->Flash->error(__('ユーザー情報の取得に失敗しました。'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $this->Locations = $this->fetchTable('Locations');
        $event = $this->Events->newEmptyEntity();
        $locations = $this->Locations->find('all')->all()->toArray();
        $locations = Hash::combine($locations, '{n}.display_name', '{n}');
        $this->set(compact('event', 'locations'));

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            //候補から選択しなかった場合のLocation追加処理
            if($data['location_id'] == ''){
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
                $this->Flash->success(__('The location has been saved.'));
                $data["location_id"] = $result_location->id;
            } else {
                $location_data = $this->Locations->newEmptyEntity();
            }
            
            $location_data = $this->Locations->patchEntity($location_data,[
                "id" => $data["location_id"],
                "display_name"=>h($data['display_name']),
                "address"=>h($data['address']),
                "usage_price"=>$data['usage_price'],
                "night_price"=>$data['night_price'],
            ], ['accessibleFields' => ['id' => true]]);
            $result_location = $this->Locations->save($location_data);
            if (!$result_location) {
                $this->Flash->error(__('The location could not be saved. Please, try again.'));
                return $this->redirect(['controller'=>'Events','action' => 'created']);
            }

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
            if (!$result_event) {
                $this->Flash->error(__('The event could not be saved. Please, try again.'));
                return $this->redirect(['controller'=>'Events','action' => 'created']);
            }
            $this->Flash->success(__('The event has been saved.'));
            return $this->redirect(['controller'=>'Events','action' => 'created']);
        }
    }

    /**
     * Edit method
     *
     * @param string|null $id Event id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null){
        $uid = $this->getLoginUserData(true);
        if(!$uid){
            $this->Flash->error(__('ユーザー情報の取得に失敗しました'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
        if(!$id){
            $this->Flash->error(__('イベントIDが存在していません'));
            return $this->redirect(['controller'=>'Events', 'action'=>'created']);
        }

        $this->Locations = $this->fetchTable('Locations');
        $event_data = $this->Events->find("all", [
            'conditions'=>['Events.id'=>$id]
        ])
        ->contain(['Locations'])
        ->select($this->Events)
        ->select($this->Locations)
        ->first();
        if(!$event_data){
            $this->Flash->error(__('存在しないイベントIDです'));
            return $this->redirect(['controller'=>'Events', 'action'=>'index']);
        }
        if ($event_data->organizer_id != $uid){
            $this->Flash->error(__('イベント編集の権限がありません'));
            return $this->redirect(['controller'=>'Events', 'action'=>'created']);
        }


        $this->Locations = $this->fetchTable('Locations');
        // $event = $this->Events->newEmptyEntity();
        $locations = $this->Locations->find('all')->all()->toArray();
        
        $locations = Hash::combine($locations, '{n}.display_name', '{n}');
        $this->set(compact('event_data', 'locations'));

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            //候補から選択しなかった場合のLocation追加処理
            if($data['location_id'] == ''){
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
                $this->Flash->success(__('The location has been saved.'));
                $data["location_id"] = $result_location->id;
            } else {
                $location_data = $this->Locations->newEmptyEntity();
            }
            
            $location_data = $this->Locations->patchEntity($location_data,[
                "id" => $data["location_id"],
                "display_name"=>h($data['display_name']),
                "address"=>h($data['address']),
                "usage_price"=>$data['usage_price'],
                "night_price"=>$data['night_price'],
            ], ['accessibleFields' => ['id' => true]]);
            $result_location = $this->Locations->save($location_data);
            if (!$result_location) {
                $this->Flash->error(__('The location could not be updated. Please, try again.'));
                return $this->redirect(['controller'=>'Events','action' => 'created']);
            }

            $event_data = $this->Events->patchEntity($event_data,[
                "organizer_id" => $uid,
                "start_time"=>$data['event_date'] . ' ' . $data['start_time'] . ':00',
                "end_time"=>$data['event_date'] . ' ' . $data['end_time'] . ':00',
                "area"=>h($data['area']),
                "participants_limit"=>$data['participants_limit'],
                "comment"=>h($data['comment']),
                "location_id"=>$data['location_id'],
            ]);

            $result_event = $this->Events->save($event_data);
            if (!$result_event) {
                $this->Flash->error(__('The event could not be updated. Please, try again.'));
                return $this->redirect(['controller'=>'Events','action' => 'created']);
            }
            $this->Flash->success(__('The event has been updated.'));
            return $this->redirect(['controller'=>'Events','action' => 'created']);
        }
    }

    /**
     * Delete method
     *
     * @param string|null $id Event id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null) //events/createdからしかアクセスされない
    {
        $this->autoRender = false;
        $uid = $this->getLoginUserData(true);
        if(!$uid){
            $this->Flash->error(__('ユーザー情報の取得に失敗しました'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
        if(!$id){
            $this->Flash->error(__('イベントIDが存在していません'));
            return $this->redirect(['controller'=>'Events', 'action'=>'created']);
        }
        $event_data = $this->Events->find("all", ['conditions'=>['id'=>$id]])->first();
        if(!$event_data){
            $this->Flash->error(__('存在しないイベントIDです'));
            return $this->redirect(['controller'=>'Events', 'action'=>'index']);
        }
        if ($event_data->organizer_id != $uid){
            $this->Flash->error(__('イベント削除の権限がありません'));
            return $this->redirect(['controller'=>'Events', 'action'=>'created']);
        }
        $event_data = $this->Events->patchEntity($event_data, ['deleted_at'=>1]);
        $result = $this->Events->save($event_data);
        if(!$result){
            $this->Flash->error(__('イベント削除に失敗しました'));
            return $this->redirect(['controller'=>'Events', 'action'=>'created']);
        }
        $this->Flash->error(__('イベント削除に成功しました'));
        return $this->redirect(['controller'=>'Events', 'action'=>'created']);
    }

    public function restore($id = null) //events/createdからしかアクセスされない
    {
        $this->autoRender = false;
        $uid = $this->getLoginUserData(true);
        if(!$uid){
            $this->Flash->error(__('ユーザー情報の取得に失敗しました'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
        if(!$id){
            $this->Flash->error(__('イベントIDが存在していません'));
            return $this->redirect(['controller'=>'Events', 'action'=>'created']);
        }
        $event_data = $this->Events->find("all", ['conditions'=>['id'=>$id]])->first();
        if(!$event_data){
            $this->Flash->error(__('存在しないイベントIDです'));
            return $this->redirect(['controller'=>'Events', 'action'=>'index']);
        }
        if ($event_data->organizer_id != $uid){
            $this->Flash->error(__('イベント復元の権限がありません'));
            return $this->redirect(['controller'=>'Events', 'action'=>'created']);
        }
        $event_data = $this->Events->patchEntity($event_data, ['deleted_at'=>0]);
        $result = $this->Events->save($event_data);
        if(!$result){
            $this->Flash->error(__('イベント復元に失敗しました'));
            return $this->redirect(['controller'=>'Events', 'action'=>'created']);
        }
        $this->Flash->error(__('イベント復元に成功しました'));
        return $this->redirect(['controller'=>'Events', 'action'=>'created']);
    }
}
