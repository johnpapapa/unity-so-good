<?php
 
namespace App\Controller\Component;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\FrozenTime;
use Cake\Utility\Hash;
// use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Datasource\ModelAwareTrait;

/**
 * @property \App\Model\Table\EventsTable $Events
 * @property \App\Model\Table\EventResponsesTable $EventResponses
 * 
 */
class eventComponent extends Component
{
    public function initialize(array $config): void
    {
        $this->Locations = FactoryLocator::get('Table')->get('Locations');
        $this->Events = FactoryLocator::get('Table')->get('Events');
        $this->Users = FactoryLocator::get('Table')->get('Users');
        $this->EventResponses = FactoryLocator::get('Table')->get('EventResponses');
    }

    public function getEventIdList($uid, $is_unrespond=false, $start_order='ASC'){
        return $this->Events->getEventIdList($uid, $is_unrespond, $start_order);
    }

    public function getEventList($organizer_user_id=false,$contain_deleted_event=false, $contain_held_event=false, $contain_not_held_event=false){
        return $this->Events->getEventList($organizer_user_id, $contain_deleted_event, $contain_held_event, $contain_not_held_event);
    }
    
    public function getUnrespondedEventIdListByUserId($uid, $conditions){
        return $this->Events->getUnrespondedEventIdListByUserId($uid, $conditions);
    }

    public function getParticipateEventIdListByUserId($uid, $conditions){
        return $this->Events->getParticipateEventIdListByUserId($uid, $conditions);
    }

    public function getEventResponseListByEventId($event_id){
        return $this->EventResponses->getEventResponseListByEventId($event_id);
    }

    public function getEventResponseListByUserId($user_id, $limit=null){
        return $this->EventResponses->getEventResponseListByUserId($user_id, $limit);
    }

    public function getAllEventResponseListByUserId($user_id, $limit=null){ //未反応のイベントも含めたevent_response
        return $this->EventResponses->getEventResponseListByUserId($user_id, $limit);
    }

    public function getEventListByEventId($event_id_list, $event_display_order='ASC', $response_display_order='ASC'){
        if(count($event_id_list) <= 0){ //id listが空の場合は空配列を返す
            return [];
        }
        return $this->Events->getEventListByEventId($event_id_list, $event_display_order, $response_display_order);
    }

    public function getNeighberEvent($start_time, $type){
        $conditions = ["Events.deleted_at"=>0];
        $order = [];
        if($type == 'previous'){
            $conditions["Events.start_time <"] = $start_time;
            $order['Events.start_time'] = 'DESC';
        }
        if($type == 'next'){
            $conditions["Events.start_time >"] = $start_time;
            $order['Events.start_time'] = 'ASC';
        }

        return $this->Events->find("all", [
            "conditions" => $conditions
        ])->select('id')->order($order)->limit(1)->first();
    }

    /**
     * 現在時間
     *
     * @return Cake\I18n\FrozenTime
     */
    public function getNow(){
        return new FrozenTime('now');
    }

    /**
     * eventの配列を一覧で表示するために整形
     * @param array $event_list
     * @param int $uid
     * @return array 
     */
    public function getFormatEventDataList($event_list=[], $uid=null){
        if(count($event_list) <= 0){
            return [];
        }
        $formated_event_list = []; //整形後のevent
        $now_datetime = $this->getNow();
        foreach($event_list as $event_data){
            $formated_event_list[] = $this->formatEventData($event_data, $now_datetime, $uid);
        }
        return $formated_event_list;
    }

    /**
     * eventを一覧で表示するために整形
     * 
     * @param array $event_data
     * @param int $uid
     * @return array 
     */
    public function getFormatEventData($event_data, $uid=null){
        $now_datetime = $this->getNow();
        return $this->formatEventData($event_data, $now_datetime, $uid);
    }

    /**
     * eventを一覧で表示するために整形
     * 
     * @param array $event_data
     * @param Cake\I18n\FrozenTime $now_datetime
     * @param int $uid
     * @return array 
     */
    public function formatEventData($event_data, $now_datetime, $uid=null){
        //時刻の比較
        $event_data['event_state'] = $this->getEventState($now_datetime, $event_data["start_time"], $event_data["end_time"]);

        //ユーザの参加情報取出
        if ($uid){
            // dd($event_data["event_responses"][0]["responder_id"]);
            $resp = array_search($uid, array_column($event_data["event_responses"], 'responder_id'));
            $event_data['user_response_state'] = ($resp !== false) ? $event_data["event_responses"][$resp]->response_state: null;
        }

        //参加情報取出
        $event_data['event_responses'] = $this->categorizedEventResponseList($event_data["event_responses"]);

        return $event_data;
    }

    public function getEventState($now_datetime, $start_time, $end_time){
        if($now_datetime < $start_time){
            return 0;
        } elseif($now_datetime > $end_time) {
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
    public function categorizedEventResponseList($event_response_list){
        $categorized_event_response_list = [0=>[], 1=>[], 2=>[], 'null'=>[]];
        foreach($event_response_list as $event_response){
            if(isset($event_response->user)){
                $user_id = $event_response->user->id;
                $display_name = $event_response->user->display_name;
            } else {
                $user_id = $event_response["id"];
                $display_name = $event_response["display_name"];
            }

            if(isset($event_response->updated_at)){
                $time = $event_response->updated_at;
            } else {
                $time = $event_response["updated_at"];
            }

            $categorized_event_response_list[(is_null($event_response["response_state"]) ? 'null':$event_response["response_state"])][] =[
                "id"=>$user_id,
                "display_name"=>$display_name,
                "time"=>$time
            ];
        }
        return $categorized_event_response_list;
    }

    // public function executeSql($sql){
    //     return ConnectionManager::get('default')->execute($sql)->fetchAll('assoc');
    // }
}