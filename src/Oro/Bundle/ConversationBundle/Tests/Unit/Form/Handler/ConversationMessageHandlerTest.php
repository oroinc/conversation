<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Form\Handler\ConversationMessageHandler;
use Oro\Bundle\ConversationBundle\Manager\ConversationMessageManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ConversationMessageHandlerTest extends TestCase
{
    private ConversationMessageManager|MockObject $conversationMessageManager;

    private ConversationMessageHandler $handler;

    #[\Override]
    protected function setUp(): void
    {
        $this->conversationMessageManager = $this->createMock(ConversationMessageManager::class);
        $this->handler = new ConversationMessageHandler($this->conversationMessageManager);
    }

    public function testProcessOnGetRequest(): void
    {
        $request = new Request();
        $request->setMethod('GET');

        $data = new ConversationMessage();

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::once())
            ->method('setData')
            ->with($data);

        self::assertFalse($this->handler->process($data, $form, $request));
    }

    public function testProcessOnNotValidForm(): void
    {
        $request = new Request();
        $request->setMethod('POST');

        $data = new ConversationMessage();

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::once())
            ->method('setData')
            ->with($data);
        $form->expects(self::once())
            ->method('submit');
        $form->expects(self::once())
            ->method('isValid')
            ->willReturn(false);

        $this->conversationMessageManager->expects(self::never())
            ->method('saveMessage');

        self::assertFalse($this->handler->process($data, $form, $request));
    }

    public function testProcessOnValidForm(): void
    {
        $request = new Request();
        $request->setMethod('POST');

        $data = new ConversationMessage();

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::once())
            ->method('setData')
            ->with($data);
        $form->expects(self::once())
            ->method('submit');
        $form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $this->conversationMessageManager->expects(self::once())
            ->method('saveMessage');

        self::assertTrue($this->handler->process($data, $form, $request));
    }
}
