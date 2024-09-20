<?php

namespace Oro\Bundle\ConversationBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareTrait;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Migration\Extension\ConversationParticipantExtensionAwareInterface;
use Oro\Bundle\ConversationBundle\Migration\Extension\ConversationParticipantExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroConversationBundleInstaller implements
    Installation,
    ActivityExtensionAwareInterface,
    ConversationParticipantExtensionAwareInterface,
    ExtendExtensionAwareInterface
{
    use ActivityExtensionAwareTrait;
    use ConversationParticipantExtensionAwareTrait;
    use ExtendExtensionAwareTrait;

    #[\Override]
    public function getMigrationVersion(): string
    {
        return 'v1_0';
    }

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $this->createOroConversationMessageTable($schema);
        $this->createOroConversationTable($schema);
        $this->createOroConversationParticipantTable($schema);

        $this->addOroConversationMessageForeignKeys($schema);
        $this->addOroOroConversationForeignKeys($schema);
        $this->addOroConversationParticipantForeignKeys($schema);

        $this->addConversationStatusField($schema, $queries);
        $this->addConversationMessageTypeField($schema);

        $this->activityExtension->addActivityAssociation($schema, 'oro_conversation', 'oro_order');
        $this->activityExtension->addActivityAssociation($schema, 'oro_conversation', 'oro_rfp_request');
        $this->activityExtension->addActivityAssociation($schema, 'oro_conversation', 'oro_user');
        $this->activityExtension->addActivityAssociation($schema, 'oro_conversation', 'oro_customer_user');

        $this->conversationParticipantExtension->addParticipantAssociation($schema, 'oro_user');
        $this->conversationParticipantExtension->addParticipantAssociation($schema, 'oro_customer_user');
    }

    private function createOroConversationMessageTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_conversation_message');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('index', 'integer', []);
        $table->addColumn('conversation_id', 'integer', ['notnull' => false]);
        $table->addColumn('participant_id', 'integer', ['notnull' => false]);
        $table->addColumn('body', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['participant_id'], 'idx_3c7059a4f33e62ff', []);
        $table->addIndex(['conversation_id'], 'idx_3c7059a49ac0396', []);
    }

    private function createOroConversationTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_conversation');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('source_entity_class', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('source_entity_id', 'integer', ['notnull' => false]);
        $table->addColumn('messages_number', 'integer', []);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'idx_BA066CE19EB185F4');
        $table->addIndex(['organization_id'], 'idx_BA066CE132C8A1DE');
    }

    private function createOroConversationParticipantTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_conversation_participant');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('conversation_id', 'integer', ['notnull' => false]);
        $table->addColumn('last_read_message_id', 'integer', ['notnull' => false]);
        $table->addColumn('last_read_message_index', 'integer', []);
        $table->addColumn('last_read_date', 'datetime', []);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['conversation_id'], 'idx_12f5661b9ac0396', []);
        $table->addIndex(['last_read_message_id'], 'idx_12f5661bba0e79c3', []);
    }

    private function addOroConversationMessageForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_conversation_message');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_conversation'),
            ['conversation_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_conversation_participant'),
            ['participant_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    private function addOroOroConversationForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_conversation');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    private function addOroConversationParticipantForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_conversation_participant');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_conversation'),
            ['conversation_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_conversation_message'),
            ['last_read_message_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    private function addConversationStatusField(Schema $schema, QueryBag $queries): void
    {
        $this->extendExtension->addEnumField(
            $schema,
            'oro_conversation',
            'status',
            'conversation_status'
        );

        $enumOptionIds = array_map(
            fn ($key) => ExtendHelper::buildEnumOptionId('conversation_status', $key),
            [
                Conversation::STATUS_ACTIVE,
                Conversation::STATUS_INACTIVE,
                Conversation::STATUS_CLOSED,
            ]
        );
        $schema->getTable('oro_conversation')
            ->addExtendColumnOption('status', 'enum', 'immutable_codes', $enumOptionIds);
    }

    private function addConversationMessageTypeField(Schema $schema): void
    {
        $this->extendExtension->addEnumField(
            $schema,
            'oro_conversation_message',
            'type',
            'conversation_message_type'
        );

        $enumOptionIds = array_map(
            fn ($key) => ExtendHelper::buildEnumOptionId('conversation_message_type', $key),
            [ConversationMessage::TYPE_SYSTEM, ConversationMessage::TYPE_TEXT]
        );
        $schema->getTable('oro_conversation_message')
            ->addExtendColumnOption('type', 'enum', 'immutable_codes', $enumOptionIds);
    }
}
