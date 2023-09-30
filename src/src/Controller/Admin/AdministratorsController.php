<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Datasource\ConnectionManager;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;

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

            return $this->redirect(['prefix' => false,'controller' => 'Events', 'action' => 'index']);
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
        $user_data = $this->Users->find('all')->toArray();
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

        $this->Users = $this->fetchTable('Users');
        $user_data = $this->Users->find('all', ['conditions' => ['id' => $id]])->first();
        if (!$user_data) {
            $this->Flash->error(__('存在しないユーザーを指定しました'));

            return $this->redirect($this->request->referer());
        }

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

            // if(
            //     isset($participants_count_data[$pcd_idx_diff])
            //     && $all_count_data[$idx]['uid'] == $participants_count_data[$pcd_idx_diff]['uid']
            // ){ //参照している配列のuidが一致している場合all_count(全イベント数) - participants_count(全部反応数)を算出する
            //     $cnt = $all_count_data[$idx]['cnt'] - $participants_count_data[$$pcd_idx_diff]['cnt'];
            //     // debug($all_count_data[$idx]['uid']);
            //     // debug($participants_count_data[$idx - $pcd_idx_diff]['uid']);
            //     // debug($all_count_data[$idx]['cnt']);
            //     // debug($participants_count_data[$idx - $pcd_idx_diff]['cnt']);
            //     $pcd_idx_diff = $pcd_idx_diff + 1;
            // } else { //一度も反応してない人の場合全イベント数を未反応数とする
            //     $cnt = $all_count_data[$idx]['cnt'];
            // }


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
}
