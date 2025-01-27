<?php

namespace Oro\Bundle\ConversationBundle\EventListener;

use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\EntityExtendBundle\Entity\Manager\AssociationManager;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Adds 'my conversation', 'have new messages' columns and filters to the conversation datagrid.
 */
class ConversationParticipantColumnListener
{
    private const COLUMN_IS_MY_CONVERSATION = 'is_my_conversation';
    private const COLUMN_HAVE_NEW_MESSAGES = 'have_new_messages';

    public function __construct(
        private TokenAccessorInterface $tokenAccessor,
        private AssociationManager $associationManager
    ) {
    }

    public function onBuildBefore(BuildBefore $event): void
    {
        $user = $this->tokenAccessor->getUser();
        if (!$user instanceof User) {
            return;
        }

        $targets = $this->associationManager->getAssociationTargets(
            ConversationParticipant::class,
            fn (string $ownerClass, string $targetClass) => $targetClass === User::class,
            RelationType::MANY_TO_ONE,
            'conversationParticipant'
        );

        $config = $event->getConfig();
        $query = $config->getOrmQuery();
        $query->addLeftJoin(
            $query->getRootAlias() . '.participants',
            'participant',
            'WITH',
            'participant.'. $targets[User::class] . ' = :user'
        );
        $config->offsetSetByPath(DatagridConfiguration::DATASOURCE_BIND_PARAMETERS_PATH, ['user']);
        $event->getDatagrid()->getParameters()->set('user', $user);

        $config->addColumn(
            self::COLUMN_HAVE_NEW_MESSAGES,
            [
                'label' => 'oro.conversation.datagrid.columns.have_new_messages',
                'type' => 'twig',
                'frontend_type' => 'html',
                'template' => '@OroConversation/Conversation/Datagrid/have_new_messages.html.twig',
                'renderable' => false,
                'inline_editing' => ['enabled' => false]
            ],
            sprintf(
                'CASE
                    WHEN participant.id is not null and conversation.messagesNumber > participant.lastReadMessageIndex
                        THEN true
                        ELSE false
                    END AS %s',
                self::COLUMN_HAVE_NEW_MESSAGES
            )
        );
        $config->addColumn(
            self::COLUMN_IS_MY_CONVERSATION,
            [
                'label' => 'oro.conversation.datagrid.columns.my',
                'frontend_type' => 'boolean',
                'renderable' => false,
                'inline_editing' => ['enabled' => false]
            ],
            sprintf(
                'CASE
                    WHEN participant.id is not null
                        THEN true
                        ELSE false
                    END AS %s',
                self::COLUMN_IS_MY_CONVERSATION
            )
        );

        $config->addFilter(self::COLUMN_IS_MY_CONVERSATION, [
            'type' => 'boolean',
            'data_name' => self::COLUMN_IS_MY_CONVERSATION,
        ]);
        $config->addFilter(self::COLUMN_HAVE_NEW_MESSAGES, [
            'type' => 'boolean',
            'data_name' => self::COLUMN_HAVE_NEW_MESSAGES,
        ]);

        $config->addProperty(
            'row_class_name',
            ['type' => 'callback', 'callable' => [$this, 'conversationHaveMessageFormatter']]
        );
    }

    public function conversationHaveMessageFormatter(ResultRecordInterface $record): ?string
    {
        if (!$record->getValue(self::COLUMN_HAVE_NEW_MESSAGES)) {
            return '';
        } else {
            return 'grid-row-attention';
        }
    }
}
