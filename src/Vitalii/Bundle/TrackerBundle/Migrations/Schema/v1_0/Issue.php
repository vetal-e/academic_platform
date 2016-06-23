<?php

namespace Vitalii\Bundle\TrackerBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtension;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtensionAwareInterface;

class Issue implements
    Migration,
    ExtendExtensionAwareInterface,
    NoteExtensionAwareInterface,
    ActivityExtensionAwareInterface
{
    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * @var NoteExtension
     */
    protected $noteExtension;

    /**
     * @var ActivityExtension
     */
    protected $activityExtension;

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setNoteExtension(NoteExtension $noteExtension)
    {
        $this->noteExtension = $noteExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createTrackerIssueTable($schema);
        $this->createTrackerIssueCodesCacheTable($schema);

        self::addActivityAssociations($schema, $this->activityExtension);
    }

    /**
     * @param Schema $schema
     */
    protected function createTrackerIssueTable(Schema $schema)
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

        $table = $this->addEnumFields($schema, $table);

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

        $this->noteExtension->addNoteAssociation($schema, $table->getName());

        $this->extendExtension->addManyToManyRelation(
            $schema,
            'tracker_issue', // owning side table
            'collaborators', // owning side field name
            'oro_user', // target side table
            ['username'], // column names are used to show a title of related entity
            ['username'], // column names are used to show detailed info about related entity
            ['username'], // Column names are used to show related entity in a grid
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'view' => [
                    'is_displayable' => false,
                ],
            ]
        );
    }

    /**
     * @param Schema $schema
     * @param Table $table
     * @return Table
     */
    private function addEnumFields(Schema $schema, Table $table)
    {
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
                'form' => [
                    'is_enabled' => false,
                ],
                'view' => [
                    'is_displayable' => false,
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
                'view' => [
                    'is_displayable' => false,
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
                'form' => [
                    'is_enabled' => false,
                ],
                'view' => [
                    'is_displayable' => false,
                ],
            ]
        );

        return $table;
    }

    /**
     * @param Schema $schema
     */
    protected function createTrackerIssueCodesCacheTable(Schema $schema)
    {
        $table = $schema->createTable('tracker_issue_codes_cache');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('code', 'string', ['length' => 255]);
        $table->addColumn('number', 'integer', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'UNIQ_98E926BB77153098');
    }

    /**
     * @param Schema $schema
     * @param ActivityExtension $activityExtension
     */
    public static function addActivityAssociations(Schema $schema, ActivityExtension $activityExtension)
    {
        $activityExtension->addActivityAssociation($schema, 'oro_email', 'tracker_issue', true);
    }
}
