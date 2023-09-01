<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Utility\Hash;
use Cake\Datasource\ConnectionManager;

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
    public function test(){
        // $this->autoRender = false;

        $this->EventResponses = $this->fetchTable('EventResponses');

        // $responsed_event_id = $this->EventResponses->find("all", [
        //     "conditions" => [
        //         "EventResponses.responder_id" => 1,
        //         "Events.start_time >" => date("Y-m-d H:i:s")
        //     ]
        // ])
        // ->select('EventResponses.event_id')
        // ->contain('Events')
        // ->all()->toArray();
        // $responsed_event_id = Hash::extract($responsed_event_id, '{n}.event_id');

        // $events = $this->Events->find("all", [
        //     "conditions"=>[
        //         "Events.id IN" => $responsed_event_id
        //     ]
        // ])->all()->toArray();


        // SELECT event_responses.event_id, event_responses.response_state, count(event_responses.response_state)
        // FROM events
        // INNER JOIN event_responses ON Events.id = event_responses.event_id
        // WHERE 1
        // GROUP BY event_responses.event_id, event_responses.response_state
    
        // // $events = $this->Events->find("all")
        // // ->contain('EventResponses')
        // // ->group('EventResponses.event_state')
        // // ->all()->toArray();
        // $events = $this->Events->find("all")
        // ->contain([
        //     'EventResponses' => function ($q) {
        //         return $q->select([
        //             'EventResponses.event_id',
        //             'EventResponses.response_state'
        //         ]);
        //     }
        // ])
        // ->select("EventResponses.event_id")
        // // ->group('EventResponses.event_id')
        // ->limit(10)
        // ->all()->toArray();
        


        
        
    }

    public function index()
    {
        $uid = null;
        if ($this->Authentication->getResult()->isValid()){
            $uid = $this->Authentication->getResult()->getData()['id'];
        }
        
        $this->Locations = $this->fetchTable('Locations');
        $conditions = [
            'Events.deleted_at IS' => NULL,
            'Events.end_time >=' => date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . "-14days"))
        ]; 
        $events_query = $this->Events->find("all", ['conditions'=>$conditions]);
        $events_query = $events_query
        ->contain([
            // 'Users', //開催者
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
        ->limit(50);
        $events = $events_query->all()->toArray();
        
        $day_of_week=[1=>'月',2=>'火',3=>'水',4=>'木',5=>'金',6=>'土',7=>'日']; //日付変換用の定数

        $events_formated = [];
        foreach($events as $event){
            $event_formated = $event;

            //開催日の取出
            // $event_formated['date'] = $event->start_time->i18nFormat('yyyy-MM-dd');
            // $event_formated['day_of_week'] = "{$day_of_week[$event->start_time->dayOfWeek]}";

            //時刻の比較
            $now_datetime = strtotime("now");
            $start_datetime = strtotime($event->start_time->i18nFormat('yyyy-MM-dd HH:mm:ss'));
            $end_datetime = strtotime($event->end_time->i18nFormat('yyyy-MM-dd HH:mm:ss'));
            if($now_datetime < $start_datetime){
                $event_formated['event_state'] = 0;
            } elseif($now_datetime > $end_datetime) {
                $event_formated['event_state'] = 2;
            } else {
                $event_formated['event_state'] = 1;
            }

            //ユーザの参加情報取出
            if ($uid){
                $user_event_responses = Hash::extract($event, 'event_responses.{n}[responder_id='.$uid.']');
                if(count($user_event_responses) <= 0){
                    $event_formated['user_response_state'] = null;
                } else {
                    $event_formated['user_response_state'] = $user_event_responses[0]['response_state'];
                }
            }

            //参加情報取出
            $event_responder_list = [0=>[], 1=>[], 2=>[]];
            foreach($event->event_responses as $event_response){
                $event_responder_list[$event_response->response_state][] = $event_response->user->display_name;
            }
            $event_formated['event_responses'] = $event_responder_list;

            
            $events_formated[] = $event_formated;
        }
        $events = $events_formated;
        
        $this->set(compact('events'));
    }

    public function unresponded(){ //未表明
        if ($this->Authentication->getResult()->isValid()){
            $uid = $this->Authentication->getResult()->getData()['id'];
        } else {
            $this->Flash->error(__('Failed to get member information. Please login.'));
            return $this->redirect(['action' => 'index']);
        }

        $this->EventResponses = $this->fetchTable('EventResponses');
        $conditions = [
            
        ];
        $event_responses = $this->EventResponses->find("all", ["conditions"=>$conditions])
        ->contain(['Events'])
        ->where([
            "EventResponses.responder_id NOT IN" => $uid, //自分
        ])
        ->select([
            'EventResponses.event_id',
        ])
        ->group('EventResponses.event_id')
        ->all()
        ->toArray();
        
        $event_ids = Hash::extract($event_responses, '{n}.event_id');
        $events = [];

        if(count($event_ids) > 0){  
            $this->Locations = $this->fetchTable('Locations');
            $conditions = [
                'Events.id IN' => $event_ids,
                'Events.end_time >=' => date("Y-m-d H:i:s"), //現在日時より後
                'Events.deleted_at IS' => NULL, //未削除
            ]; 
            $events_query = $this->Events->find("all", ['conditions'=>$conditions]);
            $events_query = $events_query
            ->contain([
                // 'Users', //開催者
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
            ->limit(50);

            $events = $events_query->all()->toArray();

            $events_formated = [];
            foreach($events as $event){
                $event_formated = $event;

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

                //参加情報取出
                $event_responder_list = [0=>[], 1=>[], 2=>[]];
                foreach($event->event_responses as $event_response){
                    $event_responder_list[$event_response->response_state][] = $event_response->user->display_name;
                }
                $event_formated['event_responses'] = $event_responder_list;

                
                $events_formated[] = $event_formated;

                
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

        $this->EventResponses = $this->fetchTable('EventResponses');
        $conditions = [
            
        ];
        $event_responses = $this->EventResponses->find("all", ["conditions"=>$conditions])
        ->contain(['Events'])
        ->where([
            "EventResponses.responder_id" => $uid, //自分
            'EventResponses.response_state IN' => [0, 1], //未定or参加
            'Events.deleted_at IS' => NULL, //未削除
            'Events.end_time >=' => date("Y-m-d H:i:s"), //現在日時より後
        ])
        ->select([
            'Events.id',
            'Events.end_time',
            'EventResponses.event_id',
            'EventResponses.responder_id',
            'EventResponses.response_state'
        ])
        ->all()
        ->toArray();

        $event_ids = Hash::extract($event_responses, '{n}.event_id');
        $events = [];

        if(count($event_ids) > 0){   
            $this->Locations = $this->fetchTable('Locations');
            $conditions = [
                'Events.id IN' => $event_ids
            ]; 
            $events_query = $this->Events->find("all", ['conditions'=>$conditions]);
            $events_query = $events_query
            ->contain([
                // 'Users', //開催者
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
            ->order(['Events.start_time'=>'DESC']);
            // ->limit(10);
            $events = $events_query->all()->toArray();
            
            $events_formated = [];
            foreach($events as $event){
                $event_formated = $event;

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
                $event['event_state'] = 0;

                //ユーザの参加情報取出
                if ($uid){
                    $user_event_responses = Hash::extract($event, 'event_responses.{n}[responder_id='.$uid.']');
                    if(count($user_event_responses) <= 0){
                        $event['user_response_state'] = null;
                    } else {
                        $event['user_response_state'] = $user_event_responses[0]['response_state'];
                    }
                }

                //参加情報取出
                $event_responder_list = [0=>[], 1=>[], 2=>[]];
                foreach($event->event_responses as $event_response){
                    $event_responder_list[$event_response->response_state][] = $event_response->user->display_name;
                }
                $event_formated['event_responses'] = $event_responder_list;

                

                $events_formated[] = $event_formated;
            };
            $events = $events_formated;
        }
        
        $this->set(compact('events'));
    }

    public function created(){
        if ($this->Authentication->getResult()->isValid()){
            $uid = $this->Authentication->getResult()->getData()['id'];
        } else {
            $this->Flash->error(__('Failed to get member information. Please login.'));
            return $this->redirect(['action' => 'index']);
        }

        $this->Locations = $this->fetchTable('Locations');
        $conditions = [
            'Events.organizer_id IN' => $uid
        ]; 
        $events_query = $this->Events->find("all", ['conditions'=>$conditions]);
        $events_query = $events_query
        ->contain([
            // 'Users', //開催者
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
        ->order(['Events.start_time'=>'DESC']);
        // ->limit(10);
        $events = $events_query->all()->toArray();
        
        $events_formated = [];
        foreach($events as $event){
            $event_formated = $event;

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

            //参加情報取出
            $event_responder_list = [0=>[], 1=>[], 2=>[]];
            foreach($event->event_responses as $event_response){
                $event_responder_list[$event_response->response_state][] = $event_response->user->display_name;
            }
            $event_formated['event_responses'] = $event_responder_list;

            
            $events_formated[] = $event_formated;

        };
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

        //参加情報取出
        $event_responder_list = [0=>[], 1=>[], 2=>[]];
        foreach($event->event_responses as $event_response){
            $event_responder_list[$event_response->response_state][] = ["name"=>$event_response->user->display_name, "time"=>$event_response->updated_at];
        }
        $event['event_responses'] = $event_responder_list;

        $event_prev = $this->Events->find("all", [
            "conditions" => ["Events.id <" => $id]
        ])->select('id')->order(['Events.id'=>'DESC'])->limit(1)->first();
        $event_next = $this->Events->find("all", [
            "conditions" => ["Events.id >" => $id]
        ])->select('id')->order(['Events.id'=>'ASC'])->limit(1)->first();

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
        $locations = Hash::combine($locations, '{n}.display_name', '{n}');
        $this->set(compact('event', 'locations'));

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $save_data = [];

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
