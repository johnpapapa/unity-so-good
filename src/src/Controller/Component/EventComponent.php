<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Datasource\FactoryLocator;
// use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\I18n\FrozenTime;
use Cake\Log\LogTrait;
use Cake\Utility\Hash;
use Psr\Log\LogLevel;
use Cake\Collection\Collection;

/**
 * @property \App\Model\Table\EventsTable $Events
 * @property \App\Model\Table\EventResponsesTable $EventResponses
 * @property \Cake\Controller\Component\FlashComponent $Flash
 */
class eventComponent extends Component
{
    protected $components = ['Flash'];

    public function initialize(array $config): void
    {
        $this->Locations = FactoryLocator::get('Table')->get('Locations');
        $this->Events = FactoryLocator::get('Table')->get('Events');
        $this->Users = FactoryLocator::get('Table')->get('Users');
        $this->EventResponses = FactoryLocator::get('Table')->get('EventResponses');
        $this->EventResponseLogs = FactoryLocator::get('Table')->get('EventResponseLogs');
        $this->log('testini', LogLevel::DEBUG);
    }

    /**
     * eventの配列を一覧で表示するために整形
     *
     * @param array $event_list
     * @param int $uid
     * @return array
     */
    public function getFormatEventDataList($event_list = [], $uid = null)
    {
        if (count($event_list) <= 0) {
            return [];
        }
        $formated_event_list = []; //整形後のevent
        $now_datetime = $this->getNow();
        foreach ($event_list as $event_data) {
            $formated_event_list[] = $this->formatEventData($event_data, $now_datetime, $uid);
        }

        return $formated_event_list;
    }

    // API用 -- イベントのリストを取得
    public function getEventListForApi(){
        $event_list = $this->Events->getEventList(
            $organizer_user_id=false,
            $contain_deleted_event=false,
            $contain_held_event=true,
            $contain_not_held_event=true,
            $is_disp_comment=true,
            $is_disp_response=false,
            $is_to_array=false
        );
        
        // 反応をグループごとにカウント
        $event_list = $event_list->map(function ($value, $key) {
            $value['event_responses'] = (new Collection($value['event_responses']))->countBy('response_state')->toArray();
            return $value;
        });
        return $event_list;
    }

    public function getEventByEventIdForApi($event_id)
    {
        $event_data = $this->Events->getEventByEventId(
            $event_id,
            $is_disp_comment=false,
            $is_disp_response=false,
            $is_to_array=false
        );
        $event_data['event_responses'] = (new Collection($event_data['event_responses']))->countBy('response_state')->toArray();
        // dd(array_count_values($event_data["event_responses"]));
        // dd($event_data);
        // $event_data = $event_data
        return $event_data;
    }

    // 指定した条件に応じたevent配列の取得
    public function getEventList($organizer_user_id = false, $contain_deleted_event = false, $contain_held_event = false, $contain_not_held_event = false)
    {
        return $this->Events->getEventList($organizer_user_id, $contain_deleted_event, $contain_held_event, $contain_not_held_event);
    }

    public function getArchivedEventList($is_logged_in)
    {
        $events = $this->Events->getArchivedEventList($is_logged_in);
        $events = $this->getFormatEventDataList($events);
        return $events;
    }

    public function getCreatedEventList($organizer_user_id){
        $events = $this->Events->getCreatedEventList($organizer_user_id);
        $events = $this->getFormatEventDataList($events);
        return $events;
    }

    // 指定したuserに紐づく未反応のeventId配列の取得

    public function getUnrespondedEventIdListByUserId($uid, $conditions)
    {
        return $this->Events->getUnrespondedEventIdListByUserId($uid, $conditions);
    }

    // 指定したuserに紐づく反応済みのeventId配列の取得

    public function getParticipateEventIdListByUserId($uid, $conditions)
    {
        return $this->Events->getParticipateEventIdListByUserId($uid, $conditions);
    }

    // 指定したeventに紐づくevent_response配列の取得

    public function getEventResponseListByEventId($event_id)
    {
        return $this->EventResponses->getEventResponseListByEventId($event_id);
    }

    // 指定したuserに紐づく全てのevent_response配列の取得

