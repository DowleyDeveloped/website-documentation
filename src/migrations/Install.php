<?php
namespace dowleydeveloped\websitedocumentation\migrations;

use dowleydeveloped\websitedocumentation\models\Navigation;

use Craft;
use craft\db\Migration;
use craft\db\Table;
use craft\helpers\MigrationHelper;

class Install extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();

        return true;
    }

    public function safeDown(): bool
    {
        $this->dropProjectConfig();
        $this->dropForeignKeys();
        $this->dropTables();

        return true;
    }

    public function createTables(): void
    {
		// Navigation Items Table
        $this->archiveTableIfExists('{{%documentation_navigation_elements}}');
        $this->createTable('{{%documentation_navigation_elements}}', [
            'id' => $this->integer()->notNull(),
            'elementId' => $this->integer(),
            'menuId' => $this->integer()->notNull(),
            'parentId' => $this->integer(),
            'url' => $this->string(255),
            'type' => $this->string(255),
            'deletedWithNav' => $this->boolean()->null(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);

		// Navigations Table
        $this->archiveTableIfExists('{{%documentation_navigations}}');
        $this->createTable('{{%documentation_navigations}}', [
            'id' => $this->primaryKey(),
			'structureId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'propagationMethod' => $this->string()->defaultValue(Navigation::PROPAGATION_METHOD_ALL)->notNull(),
			'siteId' => $this->integer(),
            'fieldLayoutId' => $this->integer(),
            'defaultPlacement' => $this->enum('defaultPlacement', [Navigation::DEFAULT_PLACEMENT_BEGINNING, Navigation::DEFAULT_PLACEMENT_END])->defaultValue('end')->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->null(),
            'uid' => $this->uid(),
        ]);

		// Guide Entries
		$this->archiveTableIfExists('{{%documentation_guide_entries}}');
		$this->createTable('{{%documentation_guide_entries}}', [
			'id' => $this->integer()->notNull(),
			'structureId' => $this->integer(),
			'parentId' => $this->integer(),
			'fieldId' => $this->integer(),
			'typeId' => $this->integer()->notNull(),
			'siteId' => $this->integer()->notNull(),
			'postDate' => $this->dateTime(),
			'expiryDate' => $this->dateTime(),
			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'PRIMARY KEY([[id]])',
		]);
    }

    public function createIndexes(): void
    {
        $this->createIndex(null, '{{%documentation_navigation_elements}}', ['menuId'], false);

        $this->createIndex(null, '{{%documentation_navigations}}', ['handle'], false);
		$this->createIndex(null, '{{%documentation_navigations}}', ['structureId'], false);
        $this->createIndex(null, '{{%documentation_navigations}}', ['fieldLayoutId'], false);
		$this->createIndex(null, '{{%documentation_navigations}}', ['siteId'], false);
        $this->createIndex(null, '{{%documentation_navigations}}', ['dateDeleted'], false);

		$this->createIndex(null, '{{%documentation_guide_entries}}', ['postDate'], false);
		$this->createIndex(null, '{{%documentation_guide_entries}}', ['expiryDate'], false);
		$this->createIndex(null, '{{%documentation_guide_entries}}', ['structureId'], false);
		$this->createIndex(null, '{{%documentation_guide_entries}}', ['typeId'], false);
		$this->createIndex(null, '{{%documentation_guide_entries}}', ['fieldId'], false);
    }

    public function addForeignKeys(): void
    {
        $this->addForeignKey(null, '{{%documentation_navigation_elements}}', ['menuId'], '{{%documentation_navigations}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%documentation_navigation_elements}}', ['elementId'], '{{%elements}}', ['id'], 'SET NULL', null);
        $this->addForeignKey(null, '{{%documentation_navigation_elements}}', ['id'], '{{%elements}}', ['id'], 'CASCADE', null);

		$this->addForeignKey(null, '{{%documentation_navigations}}', ['structureId'], '{{%structures}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%documentation_navigations}}', ['fieldLayoutId'], '{{%fieldlayouts}}', ['id'], 'SET NULL', null);


		$this->addForeignKey(null, '{{%documentation_guide_entries}}', ['id'], Table::ELEMENTS, ['id'], 'CASCADE', null);
		$this->addForeignKey(null, '{{%documentation_guide_entries}}', ['structureId'], Table::STRUCTURES, ['id'], 'CASCADE', null);
		$this->addForeignKey(null, '{{%documentation_guide_entries}}', ['parentId'], '{{%documentation_guide_entries}}', ['id'], 'SET NULL', null);
		$this->addForeignKey(null, '{{%documentation_guide_entries}}', ['typeId'], Table::ENTRYTYPES, ['id'], 'CASCADE', null);
		$this->addForeignKey(null, '{{%documentation_guide_entries}}', ['fieldId'], Table::FIELDS, ['id'], 'CASCADE', null);

    }

    public function dropTables(): void
    {
        $this->dropTableIfExists('{{%documentation_navigation_elements}}');
        $this->dropTableIfExists('{{%documentation_navigations}}');
		$this->dropTableIfExists('{{%documentation_guide_entries}}');
    }

    public function dropForeignKeys(): void
    {
        if ($this->db->tableExists('{{%documentation_navigation_elements}}')) {
            MigrationHelper::dropAllForeignKeysOnTable('{{%documentation_navigation_elements}}', $this);
        }

        if ($this->db->tableExists('{{%documentation_navigations}}')) {
            MigrationHelper::dropAllForeignKeysOnTable('{{%documentation_navigations}}', $this);
        }

		if ($this->db->tableExists('{{%documentation_guide_entries}}')) {
			MigrationHelper::dropAllForeignKeysOnTable('{{%documentation_guide_entries}}', $this);
		}
    }

    public function dropProjectConfig(): void
    {
        Craft::$app->getProjectConfig()->remove('documentation');
    }
}
