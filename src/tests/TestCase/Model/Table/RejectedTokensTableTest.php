<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RejectedTokensTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RejectedTokensTable Test Case
 */
class RejectedTokensTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RejectedTokensTable
     */
    protected $RejectedTokens;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.RejectedTokens',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('RejectedTokens') ? [] : ['className' => RejectedTokensTable::class];
        $this->RejectedTokens = $this->getTableLocator()->get('RejectedTokens', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->RejectedTokens);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\RejectedTokensTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
