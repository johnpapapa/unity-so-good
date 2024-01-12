<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\Log\Log;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/4/en/controllers.html#the-app-controller
 * @property \App\Controller\Component\loginComponent $Login
 *
 *
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication'); //認証ロジック
        $this->loadComponent('Login');
    }

    public function beforeFilter(EventInterface $event)
    {
        //ログイン情報をページ表示する前に取得//API経由のアクセスではこれらのチェックをスルー
        $prefix = $this->getRequest()->getParam('prefix');
        if($prefix == 'Api'){    
            return;        
        }
        $controller = $this->getRequest()->getParam('controller');
        $action = $this->getRequest()->getParam('action');
        if (!($controller == 'Users' && $action == 'login')) {
            $login_user_data = $this->Login->getLoginUserData();
            if (!$login_user_data) {
                $login_user_data = false;
            }
        } else {
            $login_user_data = false;
        }

        $this->set('current_user', $login_user_data);
    }

    public function setLog($message)
    {
        Log::write('debug', print_r($message, true));
    }
}
