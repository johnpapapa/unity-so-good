<?php
 
namespace App\Controller\Component;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\FrozenTime;
 
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

    public function getFormatEventDataList($events, $uid){
        $events_formated = []; //整形後のevent
        $now_datetime = $this->getNow();
        foreach($events as $event){
            $events_formated[] = $this->formatEventData($event, $uid, $now_datetime);
        }
        return $events_formated;
    }

    public function getFormatEventData($event, $uid){
        $now_datetime = $this->getNow();
        return $this->formatEventData($event, $uid, $now_datetime);
    }

    public function formatEventData($event, $uid, $now_datetime){
        //時刻の比較
        if($now_datetime < $event->start_time){
            $event['event_state'] = 0;
        } elseif($now_datetime > $event->end_time) {
            $event['event_state'] = 2;
        } else {
            $event['event_state'] = 1;
        }

        //ユーザの参加情報取出
        if ($uid){
            $resp = array_search($uid, array_column($event->event_responses, 'responder_id'));
            $event['user_response_state'] = ($resp !== false) ? $event->event_responses[$resp]->response_state: null;
        }

        //参加情報取出
        $event_responder_list = [0=>[], 1=>[], 2=>[]];
        foreach($event->event_responses as $event_response){
            $event_responder_list[$event_response->response_state][] = ["name"=>$event_response->user->display_name, "time"=>$event_response->updated_at];
        }
        $event['event_responses'] = $event_responder_list;

        return $event;
    }
}