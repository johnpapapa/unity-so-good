<?php
 
namespace App\Controller\Component;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\FrozenTime;
use Cake\Utility\Hash;
 
class eventComponent extends Component
{
    public function initialize(array $config): void
    {
        $this->Locations = FactoryLocator::get('Table')->get('Locations');
        $this->Events = FactoryLocator::get('Table')->get('Events');
        $this->Users = FactoryLocator::get('Table')->get('Users');
        $this->EventResponses = FactoryLocator::get('Table')->get('EventResponses');
    }
     
    public function getEvent() {
        return $this->Locations->find("all")->all();
    }

    public function getNow(){
        return new FrozenTime('+9 hour');
    }

    public function getFormatEventDataList($events, $uid=null){
        $events_formated = []; //整形後のevent
        $now_datetime = $this->getNow();
        foreach($events as $event){
            $events_formated[] = $this->formatEventData($event, $uid, $now_datetime);
        }
        return $events_formated;
    }

    public function getFormatEventData($event, $uid=null){
        $now_datetime = $this->getNow();
        return $this->formatEventData($event, $uid, $now_datetime);
    }

    public function formatEventData($event, $uid=null, $now_datetime){
        //時刻の比較
        $event['event_state'] = $this->getEventState($now_datetime, $event->start_time, $event->end_time);

        //ユーザの参加情報取出
        if ($uid){
            $resp = array_search($uid, array_column($event->event_responses, 'responder_id'));
            $event['user_response_state'] = ($resp !== false) ? $event->event_responses[$resp]->response_state: null;
        }

        //参加情報取出
        $event['event_responses'] = $this->getResponderList($event->event_responses);

        return $event;
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

    public function getResponderList($event_responses){
        $event_responder_list = [0=>[], 1=>[], 2=>[]];
        foreach($event_responses as $event_response){
            $event_responder_list[$event_response->response_state][] = [
                "name"=>$event_response->user->display_name, 
                "time"=>$event_response->updated_at,
            ];
        }
        return $event_responder_list;
    }

    public function getEventResponseListByEventId($event_id){
        $sql = <<<EOF
        SELECT users.id, users.display_name, er.response_state 
        FROM users 
        LEFT JOIN ( 
            SELECT event_responses.responder_id, event_responses.response_state 
            FROM event_responses 
            WHERE event_responses.event_id={$event_id} 
        ) as er 
        ON users.id = er.responder_id
        ORDER BY er.response_state DESC;
        EOF;

        $connection = ConnectionManager::get('default');
        $event_response_list = $connection->execute($sql)->fetchAll('assoc');
        return $event_response_list;
    }

    public function categorizedEventResponseList($event_response_list){
        $categorized_event_response_list = [0=>[], 1=>[], 2=>[], 'null'=>[]];
        foreach($event_response_list as $event_response){
            if(is_null($event_response["response_state"])){
                $categorized_event_response_list['null'][] = $event_response;
            } else {
                $categorized_event_response_list[$event_response["response_state"]][] = $event_response;
            }
        }
        return $categorized_event_response_list;
    }
    
    public function getEventResponseListByUserId($user_id, $limit=null){
        $limit_sql = '';
        if($limit){ $limit_sql = "LIMIT {$limit}";}
        $sql = <<<EOF
        SELECT 
            e.id,
            e.start_time,
            e.end_time,
            e.display_name,
            event_responses.created_at,
            event_responses.response_state
        FROM event_responses
        INNER JOIN (
            SELECT 
                events.id,
                events.start_time,
                events.end_time,
                locations.display_name
            FROM events
            INNER JOIN locations ON locations.id = events.location_id
            ORDER BY events.start_time DESC
            {$limit_sql}
        ) as e
        ON event_responses.event_id = e.id
        WHERE event_responses.responder_id = {$user_id}
        EOF;

        $connection = ConnectionManager::get('default');
        $event_response_list = $connection->execute($sql)->fetchAll('assoc');
        return $event_response_list;
    }

    public function getAllEventResponseListByUserId($user_id, $limit=null){ //未反応のイベントも含めたevent_response
        $limit_sql = '';
        if($limit){ $limit_sql = "LIMIT {$limit}";}
        $sql = <<<EOF
        SELECT 
            e.id,
            e.start_time,
            e.end_time,
            e.display_name,
            er.created_at,
            er.response_state
        FROM (
            SELECT 
                events.id,
                events.start_time,
                events.end_time,
                locations.display_name
            FROM events
            INNER JOIN locations ON locations.id=events.location_id
            ORDER BY events.start_time DESC
            {$limit_sql}
        ) as e
        LEFT JOIN (
            SELECT 
                event_responses.created_at,
                event_responses.response_state,
                event_responses.event_id
            FROM event_responses
            WHERE event_responses.responder_id={$user_id}
        ) as er
        ON e.id=er.event_id
        EOF;
        $connection = ConnectionManager::get('default');
        $event_response_list = $connection->execute($sql)->fetchAll('assoc');
        return $event_response_list;
    }
}