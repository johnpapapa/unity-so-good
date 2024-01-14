<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\Http\Exception\NotFoundException;

use function PHPUnit\Framework\isNull;

/**
 * Event Controller
 *
 * @method \App\Model\Entity\Event[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class EventController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Event');
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['list', 'detail']); //認証不要のアクション
        header('Access-Control-Allow-Origin: *'); //cors対策
    }

    public function list(){
        $this->request->allowMethod(['get']);
        $events = $this->Event->getEventListForApi();


        $this->set('events', $events);
        $this->viewBuilder()->setClassName('Json')
        ->setOption('serialize', ['events'])
        ->setOption('jsonOptions', JSON_FORCE_OBJECT);
    }

    public function detail($id=null){
        $this->request->allowMethod(['get']);
        $response = [];
        // dd($id);
        
        if(is_null($id)){
            $this->set('errors', ["status"=>"404","detail"=>"ID field is empty."]);
            $this->response = $this->response->withStatus(400);
        } else {
            $data = $this->Event->getEventByEventIdForApi($id);
            $this->set('responses', $data);
        }        
        $this->viewBuilder()->setClassName('Json')
        ->setOption('serialize', ['responses', 'errors'])
        ->setOption('jsonOptions', JSON_FORCE_OBJECT);
    }
}