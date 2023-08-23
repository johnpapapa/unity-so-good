<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * EventResponse Entity
 *
 * @property int $id
 * @property \Cake\I18n\FrozenTime|null $created_at
 * @property \Cake\I18n\FrozenTime|null $updated_at
 * @property int $response_state
 * @property int $responder_id
 * @property int $event_id
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Event $event
 */
class EventResponse extends Entity
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
        'created_at' => true,
        'updated_at' => true,
        'response_state' => true,
        'responder_id' => true,
        'event_id' => true,
        'user' => true,
        'event' => true,
    ];
}
