<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Datasource\ConnectionManager;

/**
 * Events Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\LocationsTable&\Cake\ORM\Association\BelongsTo $Locations
 * @property \App\Model\Table\EventResponsesTable&\Cake\ORM\Association\HasMany $EventResponses
 *
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
     * 指定したuserによるevent_responses.stateに応じたeventの取得
     *
     * @param int $uid users.id
     * @param bool $is_unrespond 未表明イベントを取得するか
     * @param string $start_order events.start_timeをキーとした並び順
     * @return array|false eventResponses.*
     */
    public function getEventIds($uid, $is_unrespond=false, $start_order='ASC'){
        $event_where = ($is_unrespond) ? ' WHERE ISNULL(er.responder_id)':'';
        $event_join_type = ($is_unrespond) ? 'LEFT JOIN':'JOIN';
        $sql = <<<EOF
            SELECT e.id
            FROM ( 
                SELECT e.id, er.responder_id, e.start_time
                FROM (
                    SELECT events.id, events.start_time
                    FROM events
                    WHERE events.deleted_at=0 AND events.start_time between cast(CURRENT_DATE as datetime) and cast(CURRENT_DATE + interval 1 year as datetime) 
                ) as e
                {$event_join_type} ( 
                    SELECT event_responses.responder_id, event_responses.event_id 
                    FROM event_responses
                    WHERE event_responses.responder_id={$uid}
                ) as er
                ON (e.id = er.event_id)
                {$event_where}
            ) as e
            ORDER BY e.start_time {$start_order};
        EOF;

        return $this->executeSql($sql);
    }

    /**
     * 指定したsql_statementを実行
     *
     * @param string $sql_statement SQL文章
     * @return array|false eventResponses.*
     */
    public function executeSql($sql_statement){
        return ConnectionManager::get('default')->execute($sql_statement)->fetchAll('assoc');
    }
}
