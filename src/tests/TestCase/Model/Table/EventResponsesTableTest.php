<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EventResponsesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\EventResponsesTable Test Case
 */
class EventResponsesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\EventResponsesTable
     */
    protected $EventResponses;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.EventResponses',
        'app.Users',
        'app.Events',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('EventResponses') ? [] : ['className' => EventResponsesTable::class];
        $this->EventResponses = $this->getTableLocator()->get('EventResponses', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->EventResponses);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\EventResponsesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\EventResponsesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
