<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\EventListener;

use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\ConversationBundle\Acl\Voter\ManageConversationMessagesVoter;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\EventListener\ConversationMessageSaveListener;
use Oro\Bundle\ConversationBundle\Manager\ConversationParticipantManager;
use Oro\Bundle\ConversationBundle\Tests\Unit\Fixture\ConversationMessageExtended;
use Oro\Bundle\ConversationBundle\Tests\Unit\Fixture\ConversationMessageType;
use Oro\Bundle\ConversationBundle\Tests\Unit\Fixture\ConversationParticipantExtended;
use Oro\Bundle\EntityExtendBundle\Provider\EnumOptionsProvider;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UIBundle\Tools\HtmlTagHelper;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ConversationMessageSaveListenerTest extends TestCase
{
    private AuthorizationCheckerInterface|MockObject $authorizationChecker;
    private HtmlTagHelper|MockObject $htmlTagHelper;
    private ActivityManager|MockObject $activityManager;
    private EnumOptionsProvider|MockObject $enumOptionsProvider;
    private ConversationParticipantManager|MockObject $participantManager;
    private TokenAccessorInterface|MockObject $tokenAccessor;

    private ConversationMessageSaveListener $listener;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->htmlTagHelper = $this->createMock(HtmlTagHelper::class);
        $this->activityManager = $this->createMock(ActivityManager::class);
        $this->enumOptionsProvider = $this->createMock(EnumOptionsProvider::class);
        $this->participantManager = $this->createMock(ConversationParticipantManager::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->listener = new ConversationMessageSaveListener(
            $this->authorizationChecker,
            $this->htmlTagHelper,
            $this->activityManager,
            $this->enumOptionsProvider,
            $this->participantManager,
            $this->tokenAccessor
        );
    }

    public function testPrePersistWhenUserHaveNoAccess(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('You does not have access to manage messages for conversation "conv1".');

        $conversation = new Conversation();
        $conversation->setName('conv1');
        $message = new ConversationMessage();
        $message->setConversation($conversation);

        $this->tokenAccessor->expects($this->once())
            ->method('hasUser')
            ->willReturn(true);

        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with(ManageConversationMessagesVoter::PERMISSION_NAME, $conversation)
            ->willReturn(false);

        $this->listener->prePersist($message);
    }

    public function testPrePersistMessageOnNewMessage(): void
    {
        $conversation = new Conversation();
        $conversation->setName('conv1');
        $conversation->setMessagesNumber(12);
        $message = new ConversationMessageExtended();
        $message->setConversation($conversation);
        $message->setBody("<script>alert();</script>Message\n #1");

        $messageType = new ConversationMessageType(1, ConversationMessage::TYPE_TEXT, 1);

        $user = new User();
        $participant = new ConversationParticipantExtended();
        $participant->setConversation($conversation);
        $participant->setConversationParticipantTarget($user);

        $this->tokenAccessor->expects($this->once())
            ->method('hasUser')
            ->willReturn(true);

        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with(ManageConversationMessagesVoter::PERMISSION_NAME, $conversation)
            ->willReturn(true);

        $this->enumOptionsProvider->expects(self::once())
            ->method('getEnumOptionByCode')
            ->with(ConversationMessage::MESSAGE_TYPE_ENUM_CODE, ConversationMessage::TYPE_TEXT)
            ->willReturn($messageType);

        $this->participantManager->expects(self::once())
            ->method('getOrCreateParticipantObjectForConversation')
            ->with($conversation)
            ->willReturn($participant);

        $this->activityManager->expects(self::once())
            ->method('addActivityTarget')
            ->with($conversation, $user);

        $this->htmlTagHelper->expects(self::once())
            ->method('sanitize')
            ->with('<p><script>alert();</script>Message</p><p> #1</p>')
            ->willReturn('<p>>Message</p><p> #1</p>');

        $this->listener->prePersist($message);

        self::assertSame($messageType, $message->getType());
        self::assertEquals(13, $message->getConversation()->getMessagesNumber());
        self::assertEquals(13, $message->getParticipant()->getLastReadMessageIndex());
        self::assertInstanceOf(\DateTime::class, $message->getParticipant()->getLastReadDate());
        self::assertSame($user, $message->getParticipant()->getConversationParticipantTarget());
        self::assertEquals('<p>>Message</p><p> #1</p>', $message->getBody());
    }
}
