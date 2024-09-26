<?php

namespace Oro\Bundle\ConversationBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddConversationSourceIndex implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('oro_conversation');
        if ($table->hasIndex('conversation_source_idx')) {
            return;
        }

        $table->addIndex(['source_entity_class', 'source_entity_id'], 'conversation_source_idx');
    }
}
