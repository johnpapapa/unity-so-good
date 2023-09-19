<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CommentsFixture
 */
class CommentsFixture extends TestFixture
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
                'created_at' => 1695132324,
                'updated_at' => 1695132324,
                'deleted_at' => 1,
                'body' => 'Lorem ipsum dolor sit amet',
                'user_id' => 1,
                'event_id' => 1,
            ],
        ];
        parent::init();
    }
}
