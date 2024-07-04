<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Entity;

use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use PHPUnit\Framework\TestCase;

class ConversationTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors(): void
    {
        self::assertPropertyAccessors(
            new Conversation(),
            [
                ['name', 'some string'],
                ['messagesNumber', 132],
                ['owner', new User()],
                ['organization', new Organization()],
                ['sourceEntityClass', \stdClass::class],
                ['sourceEntityId', 48],
            ]
        );
    }

    public function testMessages(): void
    {
        $conversation = new Conversation();
        $message = new ConversationMessage();

        self::assertEmpty($conversation->getMessages());

        $conversation->addMessage($message);
        self::assertSame($message, $conversation->getMessages()->first());

        $conversation->removeMessage($message);
        self::assertEmpty($conversation->getMessages());
    }

    public function testParticipants(): void
    {
        $conversation = new Conversation();
        $participant = new ConversationParticipant();

        self::assertEmpty($conversation->getParticipants());

        $conversation->addParticipant($participant);
        self::assertSame($participant, $conversation->getParticipants()->first());

        $conversation->removeParticipant($participant);
        self::assertEmpty($conversation->getParticipants());
    }
}
