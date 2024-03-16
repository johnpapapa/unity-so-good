<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Datasource\ConnectionManager;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Psr\Log\LogLevel;
use Cake\Core\Configure;

/**
 * Administrators Controller
 *
 * @property \App\Controller\Component\EventComponent $Event
 * @property \App\Model\Table\AdministratorsTable $Administrators
 */
class AdministratorsController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        if (!$this->Login->isAdministrator()) {
            $this->Flash->error(__('管理者権限がありません。'));

            return $this->redirect(['prefix' => false, 'controller' => 'Events', 'action' => 'index']);
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

    public function userList()
    {
        $this->Users = $this->fetchTable('Users');
        $user_data = $this->Users->find('all', ['conditions' => ['deleted_at' => false]])->toArray();
        $this->set(compact('user_data'));
    }

    public function deletedUserList()
    {
        $this->Users = $this->fetchTable('Users');
        $user_data = $this->Users->find('all', ['conditions' => ['deleted_at' => true]])->toArray();
        $this->set(compact('user_data'));
    }

    public function eventList()
    {
        $this->loadComponent('Event');

        $this->Events = $this->fetchTable('Events');
        $this->Locations = $this->fetchTable('Locations');
        $conditions = [
            'Events.deleted_at IS' => 0, //削除前のイベント
            'Events.end_time >=' => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . '-14days')), //14日前までのイベント
        ];
        $events_query = $this->Events->find('all', ['conditions' => $conditions]);
        $events_query = $events_query
            ->contain([
                'Locations',
                'EventResponses' => [
                    'sort' => [
                        'response_state' => 'DESC', //反応した種類順
                        'EventResponses.updated_at' => 'ASC', //反応した時間順
                    ],
                ],
            ])
            ->select($this->Events)
            ->select($this->Locations)
            ->contain('EventResponses.Users') //EventResponsesに紐づくUsersオブジェクト作成
            ->order(['Events.start_time' => 'ASC']) //Eventが表示される順番
            ->limit(50);
        $events = $events_query->all()->toArray();

        $events = $this->Event->getFormatEventDataList($events);

        $this->set(compact('events'));
    }

    public function userDetail($id = null)
    {
        if (!$id) {
            $this->Flash->error(__('ユーザー情報の取得に失敗しました'));

            return $this->redirect($this->request->referer());
        }
        $this->set(['user_id' => $id]);

        $this->Users = $this->fetchTable('Users');
        $user_data = $this->Users->find('all', ['conditions' => ['id' => $id]])->first();
        if (!$user_data) {
            $this->Flash->error(__('存在しないユーザーを指定しました'));

            return $this->redirect($this->request->referer());
        }
        $this->set(['is_user_deleted' => $user_data["deleted_at"]]);

        $this->loadComponent('Event');

        $this->Events = $this->fetchTable('Events');
        $this->Locations = $this->fetchTable('Locations');

        //未反応情報の取得
        //ユーザーが作成された日以降のイベントで反応していないものを表示
        $sql_statement = <<<EOF
        select 
            e.id as eid,
            events.start_time, 
            events.end_time, 
            locations.display_name,
            CASE 
                WHEN ISNULL(event_responses.response_state) THEN 'null' 
                ELSE event_responses.response_state 
            END as response_state
        from (
            select events.id
            from events
            where events.start_time > cast('{$user_data->created_at->i18nFormat("YYYY/MM/dd HH:mm:ss")}' as datetime)
        ) as e
        cross join ( select users.id from users where users.id = {$id} ) as u
        left join event_responses on event_responses.responder_id = u.id AND event_responses.event_id = e.id
        inner join events on events.id = e.id
        inner join locations on locations.id = events.location_id
        order by events.start_time DESC
        EOF;
        $event_response_history_list = ConnectionManager::get('default')->execute($sql_statement)->fetchAll('assoc');

        $unresponded_history_list = Hash::extract($event_response_history_list, '{n}[response_state=null]');

        // 反応回数算出
        $event_response_history_count_list = array_count_values(Hash::extract($event_response_history_list, '{n}.response_state'));

        // コート別参加率
        // where events.start_time <= cast(CURRENT_DATE as date)
        $sql_statement = <<<EOF
        select 
            locations.id,
            locations.display_name,
            COUNT(events.id) as sum_all,
            COUNT(CASE WHEN event_responses.response_state=2 THEN event_responses.response_state END) AS sum_2,
            COUNT(CASE WHEN event_responses.response_state=1 THEN event_responses.response_state END) AS sum_1,
            COUNT(CASE WHEN event_responses.response_state=0 THEN event_responses.response_state END) AS sum_0
        from (
            select events.id
            from events
            where events.start_time > '{$user_data->created_at->i18nFormat("YYYY/MM/dd HH:mm:ss")}'
        ) as e
        cross join ( select users.id from users where users.id = {$id} ) as u
        left join 
            event_responses 
        on 
            event_responses.responder_id = u.id AND event_responses.event_id = e.id
        join events on events.id = e.id
        join locations on locations.id = events.location_id
        group by locations.id;
        EOF;
        $location_counted_response_count_list = ConnectionManager::get('default')->execute($sql_statement)->fetchAll('assoc');
        $location_response_ratio_list = [];
        for ($lcrcl = 0; $lcrcl < count($location_counted_response_count_list); $lcrcl++) {
            $sum_all = $location_counted_response_count_list[$lcrcl]['sum_all'];
            if ($sum_all <= 0) {
                $location_counted_response_count_list[$lcrcl]['ratio_0'] = 0;
                $location_counted_response_count_list[$lcrcl]['ratio_1'] = 0;
                $location_counted_response_count_list[$lcrcl]['ratio_2'] = 0;
            } else {
                $location_counted_response_count_list[$lcrcl]['ratio_0'] = round($location_counted_response_count_list[$lcrcl]['sum_0'] / $sum_all * 100, 1);
                $location_counted_response_count_list[$lcrcl]['ratio_1'] = round($location_counted_response_count_list[$lcrcl]['sum_1'] / $sum_all * 100, 1);
                $location_counted_response_count_list[$lcrcl]['ratio_2'] = round($location_counted_response_count_list[$lcrcl]['sum_2'] / $sum_all * 100, 1);
            }
        }

        // 曜日別参加率
        // 時間別参加率


        $this->set(compact(
            'user_data',
            'event_response_history_list',
            'event_response_history_count_list',
            'unresponded_history_list',
            'location_counted_response_count_list',
        ));
    }

    public function participantsCount()
    {
        //イベント参加率の統計を表示
        $this->loadComponent('Event');
        $this->Events = $this->fetchTable('Events');
        $this->Locations = $this->fetchTable('Locations');
        $this->Users = $this->fetchTable('Users');

        //削除済みのuserは集計しない
        $sql_statement = <<<EOF
        select t.uid as uid, count(t.uid) as cnt
        from (
            select events.id as eid, users.id as uid, users.display_name
            from (
                select e.id as eid, u.id as uid
                from (
                    select events.id
                    from events
                    where events.start_time BETWEEN cast('1970-01-01' as date) AND cast(CURRENT_DATE as date)
                ) as e
                cross join (select users.id from users where users.deleted_at=0) as u
            ) as cross_t
            inner join events on events.id = cross_t.eid
            inner join users on users.id = cross_t.uid
            WHERE events.start_time > users.created_at AND events.start_time < NOW()
        ) as t
        inner join  event_responses
        on t.eid = event_responses.event_id AND t.uid = event_responses.responder_id
        group by (t.uid)
        EOF;

        $participants_count_data = ConnectionManager::get('default')->execute($sql_statement)->fetchAll('assoc');

        $sql_statement = <<<EOF
        select users.id as uid, count(users.id) as cnt, users.display_name
        from (
            select e.id as eid, u.id as uid
            from (
                select events.id
                from events
                where events.start_time BETWEEN cast('1970-01-01' as date) AND cast(CURRENT_DATE as date)
            ) as e
            cross join (select users.id from users where users.deleted_at=0) as u
        ) as cross_t
        inner join events on events.id = cross_t.eid
        inner join users on users.id = cross_t.uid
        WHERE events.start_time > users.created_at AND events.start_time < NOW()
        group by (users.id)
        EOF;
        $all_count_data = ConnectionManager::get('default')->execute($sql_statement)->fetchAll('assoc');

        $participants_count_list = [];
        $pcd_idx_diff  = 0; //$participants_count_dataを参照する時に$all_count_dataとのoffsetを表すindex
        for ($idx = 0; $idx < count($all_count_data); $idx++) {
            $cnt = 0;

            // $this->log("{$idx}");
            // $this->log("{$pcd_idx_diff}");

            $isset_response_data = isset($participants_count_data[$pcd_idx_diff]);
            if ($isset_response_data) {
                $issame_responder = ($all_count_data[$idx]['uid'] == $participants_count_data[$pcd_idx_diff]['uid']);
                $all_uid = $all_count_data[$idx]['uid'];
                $p_uid = $participants_count_data[$pcd_idx_diff]['uid'];
                $dis = $all_count_data[$idx]['display_name'];
                $all_c = $all_count_data[$idx]['cnt'];
                $p_c = $participants_count_data[$pcd_idx_diff]['cnt'];
                $cc = $all_count_data[$idx]['cnt'] - $participants_count_data[$pcd_idx_diff]['cnt'];
                $this->log("( all:{$all_uid} == p:{$p_uid} ) = {$issame_responder}, {$dis}");
                $this->log("all:{$all_c} - p:{$p_c} = {$cc}");
            } else {
                $an = $all_count_data[$idx]['display_name'];
                $this->log("{$an}");
            }

            if ($isset_response_data && $issame_responder) {
                $cnt = $all_count_data[$idx]['cnt'] - $participants_count_data[$pcd_idx_diff]['cnt'];
                $pcd_idx_diff++;
            } else {
                $cnt = $all_count_data[$idx]['cnt'];
            }

            $participants_count_list[] = [
                'id' => $all_count_data[$idx]['uid'],
                'display_name' => $all_count_data[$idx]['display_name'],
                'cnt' => $cnt,
            ];
        }
        $this->log("{$idx}");
        $this->log("{$pcd_idx_diff}");
        $participants_count_list = Hash::sort($participants_count_list, '{n}.cnt', 'desc', 'numeric');

        $this->set(compact('participants_count_list'));
    }

    public function cancellationList()
    {
        $this->loadComponent('Event');
        $this->Events = $this->fetchTable('Events');
        $this->Users = $this->fetchTable('Users');
        $this->EventResponses = $this->fetchTable('EventResponses');
        $this->EventResponseLogs = $this->fetchTable('EventResponseLogs');

        //NOTE:イベント数も少ないので、開催済みまたはイベント開始1日前のイベントで状態変更したユーザーをイテレーションする形でDB問い合わせ
        //DBへの問い合わせ数が多いのでイベント数が増えれば増えるほどえぐい


        //未開催またはイベント開始が今から1日後のイベントを取得
        $eventQuery = $this->Events->find()
            ->contain(['Locations'])
            ->where(['start_time <=' => new \DateTime('+ 1 day')])
            ->order(['Events.start_time' => 'DESC']);
        $event_data_list = $eventQuery->toArray();

        $responder_grouped_by_event = [];
        foreach ($event_data_list as $event_data) {


            //同じイベントに対して2回以上応答したresponder_idのリストを取得
            $subQuery = $this->EventResponseLogs->find()
                ->select(['responder_id'])
                ->where(['event_id IN' => $event_data->id])
                ->group(['event_id', 'responder_id'])
                ->having(['count(event_id) >=' => 2]);

            //同じイベントに対して２回以上応答したuserから、状態変更の履歴を取得
            $query = $this->EventResponseLogs->find()
                ->where([
                    'responder_id IN' => $subQuery,
                    'event_id IN' => $event_data->id,
                ])
                ->contain(['Users'])
                ->order(['EventResponseLogs.created_at' => 'DESC']);
            $results = $query->all();
            //Hashを使って、同じresponder_idでグループを作成する
            $results = collection($results)->groupBy('responder_id')->toArray();

            
            //最新の状態変更をしたユーザーを取得
            $latest_modified_list = [];
            foreach ($results as $result) {
                //Hashを使って、resultからresponse_stateとcreated_atの配列を取得
                

                // $pp = collection($result)->extract('response_state', 'created_at')->toArray();
                // $pprs = collection($result)->extract('response_state')->toArray();
                // $ppc = collection($result)->extract('created_at')->toArray();
                // $pp = array_merge($pprs, $ppc);
                // print_r("<pre />");
                // print_r($pp);

                for ($result_idx = 0; $result_idx < count($result) - 1; $result_idx++) {
                    
                    //イベント開始後に状態変更したのは無視
                    if ($result[$result_idx]->created_at > $event_data->start_time) {
                        continue;
                    }

                    //イベント開始1日前に状態変更したユーザーを取得
                    if ($result[$result_idx]->created_at > $event_data->start_time->modify('-5 day')) {
                        // $pp = [
                        //     "0" => $result[$result_idx]->user->display_name,
                        //     "1" =>$result[$result_idx]->response_state,
                        //     "2" => $result[$result_idx]->created_at->i18nFormat('YYYY/MM/dd HH:mm:ss'),
                        // ];
                        // print_r("<pre />");
                        // print_r($pp);
                        
                        //イベント開始直前に参加から不参加にした人を取得
                        if ($result[$result_idx]->response_state == 2 && $result[$result_idx + 1]->response_state == 1) {
                            $latest_modified_list[] = [
                                "responder_id" => $result[$result_idx]->responder_id,
                                "responder_name" => $result[$result_idx]->user->display_name,
                                "logs" => array_slice($result, $result_idx, 5) //状態変更の履歴を最大5つ取得
                            ];
                        }
                        break;
                    }

                    break;
                }
            }

            if(count($latest_modified_list) > 0){
                $responder_grouped_by_event[] = [
                    "event_id" => $event_data->id,
                    "event_date" => $event_data->start_time->i18nFormat('YYYY/MM/dd'),
                    "event_start_time" => $event_data->start_time->i18nFormat('HH:mm'),
                    "event_end_time" => $event_data->end_time->i18nFormat('HH:mm'),
                    "location_name" => $event_data->location->display_name,
                    "latest_modified_list" => $latest_modified_list
                ];
            }
        }

        $this->set(compact('responder_grouped_by_event'));
    }

    public function eventDetail($id = null)
    {
        if (!$id) {
            $this->Flash->error(__('イベント情報の取得に失敗しました'));

            return $this->redirect($this->request->referer());
        }

        $this->loadComponent('Event');

        $this->Events = $this->fetchTable('Events');
        $event = $this->Events->find('all', [
            'conditions' => ['Events.id' => $id],
        ])
            ->contain([
                'Locations',
                'Comments' => function (Query $query) {
                    return $query
                        ->contain('Users')
                        ->where(['Comments.deleted_at' => 0])
                        ->order(['Comments.updated_at' => 'ASC']);
                },
                'EventResponses' => function (Query $query) {
                    return $query
                        ->contain('Users')
                        ->order([
                            'EventResponses.updated_at' => 'ASC',
                            'EventResponses.response_state' => 'DESC',
                        ]);
                },
            ])
            ->first();
        $event = $this->Event->getFormatEventData($event);

        $event_response_list = $this->Event->getEventResponseListByEventId($id);
        $categorized_event_response_list = $this->Event->categorizedEventResponseList($event_response_list);

        $this->set(compact('event', 'categorized_event_response_list'));
    }


    public function editInformation()
    {
        $login_user_data = $this->Login->getLoginUserData();

        if (!$login_user_data) {
            $this->log($login_user_data);
            $this->Flash->error(__('ユーザー情報の取得に失敗しました。'));

            return $this->redirect(['prefix' => false, 'controller' => 'Users', 'action' => 'login']);
        }

        if (!$this->Login->isAdministrator()) {
            $this->Flash->error(__('編集する権限がありません'));

            return $this->redirect(['prefix' => false, 'controller' => 'Informations', 'action' => 'about']);
        }
        $this->Informations = $this->fetchTable('Informations');
        $information_data = $this->Informations->find('all')->first();
        if (!$information_data) {
            $information_data = $this->Informations->newEmptyEntity();
        }

        $this->set(compact('information_data'));

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            $save_data = [];
            if (isset($data['about'])) {
                $save_data['about'] = $data['about'];
            }
            if (isset($data['rule'])) {
                $save_data['rule'] = $data['rule'];
            }

            $information_data = $this->Informations->patchEntity($information_data, $save_data);
            $result_information = $this->Informations->save($information_data);

            if ($result_information) {
                $this->Flash->success(__('The infomation data has been saved.'));

                return $this->redirect(['prefix' => false, 'controller' => 'informations', 'action' => 'about']);
            }
            $this->Flash->error(__('The information data could not be saved. Please, try again.'));

            return;
        }
    }

    public function ajaxDeleteUser()
    {
        $this->autoRender = false;
        $response = ['status' => ''];

        $uid = $this->Login->getLoginUserData(true);
        if (!$uid) {
            $this->Flash->error(__('ユーザー取得に失敗しました'));
        }

        $this->Users = $this->fetchTable('Users');
        $data = $this->request->getData();
        $user_id = $data['user_id'];
        $user_data = $this->Users->find('all', ['conditions' => ['id' => $user_id]])->first();
        if (!$user_data) {
            $this->Flash->error(__('ユーザ情報取得に失敗しました'));
        }

        $user_data = $this->Users->patchEntity($user_data, ['deleted_at' => 1]);
        try {
            $result = $this->Users->saveOrFail($user_data);
        } catch (\Cake\ORM\Exception\PersistenceFailedException $e) {
            $response['error'] = print_r($e);
        }

        if (!$result) {
            $response['status'] = 'bad';
            $this->RequestHandler->respondAs('application/json; charset=UTF-8');
            return $this->response->withStringBody(json_encode($response));
        }

        if (!$user_data['line_user_id']) {
            $response['status'] = 'ok';
            $response['status2'] = 'not found line_user_id this user.';
            $this->RequestHandler->respondAs('application/json; charset=UTF-8');
            return $this->response->withStringBody(json_encode($response));
        }

        $this->RejectedTokens = $this->fetchTable('RejectedTokens');
        $rejected_token_data = $this->RejectedTokens->find('all', ['conditions' => ['line_user_id' => $user_data['line_user_id']]])->first();

        if (!$rejected_token_data) {
            $rejected_token_data = $this->RejectedTokens->newEntity(['line_user_id' => $user_data['line_user_id']]);
            try {
                $result = $this->RejectedTokens->saveOrFail($rejected_token_data);
            } catch (\Cake\ORM\Exception\PersistenceFailedException $e) {
                $response['error'] = print_r($e);
            }
        }



        if ($result) {
            $response['status'] = 'ok';
        } else {
            $response['status'] = 'bad';
        }

        $this->RequestHandler->respondAs('application/json; charset=UTF-8');

        return $this->response->withStringBody(json_encode($response));
    }
}
