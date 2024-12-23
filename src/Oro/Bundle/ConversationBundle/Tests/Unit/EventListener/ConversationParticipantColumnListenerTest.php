<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\EventListener;

use Oro\Bundle\ConversationBundle\EventListener\ConversationParticipantColumnListener;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\EntityExtendBundle\Entity\Manager\AssociationManager;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConversationParticipantColumnListenerTest extends TestCase
{
    private TokenAccessorInterface|MockObject $tokenAccessor;
    private AssociationManager|MockObject $associationManager;

    private ConversationParticipantColumnListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->associationManager = $this->createMock(AssociationManager::class);

        $this->listener = new ConversationParticipantColumnListener($this->tokenAccessor, $this->associationManager);
    }

    public function testOnBuildBeforeWithoutUserInToken(): void
    {
        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn(new \stdClass());

        $event = new BuildBefore($this->createMock(DatagridInterface::class), DatagridConfiguration::create([]));

        $this->listener->onBuildBefore($event);

        self::assertEquals([], $event->getConfig()->toArray());
    }

    public function testOnBuildBefore(): void
    {
        $user = new User();
        $datagridConfig = DatagridConfiguration::create([]);
        $parameters = new ParameterBag();
        $datagrid = new Datagrid('conversations-grid', $datagridConfig, $parameters);
        $event = new BuildBefore($datagrid, $datagridConfig);

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);
        $this->associationManager->expects(self::once())
            ->method('getAssociationTargets')
            ->willReturn([User::class => 'user_field123']);

        $this->listener->onBuildBefore($event);

        self::assertEquals(
            [
                'filters'    => [
                    'columns' => [
                        'is_my_conversation' => ['type' => 'boolean', 'data_name' => 'is_my_conversation'],
                        'have_new_messages'  => ['type' => 'boolean', 'data_name' => 'have_new_messages'],
                    ],
                ],
                'source'     => [
                    'query'           => [
                        'join'   => [
                            'left' => [
                                [
                                    'join'          => '.participants',
                                    'alias'         => 'participant',
                                    'conditionType' => 'WITH',
                                    'condition'     => 'participant.user_field123 = :user',
                                ],
                            ],
                        ],
                        'select' => [
                            'CASE
                    WHEN participant.id is not null and conversation.messagesNumber > participant.lastReadMessageIndex
                        THEN true
                        ELSE false
                    END AS have_new_messages',
                            'CASE
                    WHEN participant.id is not null
                        THEN true
                        ELSE false
                    END AS is_my_conversation',
                        ],
                    ],
                    'bind_parameters' => ['user'],
                ],
                'columns'    => [
                    'is_my_conversation' => [
                        'label'         => 'oro.conversation.datagrid.columns.my',
                        'frontend_type' => 'boolean',
                        'renderable'    => false,
                        'inline_editing' => ['enabled' => false]
                    ],
                    'have_new_messages'  => [
                        'label'         => 'oro.conversation.datagrid.columns.have_new_messages',
                        'type'          => 'twig',
                        'frontend_type' => 'html',
                        'template'      => '@OroConversation/Conversation/Datagrid/have_new_messages.html.twig',
                        'renderable'    => false,
                        'inline_editing' => ['enabled' => false]
                    ],
                ],
                'properties' => [
                    'row_class_name' => [
                        'type'     => 'callback',
                        'callable' => [$this->listener, 'conversationHaveMessageFormatter'],
                    ],
                ],
            ],
            $event->getConfig()->toArray()
        );

        self::assertSame($parameters->get('user'), $user);
    }
}
