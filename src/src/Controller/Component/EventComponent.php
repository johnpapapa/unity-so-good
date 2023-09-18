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
 
class eventComponent extends Component
{
    public function initialize(array $config): void
    {
        $this->Locations = FactoryLocator::get('Table')->get('Locations');
        $this->Events = FactoryLocator::get('Table')->get('Events');
        $this->Users = FactoryLocator::get('Table')->get('Users');
        $this->EventResponses = FactoryLocator::get('Table')->get('EventResponses');
    }

    public function getEventIds($uid, $is_unrespond=false, $start_order='ASC'){
        return $this->Events->getEventIds($uid, $is_unrespond, $start_order);
    }

    public function getEventResponseListByEventId($event_id){
        return $this->Events->getEventResponseListByEventId($event_id);
    }

    public function getEventResponseListByUserId($user_id, $limit=null){
        return $this->EventResponses->getEventResponseListByUserId($user_id, $limit);
    }

    public function getAllEventResponseListByUserId($user_id, $limit=null){ //未反応のイベントも含めたevent_response
        $this->EventResponses->getEventResponseListByUserId($user_id, $limit);
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
    public function getFormatEventDataList($event_list, $uid=null){
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
            $categorized_event_response_list[(is_null($event_response["response_state"]) ? 'null':$event_response["response_state"])][] =[
                "name"=>$event_response->user->display_name, 
                "time"=>$event_response->updated_at,
            ];
        }
        return $categorized_event_response_list;
    }

    // public function executeSql($sql){
    //     return ConnectionManager::get('default')->execute($sql)->fetchAll('assoc');
    // }
}