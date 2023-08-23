<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\Utility\Hash;
use DateTime;

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
        

        $events = $this->Events->find("all")
        ->contain([
            'Users', 
            'Locations',
            'EventResponses' => ['sort' => ['response_state' => 'DESC']]
        ])
        ->contain('EventResponses.Users')
        ->order(['events.start_time'=>'DESC'])
        ->limit(30)
        ->all()->toArray();
        
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
                $event['user_response_state'] = isset($user_event_responses->response_state) ? $user_event_responses->response_state : null;
            }

            return $event;
        });

        $this->set(compact('events'));
    }

    public function detail($id = null)
    {
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

        $this->set(compact('event'));
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
