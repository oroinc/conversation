<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Entity;

use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use PHPUnit\Framework\TestCase;

class ConversationParticipantTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors(): void
    {
        self::assertPropertyAccessors(
            new ConversationParticipant(),
            [
                ['lastReadMessageIndex', 45],
                ['conversation', new Conversation()],
                ['lastReadMessage', new ConversationMessage()],
                ['lastReadDate', new \DateTime('2024-01-01')]
            ]
        );
    }
}
