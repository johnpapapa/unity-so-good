<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateEventResponsesLogs extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('event_response_logs');
        // $table->addColumn('id', 'integer', [
        //     'autoIncrement' => true,
        //     'limit' => 11,
        //     'null' => false,
        //     'signed' => false,
        // ])
        // ->addPrimaryKey(['id']);
        $table->addColumn('event_id', 'integer', [
            'limit' => 11,
            'null' => false,
            'signed' => false,
        ])->addIndex(
            [
                'event_id',
            ],
            [
                'name' => 'event_response_logs_event_id_foreign',
            ]
        );
        $table->addColumn('responder_id', 'tinyinteger', [
            'limit' => 3,
            'null' => false,
            'signed' => false,
        ])->addIndex(
            [
                'responder_id',
            ],
            [
                'name' => 'event_response_log_responder_id_foreign',
            ]
        );
        $table->addColumn('response_state', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => false,
        ]);
        $table->addColumn('created_at', 'timestamp', [
            'default' => null,
            'null' => false,
        ]);
        $table->create();
    }
}
