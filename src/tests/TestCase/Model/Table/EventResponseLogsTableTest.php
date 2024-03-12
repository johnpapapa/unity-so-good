<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EventResponseLogsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\EventResponseLogsTable Test Case
 */
class EventResponseLogsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\EventResponseLogsTable
     */
    protected $EventResponseLogs;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.EventResponseLogs',
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
        $config = $this->getTableLocator()->exists('EventResponseLogs') ? [] : ['className' => EventResponseLogsTable::class];
        $this->EventResponseLogs = $this->getTableLocator()->get('EventResponseLogs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->EventResponseLogs);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\EventResponseLogsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\EventResponseLogsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
