<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Informations Controller
 *
 * @property \App\Model\Table\InformationsTable $Informations
 */
class InformationsController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['about']); //認証不要のアクション
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function about()
    {
        $is_admin = $this->Login->isAdministrator();

        $information_data = $this->Informations->find('all')->first();
        if (!$information_data) {
            $information_data = $this->Informations->newEmptyEntity();
        }
        $information_data['about'] = $information_data['about'];
        $information_data['rule'] = $information_data['rule'];

        $this->set(compact('information_data', 'is_admin'));
    }
}
