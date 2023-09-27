<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Datasource\ConnectionManager;

/**
 * EventResponses Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\EventsTable&\Cake\ORM\Association\BelongsTo $Events
 *
 * @method \App\Model\Entity\EventResponse newEmptyEntity()
 * @method \App\Model\Entity\EventResponse newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventResponse[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EventResponse get($primaryKey, $options = [])
 * @method \App\Model\Entity\EventResponse findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EventResponse patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventResponse[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EventResponse|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventResponse saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventResponse[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventResponse[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventResponse[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventResponse[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EventResponsesTable extends Table
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

        $this->setTable('event_responses');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'responder_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Events', [
            'foreignKey' => 'event_id',
            'joinType' => 'INNER',
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
            ->integer('response_state')
            ->requirePresence('response_state', 'create')
            ->notEmptyString('response_state');

        $validator
            ->notEmptyString('responder_id');

        $validator
            ->notEmptyString('event_id');

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
        $rules->add($rules->existsIn('responder_id', 'Users'), ['errorField' => 'responder_id']);
        $rules->add($rules->existsIn('event_id', 'Events'), ['errorField' => 'event_id']);

        return $rules;
    }

    /**
     * 指定したeventに紐づくevent_responsesの取得
     *
     * @param int $event_id events.id
     * @return array|false eventResponses.*
     */
    public function getEventResponseListByEventId($event_id){
        $sql = <<<EOF
        SELECT users.id, users.display_name, er.response_state , er.updated_at
        FROM users 
        LEFT JOIN ( 
            SELECT event_responses.responder_id, event_responses.response_state, event_responses.updated_at
            FROM event_responses 
            WHERE event_responses.event_id={$event_id} 
        ) as er 
        ON users.id = er.responder_id
        ORDER BY er.response_state DESC;
        EOF;
        return $this->executeSql($sql);
    }

    /**
     * 指定したuserに紐づく全てのevent_responsesの取得
     *
     * @param int $user_id users.id
     * @return array|false eventResponses.*
     */
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

        return $this->executeSql($sql);
    }

    /**
     * 指定したuserに紐づくevent_responsesの取得
     *
     * @param int $event_id events.id
     * @param int $event_id events.id
     * @return array|false eventResponses.*
     */
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

        return $this->executeSql($sql);
    }

    public function executeSql($sql){
        return ConnectionManager::get('default')->execute($sql)->fetchAll('assoc');
    }
}
