<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use Cake\Collection\Collection;

/**
 * Events Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\LocationsTable&\Cake\ORM\Association\BelongsTo $Locations
 * @property \App\Model\Table\EventResponsesTable&\Cake\ORM\Association\HasMany $EventResponses
 * @method \App\Model\Entity\Event newEmptyEntity()
 * @method \App\Model\Entity\Event newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Event[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Event get($primaryKey, $options = [])
 * @method \App\Model\Entity\Event findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Event patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Event[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Event|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Event saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Event[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Event[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Event[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Event[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 * @property \App\Model\Table\CommentsTable&\Cake\ORM\Association\HasMany $Comments
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EventsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('events');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'organizer_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Locations', [
            'foreignKey' => 'location_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('EventResponses', [
            'foreignKey' => 'event_id',
        ]);
        $this->hasMany('EventResponseLogs', [
            'foreignKey' => 'event_id',
        ]);
        $this->hasMany('Comments', [
            'foreignKey' => 'event_id',
        ]);

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created_at' => 'new',
                    'updated_at' => 'always',
                ],
            ],
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->dateTime('created_at')
            ->allowEmptyDateTime('created_at');

        $validator
            ->dateTime('updated_at')
            ->allowEmptyDateTime('updated_at');

        $validator
            ->integer('deleted_at')
            ->notEmptyString('deleted_at');

        $validator
            ->datetime('start_time')
            ->requirePresence('start_time', 'create')
            ->notEmptyTime('start_time');

        $validator
            ->datetime('end_time')
            ->requirePresence('end_time', 'create')
            ->notEmptyTime('end_time');

        $validator
            ->scalar('area')
            ->maxLength('area', 255)
            ->allowEmptyString('area');

        $validator
            ->integer('participants_limit')
            ->notEmptyString('participants_limit');

        $validator
            ->scalar('comment')
            ->maxLength('comment', 255)
            ->allowEmptyString('comment');

        $validator
            ->notEmptyString('organizer_id');

        $validator
            ->notEmptyString('location_id');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn('organizer_id', 'Users'), ['errorField' => 'organizer_id']);
        $rules->add($rules->existsIn('location_id', 'Locations'), ['errorField' => 'location_id']);

        return $rules;
    }

    /**
     * 指定したuserの反応済みeventIdの取得
     * ### Usage
     * ```
     * "start_order"=>"ASC",
     * "is_contain_held_event"=>false
     * ```
     *
     * @param int $uid users.id
     * @param array<string, mixed> $conditions
     * @return array|false eventResponses.id
     */
    public function getParticipateEventIdListByUserId($uid, $conditions = [])
    {
        $start_order = 'ASC';
        if (isset($conditions['start_order'])) {
            $start_order = $conditions['start_order'];
        }
        $event_start_between_conditions = 'events.start_time between cast(NOW() as datetime) and cast(CURRENT_DATE + interval 1 year as datetime)';
        if (isset($conditions['is_contain_held_event']) && $conditions['is_contain_held_event'] == true) {
            $event_start_between_conditions = $event_start_between_conditions . ' OR events.start_time < cast(CURRENT_DATE as datetime)';
        }

        $sql_statement = <<<EOF
        SELECT e.id 
        FROM 
        (
            SELECT e.id, er.responder_id, e.start_time
            FROM
            (
                SELECT events.id, events.start_time
                FROM events
                WHERE events.deleted_at=0 AND {$event_start_between_conditions}
            ) as e
            JOIN (
                SELECT event_responses.responder_id, event_responses.event_id
                FROM event_responses 
                WHERE event_responses.responder_id = {$uid} AND (event_responses.response_state=0 OR event_responses.response_state=1)
            ) as er ON (e.id = er.event_id)
        ) as e
        ORDER BY e.start_time {$start_order};
        EOF;

        return $this->executeSql($sql_statement);
    }

    /**
     * 指定したuserの未反応eventIdの取得
     * ### Usage
     * ```
     * "start_order"=>"ASC",
     * "is_contain_held_event"=>false
     * ```
     *
     * @param int $uid users.id
     * @param array<string, mixed> $conditions
     * @return array|false eventResponses.id
     */
    public function getUnrespondedEventIdListByUserId($uid, $conditions = [])
    {
        $start_order = 'ASC';
        if (isset($conditions['start_order'])) {
            $start_order = $conditions['start_order'];
        }
        $event_start_between_conditions = 'events.start_time between cast(NOW() as datetime) and cast(CURRENT_DATE + interval 1 year as datetime)';
        if (isset($conditions['is_contain_held_event']) && $conditions['is_contain_held_event'] == true) {
            $event_start_between_conditions = $event_start_between_conditions . ' OR events.start_time < cast(CURRENT_DATE as datetime)';
        }

        $sql_statement = <<<EOF
        SELECT e.id 
        FROM 
        (
            SELECT e.id, er.responder_id, e.start_time 
            FROM 
            (
                SELECT events.id, events.start_time 
                FROM events 
                WHERE events.deleted_at=0 AND {$event_start_between_conditions}
            ) as e
            LEFT JOIN (
                SELECT
                event_responses.responder_id, event_responses.event_id
                FROM event_responses
                WHERE event_responses.responder_id = {$uid}
            ) as er ON (e.id = er.event_id)
            WHERE ISNULL(er.responder_id)
        ) as e
        ORDER BY e.start_time {$start_order};
        EOF;

        return $this->executeSql($sql_statement);
    }

    /**
     * 指定したorganizer_idのeventを取得
     *
     * @param int $organizer_user_id users.id
     * @param bool $contain_deleted_event 削除済のイベントを含める
     * @param bool $contain_held_event 開催済みのイベントを含める
     * @param bool $contain_not_held_event 未開催のイベントを含める
     * @param bool $is_disp_comment コメントの詳細を表示する
     * @param bool $is_disp_response 反応の詳細を表示する
     * @return array|collection|false eventResponses.*
     */
    public function getEventList(
        $organizer_user_id = false,
        $contain_deleted_event = false,
        $contain_held_event = false,
        $contain_not_held_event = false,
        $is_disp_comment = true,
        $is_disp_response = true,
        $is_to_array = true
    ) {
        $Events = TableRegistry::getTableLocator()->get('Events');

        $conditions = [];
        if (!$contain_deleted_event) {
            $conditions['AND']['Events.deleted_at'] = 0;
        }
        if ($contain_held_event) {
            $conditions['OR']['Events.start_time <='] = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . 'now'));
        }
        if ($contain_not_held_event) {
            $conditions['OR']['Events.end_time >='] = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . 'now'));
        }
        if ($organizer_user_id) {
            $conditions['Events.organizer_id IN'] = $organizer_user_id;
        }


        $contain_arr = ['Locations'];
        if($is_disp_comment){
            $contain_arr['Comments'] = function (Query $query) {
                return $query
                    ->contain('Users')
                    ->where(['Comments.deleted_at' => 0])
                    ->order(['Comments.updated_at' => 'DESC']);
            };
        }

        if($is_disp_response){ //反応の詳細を表示する場合ユーザー名表示
            $contain_arr['EventResponses'] = function (Query $query) {
                return $query
                    ->contain([
                        'Users' => function (Query $uquery){
                            return $uquery->where(['Users.deleted_at'=>0]);
                        }
                    ])
                    ->order([
                        'EventResponses.updated_at' => 'ASC',
                        'EventResponses.response_state' => 'DESC',
                    ]);
            };
        } else { //反応の詳細を表示しない場合ユーザー名非表示
            $contain_arr['EventResponses'] = function (Query $query) {
                return $query
                    ->select(["EventResponses.event_id", "EventResponses.response_state"])
                    ->contain([
                        'Users' => function (Query $uquery){
                            return $uquery->where(['Users.deleted_at'=>0]);
                        }
                    ]);
            };
        }

        $events_query = $Events->find('all', ['conditions' => $conditions]);
        $events_query = $events_query->contain($contain_arr)
        ->order(['Events.start_time' => 'ASC'])
        ->limit(Configure::read('event_item_limit'));
        if ($is_to_array) {
            return $events_query->all()->toArray();
        }
        return new Collection($events_query->all());
    }

    public function getDeletedEventList($organizer_user_id=false){
        if(!$organizer_user_id){
            return false;
        }

        $events_query = $this->find('all', ['conditions' => [
                'Events.organizer_id'=>$organizer_user_id, 
                'Events.deleted_at'=>1
            ]
        ]);
        $events_query = $events_query
            ->contain([
                'Locations',
                'Comments' => function (Query $query) {
                    return $query
                        ->contain('Users')
                        ->where(['Comments.deleted_at' => 0])
                        ->order(['Comments.updated_at' => 'DESC']);
                },
                'EventResponses' => function (Query $query) {
                    return $query
                        ->contain([
                            'Users' => function (Query $uquery){
                                return $uquery->where(['Users.deleted_at'=>0]);
                            }
                        ])
                        ->order([
                            'EventResponses.updated_at' => 'ASC',
                            'EventResponses.response_state' => 'DESC',
                        ]);
                },
            ])
        ->order(['Events.start_time' => 'ASC'])
        ->limit(Configure::read('event_item_limit'));
        $events = $events_query->all()->toArray();

        return $events;
    }

    public function getArchivedEventList($organizer_user_id=false){
        if(!$organizer_user_id){
            return false;
        }

        $events_query = $this->find('all', ['conditions' => [
                'Events.organizer_id'=>$organizer_user_id, 
                'Events.end_time >='=>date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . 'now')),
                'Events.deleted_at'=>0,
            ]
        ]);
        $events_query = $events_query
            ->contain([
                'Locations',
                'Comments' => function (Query $query) {
                    return $query
                        ->contain('Users')
                        ->where(['Comments.deleted_at' => 0])
                        ->order(['Comments.updated_at' => 'DESC']);
                },
                'EventResponses' => function (Query $query) {
                    return $query
                        ->contain([
                            'Users' => function (Query $uquery){
                                return $uquery->where(['Users.deleted_at'=>0]);
                            }
                        ])
                        ->order([
                            'EventResponses.updated_at' => 'ASC',
                            'EventResponses.response_state' => 'DESC',
                        ]);
                },
            ])
        ->order(['Events.start_time' => 'ASC'])
        ->limit(Configure::read('event_item_limit'));
        $events = $events_query->all()->toArray();

        return $events;
    }

    /**
     * 指定したevent_idでeventの取得
     */
    public function getEventByEventId(
        $event_id, 
        $is_disp_comment = true,
        $is_disp_response = true
    )
    {
        $contain_arr = ['Locations'];
        if($is_disp_comment){
            $contain_arr['Comments'] = function (Query $query) {
                return $query
                    ->contain('Users')
                    ->where(['Comments.deleted_at' => 0])
                    ->order(['Comments.updated_at' => 'DESC']);
            };
        }

        if($is_disp_response){ //反応の詳細を表示する場合ユーザー名表示
            $contain_arr['EventResponses'] = function (Query $query) {
                return $query
                    ->contain([
                        'Users' => function (Query $uquery){
                            return $uquery->where(['Users.deleted_at'=>0]);
                        }
                    ])
                    ->order([
                        'EventResponses.updated_at' => 'ASC',
                        'EventResponses.response_state' => 'DESC',
                    ]);
            };
        } else { //反応の詳細を表示しない場合ユーザー名非表示
            $contain_arr['EventResponses'] = function (Query $query) {
                return $query
                    ->select(["EventResponses.event_id", "EventResponses.response_state"])
                    ->contain([
                        'Users' => function (Query $uquery){
                            return $uquery->where(['Users.deleted_at'=>0]);
                        }
                    ]);
            };
        }

        $events_query = $this->find('all', [
            'conditions' => ['Events.id' => $event_id],
        ])
        ->contain($contain_arr);

        return $events_query->first();
    }

    /**
     * 指定したevent_idのリストでeventの取得
     *
     * @param array $event_id_list 取得するeventのidの配列
     * @param string $event_display_order 開始時刻によるイベント表示順
     * @param string $response_display_order 反応時刻による反応表示順
     * @return array eventの配列
     */
    public function getEventListByEventId($event_id_list = [], $event_display_order = 'ASC', $response_display_order = 'ASC')
    {
        $Locations = TableRegistry::getTableLocator()->get('Locations');
        $Events = TableRegistry::getTableLocator()->get('Events');
        $conditions = [
            'Events.id IN' => $event_id_list,
        ];

        $events_query = $Events->find('all', ['conditions' => $conditions]);
        $events_query = $events_query
            ->contain([
                'Locations',
                'Comments' => function (Query $query) {
                    return $query
                        ->contain('Users')
                        ->where(['Comments.deleted_at' => 0])
                        ->order(['Comments.updated_at' => 'DESC']);
                },

                'EventResponses' => function (Query $query) {
                    return $query
                        ->contain([
                            'Users' => function (Query $uquery){
                                return $uquery->where(['Users.deleted_at'=>0]);
                            }
                        ])
                        ->order([
                            'EventResponses.updated_at' => 'ASC',
                            'EventResponses.response_state' => 'DESC',
                        ]);
                },
            ])
            ->select($Events)
            ->select($Locations)
            ->order(['Events.start_time' => $event_display_order]) //Eventが表示される順番
            ->limit(Configure::read('event_item_limit'));
        $events = $events_query->all()->toArray();

        return $events;
    }

    public function getNeighberEventId($start_time, $type)
    {
        $conditions = ['Events.deleted_at' => 0];
        $order = [];
        if ($type == 'previous') {
            $conditions['Events.start_time <'] = $start_time;
            $order['Events.start_time'] = 'DESC';
        }
        if ($type == 'next') {
            $conditions['Events.start_time >'] = $start_time;
            $order['Events.start_time'] = 'ASC';
        }

        $event_data = $this->find('all', [
            'conditions' => $conditions,
        ])->select('id')->order($order)->limit(1)->first();

        return $event_data;
    }

    /**
     * 指定したsql_statementを実行
     *
     * @param string $sql_statement SQL文章
     * @return array|false eventResponses.*
     */
    public function executeSql($sql_statement)
    {
        return ConnectionManager::get('default')->execute($sql_statement)->fetchAll('assoc');
    }
}
