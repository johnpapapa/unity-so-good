<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Location Entity
 *
 * @property int $id
 * @property string $display_name
 * @property string|null $address
 * @property int|null $usage_price
 * @property int|null $night_price
 *
 * @property \App\Model\Entity\Event[] $events
 */
class Location extends Entity
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
        'address' => true,
        'usage_price' => true,
        'night_price' => true,
        'events' => true,
    ];
}
