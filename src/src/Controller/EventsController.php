<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Utility\Hash;

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


        // debug($events);


        // SELECT event_responses.event_id, event_responses.response_state, count(event_responses.response_state)
        // FROM events
        // INNER JOIN event_responses ON events.id = event_responses.event_id
        // WHERE 1
        // GROUP BY event_responses.event_id, event_responses.response_state
    
        // // $events = $this->Events->find("all")
        // // ->contain('EventResponses')
        // // ->group('EventResponses.event_state')
        // // ->all()->toArray();
        // // debug($events);
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

        $conditions = [];
        $query_param = $this->request->getQuery();
        $conditions[] = ['Events.end_time >=' => date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . "-14days"))]; //14日前までのイベントを表示

        if(isset($query_param['organizer'])){ //作成したユーザで絞り込み
            $organizer = $query_param['organizer'];
            if (is_numeric($organizer) && $organizer > 0){
                $conditions[] = ['Events.organizer_id' => $organizer];
            }
        }
        // $conditions[] = ['not exists '.'(select event_responses.event_id,event_responses.responder_id from event_responses '.'where event_responses.responder_id = '.'1)'];
        
        $events_query = $this->Events->find("all", ['conditions'=>$conditions])
        ->contain([
            'Users', 
            'Locations',
            'EventResponses' => ['sort' => ['response_state' => 'DESC']]
        ])
        ->contain('EventResponses.Users')
        ->order(['events.start_time'=>'ASC'])
        ->limit(10);
        $events = $events_query->all()->toArray();
        
        $day_of_week=['月','火','水','木','金','土','日']; //日付変換用の定数
        $events = Hash::map($events, '{n}', function($event) use ($day_of_week, $uid) { //各データの整形
            //開催日の取出
            $date = $event->start_time->i18nFormat('yyyy-MM-dd');
            $event['day_of_week'] = "{$day_of_week[$event->start_time->dayOfWeek-1]}";
            [$date_y, $date_m, $date_d] = explode('-', $date);
            $event['date_y'] = $date_y;
            $event['date_m'] = $date_m;
            $event['date_d'] = $date_d;
                        
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

            //参加人数の取出
            $event['participants_0_count'] = count(Hash::extract($event, 'event_responses.{n}[response_state=0]'));
            $event['participants_1_count'] = count(Hash::extract($event, 'event_responses.{n}[response_state=1]'));
            $event['participants_2_count'] = count(Hash::extract($event, 'event_responses.{n}[response_state=2]'));

            //ユーザの参加情報取出
            if ($uid){
                $user_event_responses = Hash::extract($event, 'event_responses.{n}[responder_id='.$uid.']');
                $event['user_response_state'] = isset($user_event_responses[0]['response_state']) ? $user_event_responses[0]['response_state'] : null;
            }

            return $event;
        });
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
            "conditions" => ["events.id" => $id]
        ])
        ->contain([
            'Users', 
            'Locations',
            'EventResponses' => ['sort' => ['response_state' => 'DESC']]
        ])
        ->contain('EventResponses.Users')
        ->first();

        $day_of_week=['月','火','水','木','金','土','日']; //日付変換用の定数
        //開催日の取出
        $date = $event->start_time->i18nFormat('yyyy-MM-dd');
        $event['day_of_week'] = "{$day_of_week[$event->start_time->dayOfWeek-1]}";
        [$date_y, $date_m, $date_d] = explode('-', $date);
        $event['date_y'] = $date_y;
        $event['date_m'] = $date_m;
        $event['date_d'] = $date_d;
                    
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

        //参加人数の取出
        $event['participants_0_count'] = count(Hash::extract($event, 'event_responses.{n}[response_state=0]'));
        $event['participants_1_count'] = count(Hash::extract($event, 'event_responses.{n}[response_state=1]'));
        $event['participants_2_count'] = count(Hash::extract($event, 'event_responses.{n}[response_state=2]'));

        //ユーザの参加情報取出
        if ($uid){
            $user_event_responses = Hash::extract($event, 'event_responses.{n}[responder_id='.$uid.']');
            $event['user_response_state'] = isset($user_event_responses[0]['response_state']) ? $user_event_responses[0]['response_state'] : null;
        }

        $this->set(compact('event'));
    }

    public function ajaxChangeResponseState(){
        $this->autoRender = false;
        $data = $this->request->getData();

        // $user = $this->EventResponses->newEmptyEntity();
        $this->loadModel('EventResponses');
        // $this->fetchTable('EventResponses');
        $event_response = $this->EventResponses->find('all', ['conditions'=>['responder_id'=>$data['user_id'],'event_id'=>$data['event_id']]])->first();
        if(!$event_response){
            $event_response = $this->EventResponses->newEntity(['responder_id'=>$data['user_id'],'event_id'=>$data['event_id']]);
        }
        $event_response = $this->EventResponses->patchEntity($event_response, ['response_state'=>$data['response_state']]);
        // $response = ['er'=>$event_response];
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
        $event = $this->Events->newEmptyEntity();
        if ($this->request->is('post')) {
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
