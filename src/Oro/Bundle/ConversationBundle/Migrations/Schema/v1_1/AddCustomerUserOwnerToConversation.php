<?php

namespace Oro\Bundle\ConversationBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SecurityBundle\Migrations\Schema\UpdateOwnershipTypeQuery;

class AddCustomerUserOwnerToConversation implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('oro_conversation');
        if ($table->hasColumn('customer_user_id')) {
            return;
        }

        $table->addColumn('customer_user_id', 'integer', ['notnull' => false]);
        $table->addIndex(['customer_user_id'], 'idx_conv_customer_user');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );

        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addIndex(['customer_id'], 'idx_conv_customer');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );

        $queries->addQuery(
            new UpdateOwnershipTypeQuery(
                Conversation::class,
                [
                    'frontend_owner_type' => 'FRONTEND_USER',
                    'frontend_owner_field_name' => 'customerUser',
                    'frontend_owner_column_name' => 'customer_user_id',
                    'frontend_customer_field_name' => 'customer',
                    'frontend_customer_column_name' => 'customer_id'
                ]
            )
        );
        $queries->addQuery(
            new UpdateEntityConfigEntityValueQuery(
                Conversation::class,
                'security',
                'group_name',
                'commerce'
            )
        );
    }
}
