<?php

namespace Oro\Bundle\ConversationBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class SetLastReadDateAsNullableForParticipant implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('oro_conversation_participant');
        $table->changeColumn('last_read_date', ['notnull' => false]);
    }
}
