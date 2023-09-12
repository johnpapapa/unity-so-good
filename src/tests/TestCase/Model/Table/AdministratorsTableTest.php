<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AdministratorsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AdministratorsTable Test Case
 */
class AdministratorsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AdministratorsTable
     */
    protected $Administrators;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Administrators',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Administrators') ? [] : ['className' => AdministratorsTable::class];
        $this->Administrators = $this->getTableLocator()->get('Administrators', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Administrators);

        parent::tearDown();
    }
}
