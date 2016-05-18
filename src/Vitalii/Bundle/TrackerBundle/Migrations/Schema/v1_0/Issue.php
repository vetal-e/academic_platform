<?php

namespace Vitalii\Bundle\TrackerBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class Issue implements Migration, ExtendExtensionAwareInterface
{
    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable('tracker_issue');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('summary', 'string', ['length' => 255]);
        $table->addColumn('code', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('assignee_id', 'integer', ['notnull' => false]);
        $table->addColumn('reporter_id', 'integer', ['notnull' => false]);
        $table->addColumn('parent_issue_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'type',
            'issue_type',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => [
                    'is_visible' => false,
                ],
            ]
        );

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'priority',
            'issue_priority',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => [
                    'is_visible' => false,
                ],
            ]
        );

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'status',
            'issue_status',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => [
                    'is_visible' => false,
                ],
            ]
        );

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'resolution',
            'issue_resolution',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => [
                    'is_visible' => false,
                ],
            ]
        );

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'issue_code_idx');
        $table->addIndex(['summary'], 'issue_summary_idx', []);
        $table->addIndex(['reporter_id'], 'issue_reporter_idx', []);
        $table->addIndex(['assignee_id'], 'issue_assignee_idx', []);
        $table->addIndex(['parent_issue_id'], 'parent_issue_idx', []);
        $table->addIndex(['organization_id'], 'issue_organization_idx', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['assignee_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['reporter_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('tracker_issue'),
            ['parent_issue_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}