<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RejectedTokens Model
 *
 * @method \App\Model\Entity\RejectedToken newEmptyEntity()
 * @method \App\Model\Entity\RejectedToken newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\RejectedToken[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RejectedToken get($primaryKey, $options = [])
 * @method \App\Model\Entity\RejectedToken findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\RejectedToken patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RejectedToken[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RejectedToken|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RejectedToken saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RejectedToken[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RejectedToken[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\RejectedToken[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RejectedToken[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class RejectedTokensTable extends Table
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

        $this->setTable('rejected_tokens');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
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
            ->requirePresence('line_user_id', 'create')
            ->notEmptyString('line_user_id');

        return $validator;
    }
}
