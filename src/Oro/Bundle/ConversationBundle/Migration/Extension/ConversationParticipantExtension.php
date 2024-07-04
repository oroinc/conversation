<?php

namespace Oro\Bundle\ConversationBundle\Migration\Extension;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

/**
 * Provides an ability to create conversation participant associations.
 */
class ConversationParticipantExtension implements ExtendExtensionAwareInterface
{
    use ExtendExtensionAwareTrait;

    private const TABLE_NAME = 'oro_conversation_participant';

    public function addParticipantAssociation(
        Schema $schema,
        string $targetTableName,
        ?string $targetColumnName = null
    ): void {
        $table = $schema->getTable(self::TABLE_NAME);
        $targetTable  = $schema->getTable($targetTableName);

        if (empty($targetColumnName)) {
            $primaryKeyColumns = $targetTable->getPrimaryKeyColumns();
            $targetColumnName  = array_shift($primaryKeyColumns);
        }

        $options = new OroOptions();
        $options->set('conversation_participant', 'enabled', true);
        $targetTable->addOption(OroOptions::KEY, $options);

        $associationName = ExtendHelper::buildAssociationName(
            $this->extendExtension->getEntityClassByTableName($targetTableName),
            'conversationParticipant'
        );

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $table,
            $associationName,
            $targetTable,
            $targetColumnName
        );
    }

    public function hasParticipantAssociation(Schema $schema, string $targetTableName): bool
    {
        $table = $schema->getTable(self::TABLE_NAME);
        $targetTable  = $schema->getTable($targetTableName);

        $associationName = ExtendHelper::buildAssociationName(
            $this->extendExtension->getEntityClassByTableName($targetTableName),
            'conversationParticipant'
        );

        if (!$targetTable->hasPrimaryKey()) {
            throw new SchemaException(
                sprintf('The table "%s" must have a primary key.', $targetTable->getName())
            );
        }
        $primaryKeyColumns = $targetTable->getPrimaryKey()->getColumns();
        if (count($primaryKeyColumns) !== 1) {
            throw new SchemaException(
                sprintf('A primary key of "%s" table must include only one column.', $targetTable->getName())
            );
        }

        $primaryKeyColumnName = array_pop($primaryKeyColumns);

        $nameGenerator = $this->extendExtension->getNameGenerator();
        $selfColumnName = $nameGenerator->generateRelationColumnName(
            $associationName,
            '_' . $primaryKeyColumnName
        );

        return $table->hasColumn($selfColumnName);
    }
}