    public function getEventResponseListByUserId($user_id, $limit = null)
    {
        return $this->EventResponses->getEventResponseListByUserId($user_id, $limit);
    }

    // 指定したuserに紐づく全てのevent_response配列の取得

    public function getAllEventResponseListByUserId($user_id, $limit = null)
    {
 //未反応のイベントも含めたevent_response
        return $this->EventResponses->getEventResponseListByUserId($user_id, $limit);
    }

    // 指定したevent_idからeventを取得

    public function getEventByEventId($event_id)
    {
        return $this->Events->getEventByEventId($event_id);
    }

    // 指定したevent_id配列からeventを取得

    public function getEventListByEventId($event_id_list, $event_display_order = 'ASC', $response_display_order = 'ASC')
    {
        if (count($event_id_list) <= 0) { //id listが空の場合は空配列を返す
            return [];
        }

        return $this->Events->getEventListByEventId($event_id_list, $event_display_order, $response_display_order);
    }

    // 開始時間が隣り合うeventのid取得

    public function getNeighberEventId($start_time, $type)
    {
        $event_data = $this->Events->getNeighberEventId($start_time, $type);
        $event_data_id = $event_data->id ?? null;

        return $event_data_id;
    }

    /**
     * 現在時間
     *
     * @return \App\Controller\Component\Cake\I18n\FrozenTime
     */
    public function getNow()
    {
        return new FrozenTime('now');
    }

    

    /**
     * eventを一覧で表示するために整形
     *
     * @param array $event_data
     * @param int $uid
     * @return array
     */
    public function getFormatEventData($event_data, $uid = null)
    {
        $now_datetime = $this->getNow();

        return $this->formatEventData($event_data, $now_datetime, $uid);
    }

    /**
     * eventを一覧で表示するために整形
     *
     * @param array $event_data
     * @param \App\Controller\Component\Cake\I18n\FrozenTime $now_datetime
     * @param int $uid
     * @return array
     */
    public function formatEventData($event_data, $now_datetime, $uid = null)
    {
        //時刻の比較
        $event_data['event_state'] = $this->getEventState($now_datetime, $event_data['start_time'], $event_data['end_time']);

        //ユーザの参加情報取出
        if ($uid) {
            // dd($event_data["event_responses"][0]["responder_id"]);
            $resp = array_search($uid, array_column($event_data['event_responses'], 'responder_id'));
            $event_data['user_response_state'] = $resp !== false ? $event_data['event_responses'][$resp]->response_state : null;
        }

        //参加情報取出
        $event_data['event_responses'] = $this->categorizedEventResponseList($event_data['event_responses']);

        return $event_data;
    }

    public function getEventState($now_datetime, $start_time, $end_time)
    {
        if ($now_datetime < $start_time) {
            return 0;
        } elseif ($now_datetime > $end_time) {
            return 2;
        } else {
            return 1;
        }
    }

    /**
     * event_responsesをresponse_stateの値に応じてグループ分け
     *
     * @param array $event_response_list
     * @return array
     */
    public function categorizedEventResponseList($event_response_list)
    {
        $categorized_event_response_list = [0 => [], 1 => [], 2 => [], 'null' => []];
        // debug($event_response_list);
        
        foreach ($event_response_list as $event_response) {
            if (isset($event_response->user)) {
                $user_id = $event_response->user->id;
                $display_name = $event_response->user->display_name;
            } else {
                $user_id = $event_response['id'];
                $display_name = $event_response['display_name'];
            }

            if (isset($event_response->updated_at)) {
                $time = $event_response->updated_at;
            } else {
                $time = $event_response['updated_at'];
            }

            $categorized_event_response_list[(is_null($event_response['response_state']) ? 'null' : $event_response['response_state'])][] = [
                'id' => $user_id,
                'display_name' => $display_name,
                'time' => $time,
            ];
        }

        //NOTE: 不参加の場合は上に新しいデータが表示されると確認しやすく、参加や未定の場合は下にどんどん新しいデータが追加されると自然
        //response_stateが2の場合(不参加)のみupdated_atの降順でソート
        $categorized_event_response_list[2] = Hash::sort($categorized_event_response_list[2], '{n}.updated_at', 'desc');

        return $categorized_event_response_list;
    }
}
