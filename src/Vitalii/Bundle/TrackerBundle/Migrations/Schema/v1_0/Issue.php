<?php

namespace Vitalii\Bundle\TrackerBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class Issue implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable('tracker_issue');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('summary', 'string', ['length' => 255]);
        $table->addColumn('code', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('type', 'string', ['length' => 255]);
        $table->addColumn('priority', 'string', ['length' => 255]);
        $table->addColumn('resolution', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('status', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'issue_code_idx');
        $table->addIndex(['summary'], 'issue_summary_idx', []);
        $table->addIndex(['status'], 'issue_status_idx', []);
    }
}