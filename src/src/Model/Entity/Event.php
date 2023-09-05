<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Event Entity
 *
 * @property int $id
 * @property \Cake\I18n\FrozenTime|null $created_at
 * @property \Cake\I18n\FrozenTime|null $updated_at
 * @property int $deleted_at
 * @property \Cake\I18n\FrozenTime $start_time
 * @property \Cake\I18n\FrozenTime $end_time
 * @property string|null $area
 * @property int $participants_limit
 * @property string|null $comment
 * @property int $organizer_id
 * @property int $location_id
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Location $location
 * @property \App\Model\Entity\EventResponse[] $event_responses
 */
class Event extends Entity
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
        'deleted_at' => true,
        'start_time' => true,
        'end_time' => true,
        'area' => true,
        'participants_limit' => true,
        'comment' => true,
        'organizer_id' => true,
        'location_id' => true,
        'user' => true,
        'location' => true,
        'event_responses' => true,
    ];
}
