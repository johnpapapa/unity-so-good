<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * EventResponseLogsFixture
 */
class EventResponseLogsFixture extends TestFixture
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
                'event_id' => 1,
                'responder_id' => 1,
                'response_state' => 1,
                'created_at' => 1710230900,
            ],
        ];
        parent::init();
    }
}
