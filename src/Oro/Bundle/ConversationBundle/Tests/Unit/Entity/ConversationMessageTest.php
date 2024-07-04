<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Entity;

use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use PHPUnit\Framework\TestCase;

class ConversationMessageTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors(): void
    {
        self::assertPropertyAccessors(
            new ConversationMessage(),
            [
                ['index', 489],
                ['body', 'test string'],
                ['conversation', new Conversation()],
                ['participant', new ConversationParticipant()]
            ]
        );
    }
}
