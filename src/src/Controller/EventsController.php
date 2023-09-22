<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Utility\Hash;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;

/**
 * Events Controller
 *
 * @property \App\Model\Table\EventsTable $Events
 * @property \App\Controller\Component\EventComponent $Event
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
        
        $events = $this->Event->getEventList(false, false, false, true);
        $events = $this->Event->getFormatEventDataList($events, $uid);
        
        $this->set(compact('events'));
    }

    public function archived(){ //開催済み
        $uid = $this->getLoginUserData(true);   
        
        $events = $this->Event->getEventList(false, false, true, false);
        $events = $this->Event->getFormatEventDataList($events);
        
        $this->set(compact('events'));
    }

    public function unresponded(){ //未表明
        $uid = $this->getLoginUserData(true);
        if(!$uid){
            $this->Flash->error(__('ユーザー情報の取得に失敗しました。'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
        
        $event_responses = $this->Event->getEventIdList($uid, true);
        $event_id_list = Hash::extract($event_responses, '{n}.id');
        
        $events = $this->Event->getEventListByEventId($event_id_list);
        $events = $this->Event->getFormatEventDataList($events, $uid);

        $this->set(compact('events'));
    }

    public function participate(){ //表明済み
        $uid = $this->getLoginUserData(true);
        if(!$uid){
            $this->Flash->error(__('ユーザー情報の取得に失敗しました。'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $event_responses = $this->Event->getEventIdList($uid, false);
        $event_id_list = Hash::extract($event_responses, '{n}.id');

        $events = $this->Event->getEventListByEventId($event_id_list);
        $events = $this->Event->getFormatEventDataList($events, $uid);
        
        $this->set(compact('events'));
    }

    public function created(){
        $uid = $this->getLoginUserData(true);
        if(!$uid){
            $this->Flash->error(__('ユーザー情報の取得に失敗しました。'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
        
        $events = $this->Event->getEventList($uid, true, true, true);
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
            'Locations',
            'Comments' => function (Query $query){
                return $query
                    ->contain('Users')
                    ->where(['Comments.deleted_at' => 0])
                    ->order(['Comments.updated_at'=>'ASC']);
            },
            'EventResponses' => function (Query $query){
                return $query
                    ->contain('Users')
                    ->order([
                        'EventResponses.updated_at'=>'ASC', 
                        'EventResponses.response_state'=>'DESC'
                    ]);
            }
        ])
        ->first();

        if($event->deleted_at && $event->organizer_id != $uid){ //削除されていた時
            $this->Flash->error(__('このイベントはすでに削除されています'));
            return $this->redirect(['controller'=>'Events', 'action'=>'index']);
        }
                    
        $event = $this->Event->getFormatEventData($event, $uid);

        $event_prev = $this->Event->getNeighberEvent($event["start_time"], 'previous');
        $event_next = $this->Event->getNeighberEvent($event["start_time"], 'next');

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

    public function ajaxSubmitComment(){
        $this->autoRender = false;
        $response = ['status'=>''];
        $uid = $this->getLoginUserData(true);
        if(!$uid){
            $this->Flash->error(__('ユーザー情報の取得に失敗しました'));
        }

        $data = $this->request->getData();
        $event_id = $data['event_id'];
        $body = $data['body'];
        $this->Comments = $this->fetchTable('Comments');
        $comment_data = $this->Comments->newEmptyEntity();
        $comment_data = $this->Comments->patchEntity($comment_data,['event_id'=>$event_id,'user_id'=>$uid, 'body'=>$body]);
        try {
            $result = $this->Comments->saveOrFail($comment_data);
        } catch (\Cake\ORM\Exception\PersistenceFailedException $e) {
            $response['error'] = print_r($e);
            // $response['entity'] = $e->getEntity();
            // $repsonse['model'] = print_r($this->Comments);
            // debug($e);
        }
        
        if ($result){
            $response['status']='ok';
        } else {
            $response['status']='bad';
        }

        $this->RequestHandler->respondAs('application/json; charset=UTF-8');
        return $this->response->withStringBody(json_encode($response));
    }
    public function ajaxDeleteComment(){
        $this->autoRender = false;
        $response = ['status'=>''];
        $uid = $this->getLoginUserData(true);
        if(!$uid){
            $this->Flash->error(__('ユーザー情報の取得に失敗しました'));
        }

        $data = $this->request->getData();
        $comment_id = $data['comment_id'];
        $this->Comments = $this->fetchTable('Comments');
        $comment_data = $this->Comments->find('all', ['conditions'=>['id'=>$comment_id]])->first();
        if(!$comment_data){
            $this->Flash->error(__('コメント情報の取得に失敗しました'));
        }
        $comment_data = $this->Comments->patchEntity($comment_data,['deleted_at'=>1]);
        try {
            $result = $this->Comments->saveOrFail($comment_data);
        } catch (\Cake\ORM\Exception\PersistenceFailedException $e) {
            $response['error'] = print_r($e);
            // $response['entity'] = $e->getEntity();
            // $repsonse['model'] = print_r($this->Comments);
            // debug($e);
        }
        
        if ($result){
            $response['status']='ok';
        } else {
            $response['status']='bad';
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

            $start_time = FrozenTime::createFromFormat('H:i',$data['start_time'])->i18nFormat('HH:mm:00');
            $end_time = FrozenTime::createFromFormat('H:i',$data['end_time'])->i18nFormat('HH:mm:00');
            //使用料算出
            $area = str_replace(["、", "，", "､"], ",", mb_convert_kana($data['area'], "as"));

            $event_data = $this->Events->newEntity([
                "organizer_id" => $uid,
                "start_time"=>$data['event_date'] . ' ' . $start_time,
                "end_time"=>$data['event_date'] . ' ' . $end_time,
                "area"=>h($area),
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
        $locations = $this->Locations->find('all')->all()->toArray();
        
        $locations = Hash::combine($locations, '{n}.display_name', '{n}');
        $this->set(compact('event_data', 'locations'));

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            $data['start_time'] = FrozenTime::createFromFormat('H:i',$data['start_time'])->i18nFormat('HH:mm:00');
            $data['end_time'] = FrozenTime::createFromFormat('H:i',$data['end_time'])->i18nFormat('HH:mm:00');
            
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

            $area = str_replace(["、", "，", "､"], ",", mb_convert_kana($data['area'], "as"));

            $event_data = $this->Events->patchEntity($event_data,[
                "organizer_id" => $uid,
                "start_time"=>$data['event_date'] . ' ' . $data['start_time'],
                "end_time"=>$data['event_date'] . ' ' . $data['end_time'],
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
