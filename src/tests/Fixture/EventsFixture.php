<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * EventsFixture
 */
class EventsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'created_at' => 1692340845,
                'updated_at' => 1692340845,
                'deleted_at' => 1692340845,
                'date' => '2023-08-18',
                'start_time' => '06:40:45',
                'end_time' => '06:40:45',
                'area' => 'Lorem ipsum dolor sit amet',
                'participants_limit' => 1,
                'comment' => 'Lorem ipsum dolor sit amet',
                'organizer_id' => 1,
                'location_id' => 1,
            ],
        ];
        parent::init();
    }
}
