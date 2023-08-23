<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * EventResponsesFixture
 */
class EventResponsesFixture extends TestFixture
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
                'created_at' => 1692340865,
                'updated_at' => 1692340865,
                'response_state' => 1,
                'responder_id' => 1,
                'event_id' => 1,
            ],
        ];
        parent::init();
    }
}
