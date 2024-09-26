<?php

namespace Oro\Bundle\ConversationBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddLastMessageToConversation implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('oro_conversation');
        if ($table->hasColumn('last_message_id')) {
            return;
        }

        $table->addColumn('last_message_id', 'integer', ['notnull' => false]);
        $table->addIndex(['last_message_id'], 'idx_conv_last_message');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_conversation_message'),
            ['last_message_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }
}
