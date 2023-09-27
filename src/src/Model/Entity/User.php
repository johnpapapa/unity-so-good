<?php
declare(strict_types=1);

namespace App\Model\Entity;
use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string $display_name
 * @property string|null $user_id
 * @property string|null $line_user_id
 * @property string|null $password
 * @property string|null $remember_token
 * @property \Cake\I18n\FrozenTime|null $created_at
 * @property \Cake\I18n\FrozenTime|null $updated_at
 * @property bool $deleted_at
 * @property \App\Model\Entity\Event[] $events
 * @property \App\Model\Entity\Comment[] $comments
 */
class User extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        'display_name' => true,
        'user_id' => true,
        'line_user_id' => true,
        'password' => true,
        'remember_token' => true,
        'created_at' => true,
        'updated_at' => true,
        'deleted_at' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected $_hidden = [
        'password',
    ];

    protected function _setPassword($value)
    {
        $hasher = new DefaultPasswordHasher();
        if (strlen($value) > 0) {
            return $hasher->hash($value);    
        }
    }
}
